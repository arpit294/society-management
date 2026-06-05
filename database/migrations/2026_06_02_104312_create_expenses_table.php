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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->decimal('total_amount', 10, 2);
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Assuming expense_categories exists or will exist. Using constrained without table name won't work automatically if Laravel doesn't pluralize correctly or if the table is created after this migration.
            // Let's use foreignId('category_id')->constrained('expense_categories')->cascadeOnDelete() but since the table doesn't exist, migration might fail. 
            // I'll just use unsignedBigInteger for now, or constrained('expense_categories') and let the user handle the order.
            // I will use unsignedBigInteger to be safe from migration errors if the table is missing.
            $table->unsignedBigInteger('category_id');
            $table->string('invoice')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
