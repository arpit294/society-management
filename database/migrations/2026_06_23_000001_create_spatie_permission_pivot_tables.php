<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('permissions')) {
            Schema::create('permissions', static function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
                $table->unique(['name', 'guard_name']);
            });
        }

        if (! Schema::hasTable('roles')) {
            Schema::create('roles', static function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
                $table->unique(['name', 'guard_name']);
            });
        }

        if (! Schema::hasTable('model_has_permissions')) {
            Schema::create('model_has_permissions', static function (Blueprint $table) {
                $table->unsignedBigInteger('permission_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');

                $table->foreign('permission_id')
                    ->references('id')
                    ->on('permissions')
                    ->cascadeOnDelete();

                $table->primary(['permission_id', 'model_id', 'model_type'], 'model_has_permissions_permission_id_model_id_model_type_primary');
            });
        }

        if (! Schema::hasTable('model_has_roles')) {
            Schema::create('model_has_roles', static function (Blueprint $table) {
                $table->unsignedBigInteger('role_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');

                $table->foreign('role_id')
                    ->references('id')
                    ->on('roles')
                    ->cascadeOnDelete();

                $table->primary(['role_id', 'model_id', 'model_type'], 'model_has_roles_role_id_model_id_model_type_primary');
            });
        }

        if (! Schema::hasTable('role_has_permissions')) {
            Schema::create('role_has_permissions', static function (Blueprint $table) {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('role_id');

                $table->foreign('permission_id')
                    ->references('id')
                    ->on('permissions')
                    ->cascadeOnDelete();

                $table->foreign('role_id')
                    ->references('id')
                    ->on('roles')
                    ->cascadeOnDelete();

                $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
            });
        }

        Cache::store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('role_has_permissions');
    }
};
