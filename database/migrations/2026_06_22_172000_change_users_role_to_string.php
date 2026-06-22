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
        // Change the users.role column from ENUM to VARCHAR to allow dynamic role names like 'Admin'
        DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(100) DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the previous ENUM values (including secretary)
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner', 'rental', 'security', 'committee_member', 'secretary') DEFAULT NULL");
    }
};
