<?php

namespace App\Modules\Forum\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminRoleController extends Controller
{
    // these ship with the app; renaming or deleting them would break things
    private const BUILT_IN_ROLES = ['user', 'moderator', 'admin'];

    // permissions the code itself checks - they can't be deleted from the UI
    private const CORE_PERMISSIONS = [
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

    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('manage-roles'), 403);

        $roles = Role::where('guard_name', 'api')
            ->with('permissions:id,name')
            ->withCount('users')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $roles->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
                'users_count' => $role->users_count,
                'built_in' => in_array($role->name, self::BUILT_IN_ROLES),
                'locked' => $role->name === 'admin',
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('manage-roles'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('roles', 'name')->where('guard_name', 'api')],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'api')],
        ]);

        $role = Role::create(['name' => Str::slug($data['name']), 'guard_name' => 'api']);
        $role->syncPermissions($data['permissions'] ?? []);

        return response()->json(['data' => ['id' => $role->id, 'name' => $role->name]], 201);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        abort_unless($request->user()->can('manage-roles'), 403);
        abort_if($role->name === 'admin', 422, 'The admin role always has every permission.');

        $data = $request->validate([
            'permissions' => ['present', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'api')],
        ]);

        $role->syncPermissions($data['permissions']);

        return response()->json(['data' => ['name' => $role->name, 'permissions' => $role->permissions->pluck('name')]]);
    }

    public function destroy(Request $request, Role $role): Response
    {
        abort_unless($request->user()->can('manage-roles'), 403);
        abort_if(in_array($role->name, self::BUILT_IN_ROLES), 422, 'Built-in roles cannot be deleted.');

        // anyone holding this role drops back to a plain user
        User::role($role->name)->get()->each(function (User $user) {
            $user->syncRoles(['user']);
            $user->forceFill(['role' => 'user'])->save();
        });

        $role->delete();

        return response()->noContent();
    }

    public function permissions(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('manage-roles'), 403);

        $permissions = Permission::where('guard_name', 'api')
            ->with('roles:id,name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $permissions->map(fn (Permission $permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
                'roles' => $permission->roles->pluck('name'),
                'core' => in_array($permission->name, self::CORE_PERMISSIONS),
            ]),
        ]);
    }

    public function storePermission(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('manage-roles'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('permissions', 'name')->where('guard_name', 'api')],
        ]);

        $permission = Permission::create(['name' => Str::slug($data['name']), 'guard_name' => 'api']);

        // the admin role holds every permission, custom ones included
        Role::findByName('admin', 'api')->givePermissionTo($permission);

        return response()->json(['data' => ['id' => $permission->id, 'name' => $permission->name]], 201);
    }

    public function destroyPermission(Request $request, Permission $permission): Response
    {
        abort_unless($request->user()->can('manage-roles'), 403);
        abort_if(in_array($permission->name, self::CORE_PERMISSIONS), 422, 'Core permissions cannot be deleted.');

        $permission->delete();

        return response()->noContent();
    }
}
