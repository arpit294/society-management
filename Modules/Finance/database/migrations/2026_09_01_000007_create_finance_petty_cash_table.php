<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_petty_cash_entries', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date');
            $table->string('voucher_no')->nullable();
            $table->enum('type', ['expense', 'replenishment'])->default('expense');
            $table->decimal('amount', 15, 2);
            $table->foreignId('account_id')->constrained('finance_chart_of_accounts')->cascadeOnDelete();
            $table->string('paid_to');
            $table->string('purpose');
            $table->string('receipt_attachment')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained('finance_journal_entries')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_petty_cash_entries');
    }
};
