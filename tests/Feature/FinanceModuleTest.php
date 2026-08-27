<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Flat;
use App\Models\FlatType;
use App\Models\MaintenanceBill;
use App\Models\NameTransferBill;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->artisan('migrate');
        $this->seed(\Database\Seeders\CleanSocietySeeder::class);
    }

    public function test_maintenance_bills_page_accessible()
    {
        $admin = User::where('role', 'Admin')->first();
        $this->actingAs($admin);

        $response = $this->get(route('maintenance-bills.index'));
        $response->assertStatus(200);
    }

    public function test_expenses_page_accessible()
    {
        $admin = User::where('role', 'Admin')->first();
        $this->actingAs($admin);

        $response = $this->get(route('expenses.index'));
        $response->assertStatus(200);
    }

    public function test_expense_categories_page_accessible()
    {
        $admin = User::where('role', 'Admin')->first();
        $this->actingAs($admin);

        $response = $this->get(route('expense-categories.index'));
        $response->assertStatus(200);
    }

    public function test_name_transfer_bills_page_accessible()
    {
        $admin = User::where('role', 'Admin')->first();
        $this->actingAs($admin);

        $response = $this->get(route('name-transfer-bills.index'));
        $response->assertStatus(200);
    }

    public function test_maintenance_report_page_accessible()
    {
        $admin = User::where('role', 'Admin')->first();
        $this->actingAs($admin);

        $response = $this->get(route('reports.maintenance'));
        $response->assertStatus(200);
    }
}
