<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

        $users = $query->paginate(20);
        $roles = Role::all();
        $branches = Branch::all();

        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'Active')->count(),
            'banned_users' => User::whereIn('status', ['Suspended', 'Banned'])->count(),
            'field_surveyors' => User::whereHas('roles', fn ($q) => $q->where('name', 'like', '%survey%')->orWhere('display_name', 'like', '%survey%'))->count(),
        ];

        return view('users.index', compact('users', 'roles', 'branches', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $user = User::create([
            'organization_id' => current_organization()?->id,
            'branch_id' => $request->branch_id ?: current_branch()?->id,
            'name' => trim("{$request->first_name} {$request->last_name}"),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'status' => 'Active',
            'user_type' => 'Staff',
        ]);

        $user->roles()->attach($request->role_id, ['branch_id' => $user->branch_id]);

        return back()->with('success', "User account created for {$user->name}!");
    }

    public function elevateRole(Request $request, User $user)
    {
        $request->validate([
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $user->roles()->sync($request->role_ids);

        return back()->with('success', "Personnel roles updated for {$user->name}!");
    }

    public function toggleStatus(Request $request, User $user)
    {
        $newStatus = $user->status === 'Active' ? 'Suspended' : 'Active';
        $user->update(['status' => $newStatus]);

        $actionText = $newStatus === 'Active' ? 'activated' : 'banned/suspended';

        return back()->with('info', "User {$user->name} has been {$actionText}.");
    }
}
