<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('service_type'); // e.g. Security, Housekeeping, Lift AMC
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('gstin', 30)->nullable();
            $table->string('pan_number', 20)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('bank_ifsc', 20)->nullable();
            $table->text('address')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('finance_vendor_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number');
            $table->foreignId('vendor_id')->constrained('finance_vendors')->cascadeOnDelete();
            $table->foreignId('expense_account_id')->constrained('finance_chart_of_accounts')->cascadeOnDelete();
            $table->date('bill_date');
            $table->date('due_date');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->decimal('balance_due', 15, 2)->default(0.00);
            $table->enum('status', ['unpaid', 'partially_paid', 'paid', 'cancelled'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_vendor_bills');
        Schema::dropIfExists('finance_vendors');
    }
};
