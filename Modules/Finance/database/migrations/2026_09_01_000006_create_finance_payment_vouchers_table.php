<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_payment_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number', 50)->unique();
            $table->foreignId('vendor_bill_id')->nullable()->constrained('finance_vendor_bills')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('finance_vendors')->nullOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('finance_bank_accounts')->nullOnDelete();
            $table->date('voucher_date');
            $table->decimal('amount', 15, 2);
            $table->enum('payment_mode', ['bank_transfer', 'cheque', 'cash', 'upi'])->default('bank_transfer');
            $table->string('reference_no')->nullable(); // Cheque No, UTR, etc.
            $table->text('description')->nullable();
            $table->enum('approval_status', ['draft', 'submitted', 'approved', 'rejected', 'paid'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('journal_entry_id')->nullable()->constrained('finance_journal_entries')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_payment_vouchers');
    }
};
