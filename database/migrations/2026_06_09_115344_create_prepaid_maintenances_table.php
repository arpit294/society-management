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
        Schema::create('prepaid_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('flat_id')->constrained('flats')->onDelete('cascade');
            $table->string('month');
            $table->integer('year')->unsigned();
            $table->string('end_month')->nullable();
            $table->integer('end_year')->unsigned()->nullable();
            $table->integer('months')->unsigned()->default(1);
            $table->integer('months_used')->unsigned()->default(0);
            $table->decimal('amount_paid', 10, 2)->unsigned();
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('payment_slip')->nullable();
            $table->enum('status', ['unused', 'used'])->default('unused');
            $table->unsignedBigInteger('maintenance_bill_id')->nullable();
            $table->foreign('maintenance_bill_id')->references('id')->on('maintenance_bills')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prepaid_maintenances');
    }
};
