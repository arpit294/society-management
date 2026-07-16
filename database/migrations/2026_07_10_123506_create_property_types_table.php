<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('property_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->timestamps();
        });

        // Seed default multi-structure property types
        if (DB::table('property_types')->count() === 0) {
            DB::table('property_types')->insert([
                [
                    'name' => 'Flat Residential Society (Vertical Towers)',
                    'code' => 'flat_residential',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Commercial Shopping Complex / Arcade / IT Park',
                    'code' => 'commercial_complex',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Bungalows / Villas / Tenements / Row Houses',
                    'code' => 'rowhouse_villa',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Mixed-Use (Flats + Commercial Shops + Villas)',
                    'code' => 'mixed_use',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_types');
    }
};
