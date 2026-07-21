<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('flats', function (Blueprint $table) {
            $columnsToDrop = [];
            
            if (Schema::hasColumn('flats', 'plot_area_sqyards')) {
                $columnsToDrop[] = 'plot_area_sqyards';
            }
            if (Schema::hasColumn('flats', 'electricity_meter_no')) {
                $columnsToDrop[] = 'electricity_meter_no';
            }
            if (Schema::hasColumn('flats', 'water_meter_no')) {
                $columnsToDrop[] = 'water_meter_no';
            }
            if (Schema::hasColumn('flats', 'has_commercial_license')) {
                $columnsToDrop[] = 'has_commercial_license';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flats', function (Blueprint $table) {
            if (!Schema::hasColumn('flats', 'plot_area_sqyards')) {
                $table->decimal('plot_area_sqyards', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('flats', 'electricity_meter_no')) {
                $table->string('electricity_meter_no')->nullable();
            }
            if (!Schema::hasColumn('flats', 'water_meter_no')) {
                $table->string('water_meter_no')->nullable();
            }
            if (!Schema::hasColumn('flats', 'has_commercial_license')) {
                $table->boolean('has_commercial_license')->default(false);
            }
        });
    }
};
