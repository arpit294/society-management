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
        // 1. Add 'permissions' column to roles table
        Schema::table('roles', function (Blueprint $table) {
            $table->json('permissions')->nullable()->after('guard_name');
        });

        // 2. Migrate existing permissions data from role_has_permissions to roles.permissions
        $roles = DB::table('roles')->get();
        foreach ($roles as $role) {
            $permissionIds = DB::table('role_has_permissions')
                ->where('role_id', $role->id)
                ->pluck('permission_id');
                
            $permissions = DB::table('permissions')
                ->whereIn('id', $permissionIds)
                ->pluck('name')
                ->toArray();
                
            DB::table('roles')->where('id', $role->id)->update([
                'permissions' => json_encode($permissions)
            ]);
        }

        // 3. Drop Spatie tables
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('permissions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For simplicity, we are not building a full down migration to restore Spatie.
        // It's a one-way refactor.
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('permissions');
        });
    }
};
