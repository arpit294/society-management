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
        Schema::table('flat_types', function (Blueprint $table) {
            $table->decimal('penalty_per_day', 10, 2)->default(0)->after('maintenance_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flat_types', function (Blueprint $table) {
            $table->dropColumn('penalty_per_day');
        });
    }
};
