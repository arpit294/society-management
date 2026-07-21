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
            $columnsToDrop = [];
            
            if (Schema::hasColumn('residents', 'gstin')) {
                $columnsToDrop[] = 'gstin';
            }
            if (Schema::hasColumn('residents', 'gst_number')) {
                $columnsToDrop[] = 'gst_number';
            }
            if (Schema::hasColumn('residents', 'trade_license_no')) {
                $columnsToDrop[] = 'trade_license_no';
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
        Schema::table('residents', function (Blueprint $table) {
            if (!Schema::hasColumn('residents', 'gstin')) {
                $table->string('gstin')->nullable();
            }
            if (!Schema::hasColumn('residents', 'gst_number')) {
                $table->string('gst_number')->nullable();
            }
            if (!Schema::hasColumn('residents', 'trade_license_no')) {
                $table->string('trade_license_no')->nullable();
            }
        });
    }
};
