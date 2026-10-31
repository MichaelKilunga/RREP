<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['roles', 'branch', 'organization'])->latest();

        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        if ($request->filled('role')) {
            $roleName = $request->role;
            $query->whereHas('roles', function ($q) use ($roleName) {
                $q->where('name', $roleName)->orWhere('display_name', $roleName);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('branch')) {
            $query->where('branch_id', $request->branch);
        }

        $users = $query->paginate(20)->withQueryString();
        $roles = Role::withCount('users')->orderBy('display_name')->get();
        $branches = Branch::all();

        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'Active')->count(),
            'banned_users' => User::whereIn('status', ['Suspended', 'Banned'])->count(),
            'active_today' => User::where('last_login_at', '>=', now()->startOfDay())->count(),
        ];

        return view('users.index', compact('users', 'roles', 'branches', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'job_title' => 'nullable|string|max:255',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $this->guardRoleAssignment([$data['role_id']]);

        $user = User::create([
            'organization_id' => current_organization()?->id,
            'branch_id' => $request->branch_id ?: current_branch()?->id,
            'name' => trim("{$request->first_name} {$request->last_name}"),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'job_title' => $request->job_title,
            'password' => Hash::make($request->password),
            'status' => 'Active',
            'user_type' => 'Staff',
        ]);

        $user->roles()->attach($request->role_id, ['branch_id' => $user->branch_id]);

        return back()->with('success', __('ops.user_created', ['name' => $user->name]));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:50',
            'job_title' => 'nullable|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $user->update([
            'name' => trim("{$data['first_name']} {$data['last_name']}"),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'job_title' => $data['job_title'] ?? null,
            'branch_id' => $data['branch_id'] ?? null,
        ]);

        return back()->with('success', __('ops.user_updated', ['name' => $user->name]));
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate(['password' => 'required|string|min:8|confirmed']);

        $user->update([
            'password' => Hash::make($request->password),
            'remember_token' => null,
        ]);

        return back()->with('success', __('ops.user_password_reset', ['name' => $user->name]));
    }

    public function elevateRole(Request $request, User $user)
    {
        $request->validate([
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $this->guardRoleAssignment($request->role_ids);

        if ($user->is(auth()->user()) && ! $this->rolesGrant($request->role_ids, 'manage_roles') && ! $user->isSuperAdmin()) {
            throw ValidationException::withMessages(['role_ids' => __('ops.cannot_remove_own_role_access')]);
        }

        if ($user->isSuperAdmin() && ! in_array(Role::where('name', 'super_admin')->value('id'), array_map('intval', $request->role_ids), true)) {
            $this->guardLastSuperAdmin($user);
        }

        $user->roles()->sync($request->role_ids);

        return back()->with('success', __('ops.user_roles_updated', ['name' => $user->name]));
    }

    public function toggleStatus(Request $request, User $user)
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', __('ops.cannot_suspend_self'));
        }

        $newStatus = $user->status === 'Active' ? 'Suspended' : 'Active';
        if ($newStatus === 'Suspended') {
            $this->guardLastSuperAdmin($user);
        }

        $user->update(['status' => $newStatus]);

        return back()->with('info', $newStatus === 'Active'
            ? __('ops.user_activated', ['name' => $user->name])
            : __('ops.user_suspended', ['name' => $user->name]));
    }

    public function destroy(User $user)
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', __('ops.cannot_delete_self'));
        }

        $this->guardLastSuperAdmin($user);
        $name = $user->name;
        $user->delete();

        return back()->with('success', __('ops.user_deleted', ['name' => $name]));
    }

    public function bulkAction(Request $request)
    {
        $data = $request->validate([
            'action' => 'required|in:activate,suspend,delete',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:users,id',
        ]);

        $users = User::whereIn('id', $data['ids'])->where('id', '!=', auth()->id())->get();
        $done = 0;

        foreach ($users as $user) {
            try {
                if ($data['action'] !== 'activate') {
                    $this->guardLastSuperAdmin($user);
                }
            } catch (ValidationException) {
                continue;
            }

            match ($data['action']) {
                'activate' => $user->update(['status' => 'Active']),
                'suspend' => $user->update(['status' => 'Suspended']),
                'delete' => $user->delete(),
            };
            $done++;
        }

        return back()->with('success', __('ops.users_bulk_done', ['count' => $done]));
    }

    /**
     * Only a super admin may hand out the super-admin role.
     *
     * @param  array<int, int|string>  $roleIds
     */
    protected function guardRoleAssignment(array $roleIds): void
    {
        $superAdminRoleId = Role::where('name', 'super_admin')->value('id');

        if ($superAdminRoleId && in_array((int) $superAdminRoleId, array_map('intval', $roleIds), true) && ! auth()->user()->isSuperAdmin()) {
            throw ValidationException::withMessages(['role_ids' => __('ops.only_super_admin_grants_super_admin')]);
        }
    }

    protected function guardLastSuperAdmin(User $user): void
    {
        if (! $user->hasRole('super_admin')) {
            return;
        }

        $remaining = User::where('id', '!=', $user->id)
            ->where('status', 'Active')
            ->whereHas('roles', fn ($q) => $q->where('name', 'super_admin'))
            ->count();

        if ($remaining === 0) {
            throw ValidationException::withMessages(['user' => __('ops.last_super_admin')]);
        }
    }

    /**
     * @param  array<int, int|string>  $roleIds
     */
    protected function rolesGrant(array $roleIds, string $permission): bool
    {
        return Role::whereIn('id', $roleIds)
            ->where(fn ($q) => $q->where('name', 'super_admin')->orWhereHas('permissions', fn ($p) => $p->where('name', $permission)))
            ->exists();
    }
}
