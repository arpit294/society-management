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
        Schema::table('residents', function (Blueprint $table) {
            if (!Schema::hasColumn('residents', 'occupant_category')) {
                $table->string('occupant_category')->default('individual')->after('type'); // individual, company, partnership, retail_brand
            }
            if (!Schema::hasColumn('residents', 'company_name')) {
                $table->string('company_name')->nullable()->after('occupant_category');
            }
            if (!Schema::hasColumn('residents', 'gstin')) {
                $table->string('gstin')->nullable()->after('company_name');
            }
            if (!Schema::hasColumn('residents', 'trade_license_no')) {
                $table->string('trade_license_no')->nullable()->after('gstin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            if (Schema::hasColumn('residents', 'occupant_category')) {
                $table->dropColumn('occupant_category');
            }
            if (Schema::hasColumn('residents', 'company_name')) {
                $table->dropColumn('company_name');
            }
            if (Schema::hasColumn('residents', 'gstin')) {
                $table->dropColumn('gstin');
            }
            if (Schema::hasColumn('residents', 'trade_license_no')) {
                $table->dropColumn('trade_license_no');
            }
        });
    }
};
