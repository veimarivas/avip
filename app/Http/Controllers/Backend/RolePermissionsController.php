<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $query = Role::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $roles = $query->paginate(10)->appends(['search' => $search]);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.role-permissions.partials.roles-table', compact('roles'))->render(),
                'pagination' => $roles->links('pagination::bootstrap-5')->toHtml()
            ]);
        }

        return view('admin.role-permissions.index', compact('roles'));
    }

    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        // Obtener todos los grupos de permisos
        $permissionGroups = Permission::select('group_name')
            ->whereNotNull('group_name')
            ->groupBy('group_name')
            ->pluck('group_name');

        // Obtener IDs de permisos asignados al rol
        $assignedPermissionIds = $role->permissions->pluck('id')->toArray();

        // Preparar los permisos disponibles por grupo
        $availablePermissionsByGroup = [];

        foreach ($permissionGroups as $group) {
            // Obtener permisos por grupo que NO están asignados al rol
            $availablePermissions = Permission::where('group_name', $group)
                ->whereNotIn('id', $assignedPermissionIds)
                ->get();

            $availablePermissionsByGroup[$group] = $availablePermissions;
        }

        return view('admin.role-permissions.show', compact(
            'role',
            'permissionGroups',
            'availablePermissionsByGroup'
        ));
    }

    public function assignPermission(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $role = Role::findOrFail($request->role_id);
        $permission = Permission::findOrFail($request->permission_id);

        $role->givePermissionTo($permission);

        // Limpiar caché de permisos
        $role->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'msg' => 'Permiso asignado correctamente al rol.',
            'permission' => [
                'id' => $permission->id,
                'name' => $permission->name,
                'group_name' => $permission->group_name
            ]
        ]);
    }

    public function revokePermission(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $role = Role::findOrFail($request->role_id);
        $permission = Permission::findOrFail($request->permission_id);

        $role->revokePermissionTo($permission);

        // Limpiar caché de permisos
        $role->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'msg' => 'Permiso removido correctamente del rol.'
        ]);
    }

    protected function getPermissionGroups()
    {
        return Permission::select('group_name')
            ->whereNotNull('group_name')
            ->groupBy('group_name')
            ->pluck('group_name');
    }
}
