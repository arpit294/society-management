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
            $columnsToDrop = [];
            if (Schema::hasColumn('flat_types', 'commercial_surcharge_percentage')) {
                $columnsToDrop[] = 'commercial_surcharge_percentage';
            }
            if (Schema::hasColumn('flat_types', 'description')) {
                $columnsToDrop[] = 'description';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flat_types', function (Blueprint $table) {
            if (!Schema::hasColumn('flat_types', 'commercial_surcharge_percentage')) {
                $table->decimal('commercial_surcharge_percentage', 5, 2)->default(0)->nullable();
            }
            if (!Schema::hasColumn('flat_types', 'description')) {
                $table->string('description')->nullable();
            }
        });
    }
};
