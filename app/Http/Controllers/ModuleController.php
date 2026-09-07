<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Facades\Module;
use Symfony\Component\Process\Process;
use ZipArchive;

class ModuleController extends Controller
{
    /**
     * Handle Module ZIP Upload & Automatic Installation Pipeline.
     *
     * 1. Validate uploaded .zip
     * 2. Open archive
     * 3. Read module.json manifest
     * 4. Extract files to Modules/{ModuleName}
     * 5. Run automatic setup pipeline (Enable -> Composer -> Migrate -> Seed -> Cache Clear)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        // 5-minute timeout and 512MB memory for large packages and composer sync
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        // Step 1: Validate file format and size
        $request->validate([
            'module_zip' => 'required|file|max:51200', // max 50MB
        ]);

        $file = $request->file('module_zip');
        if (strtolower($file->getClientOriginalExtension()) !== 'zip') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file format. Only .zip archives are supported.',
            ], 422);
        }

        // Step 2: Open the ZIP archive
        $zip = new ZipArchive;
        if ($zip->open($file->getRealPath()) !== true) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to open the uploaded ZIP. File may be corrupted.',
            ], 422);
        }

        try {
            // Step 3: Find and read module.json
            $manifest = $this->readManifest($zip);
            if (!$manifest) {
                $zip->close();
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid package. Required "module.json" manifest was not found in the ZIP.',
                ], 422);
            }

            $moduleName = trim($manifest['name']);

            // Step 4: Extract files safely into Modules/{moduleName}
            $this->extractModuleFiles($zip, $moduleName);
            $zip->close();

            Log::info("Module '{$moduleName}' extracted successfully into Modules/{$moduleName}");

            // Step 5: Run Automated Configuration Pipeline
            $pipelineResults = $this->runSetupPipeline($moduleName);

            // Step 6: Return success response
            return response()->json([
                'success' => true,
                'module' => $moduleName,
                'version' => $manifest['version'] ?? '1.0.0',
                'target_directory' => 'Modules/' . $moduleName,
                'pipeline' => $pipelineResults,
                'message' => "Module '{$moduleName}' uploaded, extracted, and configured successfully!",
            ]);

        } catch (\Throwable $e) {
            if (isset($zip) && $zip instanceof ZipArchive && $zip->filename) {
                @$zip->close();
            }
            Log::error("Module installation failed: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Installation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Find and parse module.json from the ZIP archive.
     * (Handles both root module.json and nested folder Finance/module.json)
     *
     * @param ZipArchive $zip
     * @return array|null
     */
    protected function readManifest(ZipArchive $zip): ?array
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = str_replace('\\', '/', $zip->getNameIndex($i));

            // Matches root module.json OR FolderName/module.json
            if ($entryName === 'module.json' || preg_match('/^[^\/]+\/module\.json$/i', $entryName)) {
                $content = $zip->getFromIndex($i);
                $manifest = json_decode($content, true);

                if (is_array($manifest) && !empty($manifest['name'])) {
                    return $manifest;
                }
            }
        }


        
        return null;
    }

    /**
     * Extract files safely into Modules/{moduleName}/ directory.
     *
     * @param ZipArchive $zip
     * @param string $moduleName
     * @return void
     */
    protected function extractModuleFiles(ZipArchive $zip, string $moduleName): void
    {
        $targetDir = base_path('Modules/' . $moduleName);

        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        // Check if ZIP was packaged with an outer folder (e.g. Finance/...)
        $folderPrefix = '';
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = str_replace('\\', '/', $zip->getNameIndex($i));
            if (preg_match('/^([^\/]+)\/module\.json$/i', $name, $matches)) {
                $folderPrefix = $matches[1] . '/';
                break;
            }
        }

        // Extract every file into target directory
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = str_replace('\\', '/', $zip->getNameIndex($i));

            // Strip the outer folder name if present
            $relativePath = (!empty($folderPrefix) && str_starts_with($entryName, $folderPrefix))
                ? substr($entryName, strlen($folderPrefix))
                : $entryName;

            if (empty($relativePath) || $relativePath === '/') {
                continue;
            }

            // Security: Prevent Zip-Slip directory traversal
            if (str_contains($relativePath, '..')) {
                continue;
            }

            $destination = $targetDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

            if (str_ends_with($entryName, '/')) {
                File::makeDirectory($destination, 0755, true, true);
            } else {
                File::makeDirectory(dirname($destination), 0755, true, true);
                File::put($destination, $zip->getFromIndex($i));
            }
        }
    }

    /**
     * Run the automated 5-step module setup pipeline:
     * 1. Enable module in modules_statuses.json
     * 2. Synchronize Composer autoloader
     * 3. Run database migrations
     * 4. Run module seeders (permissions)
     * 5. Clear application caches
     *
     * @param string $moduleName
     * @return array
     */
    protected function runSetupPipeline(string $moduleName): array
    {
        $log = [];

        // 1. Enable module in status file & Nwidart
        try {
            $statusFile = base_path('modules_statuses.json');
            $statuses = File::exists($statusFile) ? (json_decode(File::get($statusFile), true) ?: []) : [];
            $statuses[$moduleName] = true;
            File::put($statusFile, json_encode($statuses, JSON_PRETTY_PRINT));

            if (class_exists(Module::class)) {
                Module::enable($moduleName);
            }

            $log['module_enable'] = ['success' => true, 'message' => "Module enabled in status file."];
        } catch (\Throwable $e) {
            $log['module_enable'] = ['success' => false, 'message' => $e->getMessage()];
        }

        // 2. Composer Autoload Sync (composer dump-autoload)
        try {
            $composerBinary = $this->resolveComposerBinary();
            $process = new Process([$composerBinary, 'dump-autoload', '-o', '--no-scripts'], base_path());
            $process->setTimeout(180);
            $process->run();

            $log['composer_sync'] = ['success' => true, 'message' => 'Composer autoloader synchronized.'];
        } catch (\Throwable $e) {
            $log['composer_sync'] = ['success' => false, 'message' => $e->getMessage()];
        }

        // 3. Database Migrations (php artisan module:migrate {module})
        try {
            Artisan::call('module:migrate', ['module' => $moduleName, '--force' => true]);
            $log['migrations'] = ['success' => true, 'message' => 'Database tables migrated.'];
        } catch (\Throwable $e) {
            $log['migrations'] = ['success' => false, 'message' => $e->getMessage()];
        }

        // 4. Module Seeders (php artisan module:seed {module})
        try {
            Artisan::call('module:seed', ['module' => $moduleName, '--force' => true]);
            $log['seeders'] = ['success' => true, 'message' => 'Default permissions seeded.'];
        } catch (\Throwable $e) {
            $log['seeders'] = ['success' => false, 'message' => $e->getMessage()];
        }

        // 5. Cache Clear (php artisan optimize:clear)
        try {
            Artisan::call('optimize:clear');
            $log['cache_clear'] = ['success' => true, 'message' => 'Application caches cleared.'];
        } catch (\Throwable $e) {
            $log['cache_clear'] = ['success' => false, 'message' => $e->getMessage()];
        }

        return $log;
    }

    /**
     * find  composer binary executable path across different environments.
     * just for find composer path in windows and linux server.
     *
     * @return string
     */
    protected function resolveComposerBinary(): string
    {
        $candidatePaths = [
            'C:\\laragon\\bin\\composer\\composer.bat',
            'C:\\ProgramData\\ComposerSetup\\bin\\composer.bat',
            '/usr/local/bin/composer',
            '/usr/bin/composer',
        ];

        foreach ($candidatePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return 'composer';
    }
}