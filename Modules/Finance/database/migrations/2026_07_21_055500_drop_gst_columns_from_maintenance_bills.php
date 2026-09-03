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
        Schema::table('maintenance_bills', function (Blueprint $table) {
            $columnsToDrop = [];
            
            if (Schema::hasColumn('maintenance_bills', 'gst_percentage')) {
                $columnsToDrop[] = 'gst_percentage';
            }
            if (Schema::hasColumn('maintenance_bills', 'gst_amount')) {
                $columnsToDrop[] = 'gst_amount';
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
        Schema::table('maintenance_bills', function (Blueprint $table) {
            if (!Schema::hasColumn('maintenance_bills', 'gst_percentage')) {
                $table->decimal('gst_percentage', 5, 2)->default(0)->nullable();
            }
            if (!Schema::hasColumn('maintenance_bills', 'gst_amount')) {
                $table->decimal('gst_amount', 10, 2)->default(0)->nullable();
            }
        });
    }
};
