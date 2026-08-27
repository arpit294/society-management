<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number', 50)->unique();
            $table->foreignId('fiscal_year_id')->nullable()->constrained('finance_fiscal_years')->nullOnDelete();
            $table->date('entry_date');
            $table->nullableMorphs('reference');
            $table->text('description')->nullable();
            $table->decimal('total_debit', 15, 2)->default(0.00);
            $table->decimal('total_credit', 15, 2)->default(0.00);
            $table->enum('status', ['draft', 'posted', 'void'])->default('posted');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('finance_journal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('finance_journal_entries')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('finance_chart_of_accounts')->cascadeOnDelete();
            $table->decimal('debit', 15, 2)->default(0.00);
            $table->decimal('credit', 15, 2)->default(0.00);
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_journal_items');
        Schema::dropIfExists('finance_journal_entries');
    }
};
