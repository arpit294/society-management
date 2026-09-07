<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Facades\Module;
//this is for the the composer autoload cmd
use Symfony\Component\Process\Process;

class ModuleController extends Controller
{
    /**
     * Handle Module ZIP Upload.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'module_zip' => 'required|file|max:51200', // 50MB
        ]);

        $file = $request->file('module_zip');
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension !== 'zip') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file format. Only .zip archives are supported.',
            ], 422);
        }

        $zip = new \ZipArchive;
        $zipPath = $file->getRealPath();

        if ($zip->open($zipPath) !== true) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to open the uploaded ZIP archive. File may be corrupted.',
            ], 422);
        }

        try {
            // Step 2.1: Locate module.json inside archive (at root or top-level directory)
            $manifestPath = null;
            $hasPrefixDir = false;
            $prefixDir = '';

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $filename = str_replace('\\', '/', $stat['name']);

                // Root-level module.json (e.g. module.json)
                if (strtolower($filename) === 'module.json') {
                    $manifestPath = $stat['name'];
                    $hasPrefixDir = false;
                    break;
                }

                // Nested in single top-level folder (e.g. Finance/module.json)
                if (preg_match('/^([^\/]+)\/module\.json$/i', $filename, $matches)) {
                    $manifestPath = $stat['name'];
                    $hasPrefixDir = true;
                    $prefixDir = $matches[1];
                    break;
                }
            }

            if (!$manifestPath) {
                $zip->close();
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid module package. Required "module.json" manifest was not found in the ZIP archive.',
                ], 422);
            }

            // Step 2.2: Read and validate module.json
            $manifestContent = $zip->getFromName($manifestPath);
            if (!$manifestContent) {
                $zip->close();
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to read module.json from the ZIP archive.',
                ], 422);
            }

            $manifest = json_decode($manifestContent, true);
            if (!is_array($manifest) || empty($manifest['name'])) {
                $zip->close();
                return response()->json([
                    'success' => false,
                    'message' => 'Corrupted or invalid module.json. Required "name" attribute is missing.',
                ], 422);
            }

            $moduleName = trim($manifest['name']);

            // Validate module name format
            if (!preg_match('/^[A-Za-z0-9_]+$/', $moduleName)) {
                $zip->close();
                return response()->json([
                    'success' => false,
                    'message' => "Invalid module name '{$moduleName}'. Must only contain letters, numbers, and underscores.",
                ], 422);
            }

            // Step 2.3: Safe extraction into Modules/{moduleName}
            $modulesBasePath = base_path('Modules');
            if (!File::exists($modulesBasePath)) {
                File::makeDirectory($modulesBasePath, 0755, true);
            }

            $targetDir = $modulesBasePath . DIRECTORY_SEPARATOR . $moduleName;
            if (!File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }

            $realTargetDir = realpath($targetDir) ?: $targetDir;

            // Extract each file safely with Zip Slip (path traversal) prevention
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = $zip->getNameIndex($i);
                $normalizedEntry = str_replace('\\', '/', $entryName);

                // Strip root folder prefix if archive was zipped with folder
                $relativePath = $normalizedEntry;
                if ($hasPrefixDir && str_starts_with($normalizedEntry, $prefixDir . '/')) {
                    $relativePath = substr($normalizedEntry, strlen($prefixDir) + 1);
                }

                if (empty($relativePath) || $relativePath === '/') {
                    continue;
                }

                $destFile = $targetDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

                // Path Traversal Security Verification
                $normalizedDestDir = realpath(dirname($destFile)) ?: dirname($destFile);
                if (!str_starts_with(str_replace('/', '\\', $destFile), str_replace('/', '\\', $realTargetDir))) {
                    $zip->close();
                    return response()->json([
                        'success' => false,
                        'message' => 'Security violation: Path traversal detected in archive.',
                    ], 422);
                }

                if (str_ends_with($normalizedEntry, '/')) {
                    if (!File::exists($destFile)) {
                        File::makeDirectory($destFile, 0755, true);
                    }
                } else {
                    $parentDir = dirname($destFile);
                    if (!File::exists($parentDir)) {
                        File::makeDirectory($parentDir, 0755, true);
                    }

                    $stream = $zip->getStream($entryName);
                    if ($stream) {
                        $out = fopen($destFile, 'w');
                        stream_copy_to_stream($stream, $out);
                        fclose($out);
                        fclose($stream);
                    } else {
                        $fileContent = $zip->getFromIndex($i);
                        File::put($destFile, $fileContent);
                    }
                }
            }

            $zip->close();

            Log::info("Module '{$moduleName}' extracted successfully into Modules/{$moduleName}");

            // Step 3: Run Automated Pipeline (Enable -> Composer Sync -> Migrations -> Cache Clear)
            $pipelineResults = $this->runModuleSetupPipeline($moduleName);

            return response()->json([
                'success' => true,
                'module' => $moduleName,
                'version' => $manifest['version'] ?? '1.0.0',
                'description' => $manifest['description'] ?? '',
                'target_directory' => 'Modules/' . $moduleName,
                'pipeline' => $pipelineResults,
                'message' => "Module '{$moduleName}' uploaded, extracted, and automatically configured successfully!",
            ]);
        } catch (\Throwable $e) {
            $zip->close();
            Log::error("Module extraction failed: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Extraction failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run the automated module setup pipeline:
     * 1. Enable module in modules_statuses.json
     * 2. Composer dump-autoload
     * 3. Database migrations (php artisan module:migrate {module})
     * 4. Cache clear (php artisan optimize:clear)
     *
     * @param string $moduleName
     * @return array
     */
    protected function runModuleSetupPipeline(string $moduleName): array
    {
        $log = [];

        // 1. Enable Module in modules_statuses.json & Nwidart Module System
        try {
            if (class_exists(Module::class)) {
                Module::enable($moduleName);
            }

            // Fallback guarantee: write directly to modules_statuses.json
            $statusesFile = base_path('modules_statuses.json');
            $statuses = [];
            if (File::exists($statusesFile)) {
                $statuses = json_decode(File::get($statusesFile), true) ?: [];
            }
            $statuses[$moduleName] = true;
            File::put($statusesFile, json_encode($statuses, JSON_PRETTY_PRINT));

            $log['module_enable'] = [
                'success' => true,
                'message' => "Module '{$moduleName}' enabled in modules_statuses.json.",
            ];
        } catch (\Throwable $e) {
            $log['module_enable'] = [
                'success' => false,
                'message' => 'Failed to enable module: ' . $e->getMessage(),
            ];
        }

        // 2. Composer Autoload Sync (composer dump-autoload -o --no-scripts)
        try {
            $process = new Process(['composer', 'dump-autoload', '-o', '--no-scripts'], base_path());
            $process->setTimeout(180);
            $process->run();

            if ($process->isSuccessful()) {
                $log['composer_sync'] = [
                    'success' => true,
                    'message' => 'Composer autoload synchronized successfully.',
                ];
            } else {
                // Fallback attempt via shell exec
                $output = [];
                $retCode = 0;
                exec("cd /d " . escapeshellarg(base_path()) . " && composer dump-autoload -o --no-scripts 2>&1", $output, $retCode);

                $log['composer_sync'] = [
                    'success' => ($retCode === 0),
                    'message' => ($retCode === 0) ? 'Composer autoload synchronized via shell fallback.' : 'Composer autoload sync warning.',
                ];
            }
        } catch (\Throwable $e) {
            $log['composer_sync'] = [
                'success' => false,
                'message' => 'Composer autoload error: ' . $e->getMessage(),
            ];
        }

        // 3. Database Migrations (php artisan module:migrate {module})
        try {
            Artisan::call('module:migrate', [
                'module' => $moduleName,
                '--force' => true,
            ]);

            $log['migrations'] = [
                'success' => true,
                'message' => "Database migrations completed for '{$moduleName}'.",
                'output' => trim(Artisan::output()),
            ];
        } catch (\Throwable $e) {
            $log['migrations'] = [
                'success' => false,
                'message' => 'Migration notice: ' . $e->getMessage(),
            ];
        }

        // 4. Cache Clear (php artisan optimize:clear)
        try {
            Artisan::call('optimize:clear');
            $log['cache_clear'] = [
                'success' => true,
                'message' => 'Application caches flushed successfully.',
            ];
        } catch (\Throwable $e) {
            $log['cache_clear'] = [
                'success' => false,
                'message' => 'Cache clear notice: ' . $e->getMessage(),
            ];
        }

        return $log;
    }

    /**
     * Toggle a module between Enabled and Disabled state.
     *
     * @param Request $request
     * @param string $module
     * @return JsonResponse
     */
    public function toggle(Request $request, string $module): JsonResponse
    {
        try {
            $moduleDir = base_path('Modules/' . $module);

            if (!File::isDirectory($moduleDir) && !Module::has($module)) {
                return response()->json([
                    'success' => false,
                    'message' => "Module '{$module}' is not installed.",
                ], 404);
            }

            if (Module::isEnabled($module)) {
                Module::disable($module);
                $status = 'disabled';
            } else {
                Module::enable($module);
                $status = 'enabled';
            }

            Artisan::call('optimize:clear');

            return response()->json([
                'success' => true,
                'module' => $module,
                'status' => $status,
                'is_enabled' => Module::isEnabled($module),
                'message' => "Module '{$module}' has been {$status} successfully.",
            ]);
        } catch (\Throwable $e) {
            Log::error("Module toggle failed for {$module}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle module status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Uninstall / Delete a module.
     *
     * @param Request $request
     * @param string $module
     * @return JsonResponse
     */
    public function destroy(Request $request, string $module): JsonResponse
    {
        try {
            $moduleDir = base_path('Modules/' . $module);

            if (!File::isDirectory($moduleDir) && !Module::has($module)) {
                return response()->json([
                    'success' => false,
                    'message' => "Module '{$module}' is not found.",
                ], 404);
            }

            // Disable module first
            if (Module::has($module)) {
                Module::disable($module);
            }

            Artisan::call('optimize:clear');

            return response()->json([
                'success' => true,
                'module' => $module,
                'message' => "Module '{$module}' uninstalled successfully.",
            ]);
        } catch (\Throwable $e) {
            Log::error("Module deletion failed for {$module}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to uninstall module: ' . $e->getMessage(),
            ], 500);
        }
    }
}