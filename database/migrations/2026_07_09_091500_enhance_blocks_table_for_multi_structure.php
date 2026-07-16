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
        Schema::table('blocks', function (Blueprint $table) {
            if (!Schema::hasColumn('blocks', 'block_type')) {
                $table->string('block_type')->default('residential_tower')->after('block_name'); // residential_tower, commercial_tower, row_house_lane, villa_sector, shopping_arcade, mixed_use
            }
            if (!Schema::hasColumn('blocks', 'label_type')) {
                $table->string('label_type')->default('Wing')->after('block_type'); // Wing, Tower, Block, Sector, Lane, Phase
            }
            if (Schema::hasColumn('blocks', 'total_floor')) {
                $table->integer('total_floor')->unsigned()->nullable()->default(0)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blocks', function (Blueprint $table) {
            if (Schema::hasColumn('blocks', 'block_type')) {
                $table->dropColumn('block_type');
            }
            if (Schema::hasColumn('blocks', 'label_type')) {
                $table->dropColumn('label_type');
            }
        });
    }
};
