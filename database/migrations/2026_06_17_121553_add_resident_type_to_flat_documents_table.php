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
        Schema::table('flat_documents', function (Blueprint $table) {
            $table->enum('resident_type', ['owner', 'rental', 'both'])->default('owner')->after('flat_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flat_documents', function (Blueprint $table) {
            $table->dropColumn('resident_type');
        });
    }
};
