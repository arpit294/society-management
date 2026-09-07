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
        Schema::table('name_transfer_bills', function (Blueprint $table) {
            if (!Schema::hasColumn('name_transfer_bills', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('is_approved')->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('name_transfer_bills', function (Blueprint $table) {
            if (Schema::hasColumn('name_transfer_bills', 'approved_by')) {
                $table->dropForeign(['approved_by']);
                $table->dropColumn('approved_by');
            }
        });
    }
};
