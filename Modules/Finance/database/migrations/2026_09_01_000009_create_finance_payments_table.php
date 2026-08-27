<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 50)->unique();
            $table->foreignId('invoice_id')->nullable()->constrained('finance_invoices')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('flat_id')->constrained('flats')->cascadeOnDelete();
            $table->foreignId('bank_account_id')->constrained('finance_bank_accounts')->cascadeOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->enum('payment_mode', ['cash', 'cheque', 'bank_transfer', 'upi', 'advance_adjustment'])->default('bank_transfer');
            $table->string('transaction_reference')->nullable(); // Cheque / UTR
            $table->enum('status', ['completed', 'pending', 'rejected'])->default('completed');
            $table->text('remarks')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained('finance_journal_entries')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_payments');
    }
};
