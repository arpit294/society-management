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
            if (!Schema::hasColumn('flats', 'unit_type')) {
                $table->string('unit_type')->default('flat')->after('block_id'); // flat, shop, office, showroom, row_house, villa, tenement, plot
            }
            if (!Schema::hasColumn('flats', 'area_sqft')) {
                $table->decimal('area_sqft', 10, 2)->unsigned()->nullable()->after('flat_type_id');
            }
            if (!Schema::hasColumn('flats', 'plot_area_sqyards')) {
                $table->decimal('plot_area_sqyards', 10, 2)->unsigned()->nullable()->after('area_sqft');
            }
            if (!Schema::hasColumn('flats', 'electricity_meter_no')) {
                $table->string('electricity_meter_no')->nullable()->after('plot_area_sqyards');
            }
            if (!Schema::hasColumn('flats', 'water_meter_no')) {
                $table->string('water_meter_no')->nullable()->after('electricity_meter_no');
            }
            if (!Schema::hasColumn('flats', 'has_commercial_license')) {
                $table->boolean('has_commercial_license')->default(false)->after('water_meter_no');
            }
            if (Schema::hasColumn('flats', 'floor_no')) {
                $table->string('floor_no')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flats', function (Blueprint $table) {
            if (Schema::hasColumn('flats', 'unit_type')) {
                $table->dropColumn('unit_type');
            }
            if (Schema::hasColumn('flats', 'area_sqft')) {
                $table->dropColumn('area_sqft');
            }
            if (Schema::hasColumn('flats', 'plot_area_sqyards')) {
                $table->dropColumn('plot_area_sqyards');
            }
            if (Schema::hasColumn('flats', 'electricity_meter_no')) {
                $table->dropColumn('electricity_meter_no');
            }
            if (Schema::hasColumn('flats', 'water_meter_no')) {
                $table->dropColumn('water_meter_no');
            }
            if (Schema::hasColumn('flats', 'has_commercial_license')) {
                $table->dropColumn('has_commercial_license');
            }
        });
    }
};
