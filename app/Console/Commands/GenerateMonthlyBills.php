<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:generate-monthly-bills')]
#[Description('Generate monthly maintenance bills for all active residents')]
class GenerateMonthlyBills extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentMonthName = now()->format('F');
        $currentYear = now()->year;
        $dueDate = now()->setDay(15)->format('Y-m-d');
        $generatedDate = now()->format('Y-m-d');

        $this->info("Starting generation of maintenance bills for {$currentMonthName} {$currentYear}...");

        $activeResidents = \App\Models\Resident::with(['flat.flatType'])
            ->whereNull('move_out_date')
            ->orWhere('move_out_date', '>=', now()->startOfMonth())
            ->get();

        $count = 0;

        foreach ($activeResidents as $resident) {
            // Check if bill already exists for this flat for the current month and year
            $billExists = \App\Models\MaintenanceBill::where('flat_id', $resident->flat_id)
                ->where('month', $currentMonthName)
                ->where('year', $currentYear)
                ->exists();

            if (!$billExists) {
                $maintenanceFee = $resident->flat && $resident->flat->flatType 
                    ? $resident->flat->flatType->maintenance_fee 
                    : 0;

                if ($maintenanceFee > 0) {
                    \App\Models\MaintenanceBill::create([
                        'block_id' => $resident->block_id,
                        'user_id' => $resident->user_id,
                        'flat_id' => $resident->flat_id,
                        'amount' => $maintenanceFee,
                        'penalty_amount' => 0,
                        'total_amount' => $maintenanceFee,
                        'month' => $currentMonthName,
                        'year' => $currentYear,
                        'due_date' => $dueDate,
                        'generated_date' => $generatedDate,
                        'status' => 'pending',
                    ]);
                    $count++;
                }
            }
        }

        $this->info("Successfully generated {$count} maintenance bills.");
    }
}
