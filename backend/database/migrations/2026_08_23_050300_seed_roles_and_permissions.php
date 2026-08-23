<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    // everything the code actually checks; the admin UI can add custom ones on top
    private const PERMISSIONS = [
        'access-admin-panel',
        'manage-users',
        'manage-roles',
        'manage-categories',
        'manage-tags',
        'manage-threads',
        'manage-posts',
        'manage-dummy-data',
        'moderate-threads',
        'manage-reports',
        'view-stats',
    ];

    private const MODERATOR_PERMISSIONS = [
        'moderate-threads',
        'manage-reports',
        'view-stats',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $name) {
            Permission::findOrCreate($name, 'api');
        }

        Role::findOrCreate('user', 'api');
        Role::findOrCreate('moderator', 'api')->syncPermissions(self::MODERATOR_PERMISSIONS);
        Role::findOrCreate('admin', 'api')->syncPermissions(self::PERMISSIONS);

        // carry the old single-column roles over
        User::query()->each(function (User $user) {
            $role = in_array($user->getAttributes()['role'] ?? 'user', ['user', 'moderator', 'admin'])
                ? $user->getAttributes()['role']
                : 'user';
            $user->syncRoles([$role]);
        });
    }

    public function down(): void
    {
        foreach (['user', 'moderator', 'admin'] as $name) {
            Role::where('name', $name)->where('guard_name', 'api')->delete();
        }
        Permission::whereIn('name', self::PERMISSIONS)->where('guard_name', 'api')->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
