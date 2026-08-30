<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class RBACController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all()->groupBy('module');

        return view('settings.rbac', compact('roles', 'permissions'));
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $role->permissions()->sync($request->input('permissions', []));

        return back()->with('success', "Permissions updated for role: {$role->display_name}");
    }
}
