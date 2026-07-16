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
        Schema::table('residents', function (Blueprint $table) {
            if (!Schema::hasColumn('residents', 'business_name')) {
                $table->string('business_name')->nullable()->after('company_name');
            }
            if (!Schema::hasColumn('residents', 'contact_person')) {
                $table->string('contact_person')->nullable()->after('business_name');
            }
            if (!Schema::hasColumn('residents', 'gst_number')) {
                $table->string('gst_number')->nullable()->after('gstin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            if (Schema::hasColumn('residents', 'business_name')) {
                $table->dropColumn('business_name');
            }
            if (Schema::hasColumn('residents', 'contact_person')) {
                $table->dropColumn('contact_person');
            }
            if (Schema::hasColumn('residents', 'gst_number')) {
                $table->dropColumn('gst_number');
            }
        });
    }
};
