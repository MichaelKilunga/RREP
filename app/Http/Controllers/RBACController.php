<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RBACController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->withCount('users')->orderByDesc('is_system')->orderBy('display_name')->get();
        $permissions = Permission::orderBy('name')->get()->groupBy('module');

        return view('settings.rbac', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'copy_from' => 'nullable|exists:roles,id',
        ]);

        $role = Role::create([
            'name' => $this->uniqueSlug($data['display_name']),
            'display_name' => $data['display_name'],
            'description' => $data['description'] ?? null,
            'is_system' => false,
        ]);

        if (! empty($data['copy_from'])) {
            $role->permissions()->sync(Role::find($data['copy_from'])->permissions()->pluck('permissions.id'));
        }

        return redirect()->route('settings.rbac')->with('success', __('ops.role_created', ['name' => $role->display_name]));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $role->update($data);

        return back()->with('success', __('ops.role_saved', ['name' => $role->display_name]));
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        if ($role->name === 'super_admin') {
            return back()->with('info', __('ops.super_admin_always_full'));
        }

        $newIds = array_map('intval', $request->input('permissions', []));
        $this->guardOwnRoleAccess($role, $newIds);

        $role->permissions()->sync($newIds);

        return back()->with('success', __('ops.role_permissions_saved', ['name' => $role->display_name, 'count' => count($newIds)]));
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return back()->with('error', __('ops.role_system_locked'));
        }

        if ($role->users()->exists()) {
            return back()->with('error', __('ops.role_has_users', ['count' => $role->users()->count()]));
        }

        $name = $role->display_name;
        $role->permissions()->detach();
        $role->delete();

        return back()->with('success', __('ops.role_deleted', ['name' => $name]));
    }

    /**
     * Stop an admin from removing "manage roles" from the only role that gives it to them.
     *
     * @param  array<int, int>  $newPermissionIds
     */
    protected function guardOwnRoleAccess(Role $role, array $newPermissionIds): void
    {
        $user = auth()->user();
        if ($user->isSuperAdmin() || ! $user->roles->contains('id', $role->id)) {
            return;
        }

        $manageRolesId = Permission::where('name', 'manage_roles')->value('id');
        if (in_array((int) $manageRolesId, $newPermissionIds, true)) {
            return;
        }

        $keptElsewhere = $user->roles
            ->where('id', '!=', $role->id)
            ->contains(fn (Role $other) => $other->permissions->contains('id', $manageRolesId));

        if (! $keptElsewhere) {
            throw ValidationException::withMessages(['permissions' => __('ops.cannot_remove_own_role_access')]);
        }
    }

    protected function uniqueSlug(string $displayName): string
    {
        $base = Str::slug($displayName, '_') ?: 'role';
        $slug = $base;
        $i = 2;
        while (Role::where('name', $slug)->exists()) {
            $slug = $base.'_'.$i++;
        }

        return $slug;
    }
}
