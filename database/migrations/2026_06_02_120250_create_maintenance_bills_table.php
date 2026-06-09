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
        Schema::create('maintenance_bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('block_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('flat_id');
            $table->decimal('amount', 10, 2);
            $table->decimal('penalty_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('month');
            $table->integer('year');
            $table->date('due_date')->nullable();
            $table->date('generated_date');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->enum('status', ['paid', 'due', 'pending'])->default('due');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_bills');
    }
};
