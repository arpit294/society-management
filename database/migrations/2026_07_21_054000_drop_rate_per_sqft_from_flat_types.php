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
            if (Schema::hasColumn('flat_types', 'rate_per_sqft')) {
                $table->dropColumn('rate_per_sqft');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flat_types', function (Blueprint $table) {
            if (!Schema::hasColumn('flat_types', 'rate_per_sqft')) {
                $table->decimal('rate_per_sqft', 10, 2)->default(0)->nullable();
            }
        });
    }
};
