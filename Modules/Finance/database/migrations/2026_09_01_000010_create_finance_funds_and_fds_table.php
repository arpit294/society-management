<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_funds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['sinking_fund', 'reserve_fund', 'fixed_deposit', 'corpus_fund']);
            $table->foreignId('account_id')->constrained('finance_chart_of_accounts')->cascadeOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('finance_bank_accounts')->nullOnDelete();
            $table->decimal('principal_amount', 15, 2)->default(0.00);
            $table->decimal('current_balance', 15, 2)->default(0.00);
            $table->decimal('interest_rate', 5, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('maturity_date')->nullable();
            $table->string('certificate_no')->nullable();
            $table->enum('status', ['active', 'matured', 'closed'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_funds');
    }
};
