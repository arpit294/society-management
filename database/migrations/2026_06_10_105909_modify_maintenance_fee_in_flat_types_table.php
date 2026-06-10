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
            $table->renameColumn('maintenance_fee', 'owner_maintenance_fee');
        });

        Schema::table('flat_types', function (Blueprint $table) {
            $table->decimal('rental_maintenance_fee', 10, 2)->default(0)->after('owner_maintenance_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flat_types', function (Blueprint $table) {
            $table->dropColumn('rental_maintenance_fee');
        });

        Schema::table('flat_types', function (Blueprint $table) {
            $table->renameColumn('owner_maintenance_fee', 'maintenance_fee');
        });
    }
};
