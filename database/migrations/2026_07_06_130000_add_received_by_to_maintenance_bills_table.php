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
            if (!Schema::hasColumn('maintenance_bills', 'received_by')) {
                $table->foreignId('received_by')->nullable()->after('payment_slip')->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_bills', function (Blueprint $table) {
            if (Schema::hasColumn('maintenance_bills', 'received_by')) {
                $table->dropForeign(['received_by']);
                $table->dropColumn('received_by');
            }
        });
    }
};
