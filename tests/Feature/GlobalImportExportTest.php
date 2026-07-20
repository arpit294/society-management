<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Block;
use App\Models\Flat;
use App\Models\FlatType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use OpenSpout\Writer\XLSX\Writer as XLSXWriter;
use OpenSpout\Common\Entity\Row;

class GlobalImportExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup admin role & permissions
        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $permView = Permission::firstOrCreate(['name' => 'setting_view', 'guard_name' => 'web']);
        $permEdit = Permission::firstOrCreate(['name' => 'setting_edit', 'guard_name' => 'web']);
        $role->givePermissionTo([$permView, $permEdit]);

        $this->user = User::factory()->create(['status' => 'active']);
        $this->user->assignRole('admin');
    }

    public function test_export_master_works_and_filters_selected_tables()
    {
        $this->actingAs($this->user);

        // Create sample data
        Block::create(['block_name' => 'Block A', 'total_floor' => 5, 'total_flats' => 10]);

        $response = $this->get(route('settings.global.export_master', ['tables' => ['blocks', 'flats']]));

        $response->assertStatus(200);
        $response->assertHeader('Content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_template_master_works_and_filters_selected_tables()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('settings.global.template_master', ['tables' => ['blocks']]));

        $response->assertStatus(200);
        $response->assertHeader('Content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_preview_and_process_master_import()
    {
        $this->actingAs($this->user);
        Storage::fake('local');

        // Create a temporary Excel file matching Block headers
        $tempPath = storage_path('app/test_master.xlsx');
        $writer = new XLSXWriter();
        $writer->openToFile($tempPath);
        
        // Add sheet named Blocks
        $sheet = $writer->getCurrentSheet();
        $sheet->setName('Blocks');
        $writer->addRow(Row::fromValues(['Block Name (*)', 'Total Floors', 'Total Flats']));
        $writer->addRow(Row::fromValues(['Tower Z', 10, 40]));
        $writer->close();

        $file = new UploadedFile($tempPath, 'master.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        // Preview Master
        $previewResponse = $this->postJson(route('settings.global.preview_master'), [
            'import_file' => $file
        ]);

        $previewResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $filePath = $previewResponse->json('file_path');

        // Process Master
        $processResponse = $this->postJson(route('settings.global.process_master'), [
            'file_path' => $filePath
        ]);

        $processResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'success_count' => 1,
                'failed_count' => 0
            ]);

        $this->assertDatabaseHas('blocks', [
            'block_name' => 'Tower Z',
            'total_floor' => 10,
            'total_flats' => 40
        ]);

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }
}
