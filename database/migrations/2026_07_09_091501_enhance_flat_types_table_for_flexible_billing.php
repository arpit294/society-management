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
        Schema::table('flat_types', function (Blueprint $table) {
            if (!Schema::hasColumn('flat_types', 'category_type')) {
                $table->string('category_type')->default('residential')->after('name'); // residential, commercial, institutional, industrial
            }
            if (!Schema::hasColumn('flat_types', 'calculation_method')) {
                $table->string('calculation_method')->default('fixed')->after('rental_maintenance_fee'); // fixed, per_sqft, per_sqyard, hybrid
            }
            if (!Schema::hasColumn('flat_types', 'rate_per_sqft')) {
                $table->decimal('rate_per_sqft', 10, 2)->unsigned()->default(0)->after('calculation_method');
            }
            if (!Schema::hasColumn('flat_types', 'commercial_surcharge_percentage')) {
                $table->decimal('commercial_surcharge_percentage', 5, 2)->unsigned()->default(0)->after('rate_per_sqft');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flat_types', function (Blueprint $table) {
            if (Schema::hasColumn('flat_types', 'category_type')) {
                $table->dropColumn('category_type');
            }
            if (Schema::hasColumn('flat_types', 'calculation_method')) {
                $table->dropColumn('calculation_method');
            }
            if (Schema::hasColumn('flat_types', 'rate_per_sqft')) {
                $table->dropColumn('rate_per_sqft');
            }
            if (Schema::hasColumn('flat_types', 'commercial_surcharge_percentage')) {
                $table->dropColumn('commercial_surcharge_percentage');
            }
        });
    }
};
