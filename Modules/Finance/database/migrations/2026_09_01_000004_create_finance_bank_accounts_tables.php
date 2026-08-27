<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('finance_chart_of_accounts')->cascadeOnDelete();
            $table->string('bank_name');
            $table->string('account_name');
            $table->string('account_number');
            $table->string('ifsc_code', 20)->nullable();
            $table->string('branch')->nullable();
            $table->enum('account_type', ['savings', 'current', 'escrow', 'cash'])->default('savings');
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->decimal('current_balance', 15, 2)->default(0.00);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('finance_bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('finance_bank_accounts')->cascadeOnDelete();
            $table->date('transaction_date');
            $table->enum('type', ['deposit', 'withdrawal']);
            $table->decimal('amount', 15, 2);
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_reconciled')->default(false);
            $table->timestamp('reconciled_at')->nullable();
            $table->foreignId('journal_entry_id')->nullable()->constrained('finance_journal_entries')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_bank_transactions');
        Schema::dropIfExists('finance_bank_accounts');
    }
};
