<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Resident;
use App\Models\Maintenance;
use App\Models\MaintenanceBill;

class MaintenanceBillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $residents = Resident::with(['flat.flatType', 'flat.block', 'user'])
            ->whereNull('move_out_date')
            ->get();

        if ($residents->isEmpty()) {
            return;
        }

        // Create 3 maintenance billing periods: May, June, July of current year
        $year = (int) date('Y');
        
        $mMay = Maintenance::firstOrCreate(
            ['month' => 'May', 'year' => $year],
            ['billing_cycle' => 'monthly', 'due_date' => "$year-05-31", 'status' => 'published']
        );

        $mJune = Maintenance::firstOrCreate(
            ['month' => 'June', 'year' => $year],
            ['billing_cycle' => 'monthly', 'due_date' => "$year-06-30", 'status' => 'published']
        );

        $mJuly = Maintenance::firstOrCreate(
            ['month' => 'July', 'year' => $year],
            ['billing_cycle' => 'monthly', 'due_date' => "$year-07-31", 'status' => 'published']
        );

        foreach ($residents as $index => $resident) {
            if (!$resident->flat || !$resident->flat->flatType) {
                continue;
            }

            $fee = ($resident->type === 'owner')
                ? $resident->flat->flatType->owner_maintenance_fee
                : $resident->flat->flatType->rental_maintenance_fee;

            // All residents paid May bill
            MaintenanceBill::updateOrCreate([
                'maintenance_id' => $mMay->id,
                'flat_id' => $resident->flat_id,
            ], [
                'batch_id' => uniqid('pay_'),
                'user_id' => $resident->user_id,
                'block_id' => $resident->block_id,
                'amount' => $fee,
                'total_amount' => $fee,
                'generated_date' => "$year-05-01",
                'paid_at' => "$year-05-10",
                'payment_method' => 'upi',
                'transaction_id' => str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT),
                'status' => 'paid',
            ]);

            // For half of the flats (or specifically flats ending in 1, 2, 3 like A-901, A-101, B-202, C-203), leave June and July UNPAID (due/pending)
            $flatNo = $resident->flat->flat_no ?? '';
            $hasPending = ($index % 2 == 0) || in_array($flatNo, ['901', '101', '202', '203', '303', '404', '505']);

            if ($hasPending) {
                // June is overdue/due
                MaintenanceBill::updateOrCreate([
                    'maintenance_id' => $mJune->id,
                    'flat_id' => $resident->flat_id,
                ], [
                    'batch_id' => uniqid('due_'),
                    'user_id' => $resident->user_id,
                    'block_id' => $resident->block_id,
                    'amount' => $fee,
                    'total_amount' => $fee,
                    'generated_date' => "$year-06-01",
                    'paid_at' => null,
                    'payment_method' => null,
                    'transaction_id' => null,
                    'status' => 'due',
                ]);

                // July is pending
                MaintenanceBill::updateOrCreate([
                    'maintenance_id' => $mJuly->id,
                    'flat_id' => $resident->flat_id,
                ], [
                    'batch_id' => uniqid('due_'),
                    'user_id' => $resident->user_id,
                    'block_id' => $resident->block_id,
                    'amount' => $fee,
                    'total_amount' => $fee,
                    'generated_date' => "$year-07-01",
                    'paid_at' => null,
                    'payment_method' => null,
                    'transaction_id' => null,
                    'status' => 'pending',
                ]);
            } else {
                // Other half paid June and July
                MaintenanceBill::updateOrCreate([
                    'maintenance_id' => $mJune->id,
                    'flat_id' => $resident->flat_id,
                ], [
                    'batch_id' => uniqid('pay_'),
                    'user_id' => $resident->user_id,
                    'block_id' => $resident->block_id,
                    'amount' => $fee,
                    'total_amount' => $fee,
                    'generated_date' => "$year-06-01",
                    'paid_at' => "$year-06-12",
                    'payment_method' => 'cash',
                    'status' => 'paid',
                ]);

                MaintenanceBill::updateOrCreate([
                    'maintenance_id' => $mJuly->id,
                    'flat_id' => $resident->flat_id,
                ], [
                    'batch_id' => uniqid('pay_'),
                    'user_id' => $resident->user_id,
                    'block_id' => $resident->block_id,
                    'amount' => $fee,
                    'total_amount' => $fee,
                    'generated_date' => "$year-07-01",
                    'paid_at' => "$year-07-10",
                    'payment_method' => 'upi',
                    'transaction_id' => str_pad((string) rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT),
                    'status' => 'paid',
                ]);
            }
        }
    }
}
