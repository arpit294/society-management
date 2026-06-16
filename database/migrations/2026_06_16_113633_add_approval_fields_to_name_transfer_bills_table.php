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
            $table->date('transfer_date')->nullable()->after('amount');
            $table->boolean('is_approved')->default(false)->after('payment_slip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('name_transfer_bills', function (Blueprint $table) {
            $table->dropColumn(['transfer_date', 'is_approved']);
        });
    }
};
