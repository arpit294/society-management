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
        // First drop the old columns that we are replacing
        Schema::table('flat_documents', function (Blueprint $table) {
            $table->dropColumn(['title', 'description', 'file_path', 'file_type', 'file_size']);
        });

        // Then add the new columns
        Schema::table('flat_documents', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('flat_id')->constrained('users')->cascadeOnDelete();
            $table->json('documents')->nullable()->after('resident_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flat_documents', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'documents']);
            
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
        });
    }
};
