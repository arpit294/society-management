<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('flat_id')->constrained('flats')->cascadeOnDelete();
            $table->date('invoice_date');
            $table->date('due_date');
            $table->string('bill_month', 20)->nullable();
            $table->unsignedSmallInteger('bill_year')->nullable();
            $table->enum('invoice_type', ['maintenance', 'name_transfer', 'noc', 'amenity_booking', 'penalty', 'custom'])->default('maintenance');
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('late_fee', 15, 2)->default(0.00);
            $table->decimal('discount', 15, 2)->default(0.00);
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->decimal('balance_due', 15, 2)->default(0.00);
            $table->enum('status', ['unpaid', 'partially_paid', 'paid', 'overdue', 'cancelled'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->foreignId('journal_entry_id')->nullable()->constrained('finance_journal_entries')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('finance_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('finance_invoices')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('finance_chart_of_accounts')->cascadeOnDelete();
            $table->string('item_name');
            $table->string('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_invoice_items');
        Schema::dropIfExists('finance_invoices');
    }
};
