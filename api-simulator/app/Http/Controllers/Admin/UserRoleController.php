<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserRole;
use App\Support\AdminPermissionCatalog;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    public function index()
    {
        $roles = UserRole::query()
            ->orderBy('id')
            ->get();

        return view('admin.user-roles.index', [
            'roles' => $roles,
            'activeCount' => $roles->where('status', 'active')->count(),
            'inactiveCount' => $roles->where('status', 'inactive')->count(),
        ]);
    }

    public function edit(UserRole $userRole)
    {
        return view('admin.user-roles.edit', [
            'role' => $userRole,
            'permissionSections' => AdminPermissionCatalog::sections(),
            'selectedPermissions' => $userRole->permissions ?? AdminPermissionCatalog::allKeys(),
        ]);
    }

    public function update(Request $request, UserRole $userRole)
    {
        $validated = $request->validate([
            'role_name' => 'required|string|max:120',
            'status' => 'required|in:active,inactive',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $validPermissionKeys = AdminPermissionCatalog::allKeys();
        $selectedPermissions = array_values(array_intersect(
            $validPermissionKeys,
            $validated['permissions'] ?? []
        ));

        $userRole->update([
            'role_name' => $validated['role_name'],
            'status' => $validated['status'],
            'permissions' => $selectedPermissions,
        ]);

        return redirect()
            ->route('admin.user-roles')
            ->with('success', 'User role updated successfully.');
    }
}
