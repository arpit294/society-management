<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // The application still uses Spatie roles, which require guard_name.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
