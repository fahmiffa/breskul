<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\MenuService;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    /**
     * Tampilkan matriks role & permission
     */
    public function index(Request $request)
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all()->groupBy('group');

        return view('master.role_permission.index', [
            'title'       => 'Hak Akses Role & Menu',
            'roles'       => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Update hak akses suatu role (AJAX / Form Post)
     */
    public function updateRolePermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permission_ids'   => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync($validated['permission_ids'] ?? []);

        // Hapus cache RBAC user yang memiliki role ini agar perubahan langsung berlaku
        $users = $role->users()->get();
        foreach ($users as $user) {
            $user->clearRbacCache();
        }

        // Hapus cache menu
        app(MenuService::class)->clearCache();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Hak akses untuk role '{$role->display_name}' berhasil diperbarui.",
            ]);
        }

        return redirect()->route('dashboard.master.role-permission.index')
            ->with('success', "Hak akses untuk role '{$role->display_name}' berhasil diperbarui.");
    }

    /**
     * Quick toggle single permission via AJAX
     */
    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'role_id'       => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
            'allowed'       => 'required|boolean',
        ]);

        $role = Role::findOrFail($validated['role_id']);
        $permissionId = $validated['permission_id'];

        if ($validated['allowed']) {
            $role->permissions()->syncWithoutDetaching([$permissionId]);
        } else {
            $role->permissions()->detach([$permissionId]);
        }

        // Clear cache user terkait
        foreach ($role->users as $user) {
            $user->clearRbacCache();
        }
        app(MenuService::class)->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Hak akses berhasil diperbarui.',
        ]);
    }
}
