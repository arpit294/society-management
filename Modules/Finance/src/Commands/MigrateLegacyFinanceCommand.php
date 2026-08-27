<?php

namespace Modules\Finance\Commands;

use Illuminate\Console\Command;
use Modules\Finance\Services\LegacyDataMigrationService;

class MigrateLegacyFinanceCommand extends Command
{
    protected $signature = 'finance:migrate-legacy';
    protected $description = 'Migrate legacy expenses and maintenance bills into the new Finance Module double-entry ledger';

    public function handle(LegacyDataMigrationService $migrationService): int
    {
        $this->info("=== Starting Legacy Finance Data Migration ===");

        $this->info("Migrating expenses...");
        $expenses = $migrationService->migrateExpenses();
        $this->line("  -> Migrated {$expenses} expenses into Vendor Bills & Ledger.");

        $this->info("Migrating maintenance bills...");
        $maintenance = $migrationService->migrateMaintenanceBills();
        $this->line("  -> Migrated {$maintenance} maintenance bills into Invoices & Receipts.");

        $this->info("Migrating name transfer bills...");
        $transfers = $migrationService->migrateNameTransferBills();
        $this->line("  -> Migrated {$transfers} transfer bills.");

        $this->info("=== Migration Completed Successfully ===");
        return Command::SUCCESS;
    }
}
