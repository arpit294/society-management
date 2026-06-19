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
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded.');
        }

        Schema::table($tableNames['permissions'], function (Blueprint $table) {
            $table->string('module_name')->nullable()->after('guard_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded.');
        }

        Schema::table($tableNames['permissions'], function (Blueprint $table) {
            $table->dropColumn('module_name');
        });
    }
};
