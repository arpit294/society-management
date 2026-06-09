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
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('month');
            $table->integer('year');
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->date('due_date');
            $table->decimal('total_additional_cost', 10, 2)->default(0);
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamps();
        });

        // Delete existing maintenance bills to avoid foreign key errors since we are changing the schema drastically
        \DB::table('maintenance_bills')->truncate();

        Schema::table('maintenance_bills', function (Blueprint $table) {
            $table->unsignedBigInteger('maintenance_id')->after('id');
            $table->foreign('maintenance_id')->references('id')->on('maintenances')->onDelete('cascade');
            
            // drop old columns if they exist
            if (Schema::hasColumn('maintenance_bills', 'month')) {
                $table->dropColumn('month');
            }
            if (Schema::hasColumn('maintenance_bills', 'year')) {
                $table->dropColumn('year');
            }
            // we'll keep generated_date if it exists, or just use created_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_bills', function (Blueprint $table) {
            $table->dropForeign(['maintenance_id']);
            $table->dropColumn('maintenance_id');
            $table->string('month')->nullable();
            $table->integer('year')->nullable();
        });
        
        Schema::dropIfExists('maintenances');
    }
};
