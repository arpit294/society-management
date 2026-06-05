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
        Schema::table('flats', function (Blueprint $table) {
            $table->dropColumn(['flat_type', 'maintenance_amount']);
            $table->unsignedBigInteger('flat_type_id')->nullable()->after('floor_no');
            
            // If you want to add foreign key constraint:
            // $table->foreign('flat_type_id')->references('id')->on('flat_types')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flats', function (Blueprint $table) {
            $table->dropColumn('flat_type_id');
            $table->string('flat_type')->nullable();
            $table->decimal('maintenance_amount', 10, 2)->default(0);
        });
    }
};
