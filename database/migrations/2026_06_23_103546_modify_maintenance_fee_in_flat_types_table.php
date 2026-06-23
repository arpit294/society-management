<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('flat_types', function (Blueprint $table) {
            $table->renameColumn('maintenance_fee', 'owner_maintenance_fee');
            $table->decimal('rental_maintenance_fee', 10, 2)->default(0)->after('owner_maintenance_fee');
        });

        DB::table('flat_types')->get()->each(function ($type) {
            DB::table('flat_types')
                ->where('id', $type->id)
                ->update(['rental_maintenance_fee' => $type->owner_maintenance_fee + 500]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flat_types', function (Blueprint $table) {
            $table->dropColumn('rental_maintenance_fee');
            $table->renameColumn('owner_maintenance_fee', 'maintenance_fee');
        });
    }
};
