<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionsController extends Controller
{
    public function permissionsListar(Request $request)
    {
        $search = $request->get('search', '');
        $group = $request->get('group', '');

        $query = Permission::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('group_name', 'like', "%{$search}%");
            });
        }

        if ($group) {
            $query->where('group_name', $group);
        }

        $permissions = $query->paginate(10)->appends([
            'search' => $search,
            'group' => $group
        ]);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.permissions.partials.table-body', compact('permissions'))->render(),
                'pagination' => $permissions->links('pagination::bootstrap-5')->toHtml(),
                'filters' => [
                    'search' => $search,
                    'group' => $group
                ]
            ]);
        }

        $permissionGroups = Permission::select('group_name')
            ->whereNotNull('group_name')
            ->distinct()
            ->pluck('group_name');

        return view('admin.permissions.listar', compact('permissions', 'permissionGroups'));
    }

    public function permissionsRegistrar(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name',
            'group_name' => 'nullable|string',
        ]);

        $permission = Permission::create([
            'name' => $request->name,
            'group_name' => $request->group_name,
            'guard_name' => 'web'
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Permiso registrado correctamente.',
            'permission' => $permission
        ]);
    }

    public function verificarNombre(Request $request)
    {
        $name = $request->input('name');
        $id = $request->input('id');

        $query = Permission::where('name', $name);

        if ($id && is_numeric($id) && $id > 0) {
            $query->where('id', '!=', $id);
        }

        return response()->json(['exists' => $query->exists()]);
    }

    public function verificarNombreEdicion(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $name = trim($request->input('name'));
        $id = $request->input('id');

        $id = is_numeric($id) && $id > 0 ? (int) $id : null;

        $exists = Permission::where('name', $name)
            ->when($id, function ($query) use ($id) {
                return $query->where('id', '!=', $id);
            })
            ->exists();

        return response()->json(['exists' => $exists]);
    }

    public function permissionsModificar(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:permissions,id',
            'name' => 'required|unique:permissions,name,' . $request->id,
        ]);

        Permission::where('id', $request->id)->update([
            'name' => $request->name,
            'group_name' => $request->group_name
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Permiso actualizado correctamente.'
        ]);
    }

    public function permissionsEliminar(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:permissions,id'
        ]);

        Permission::where('id', $request->id)->delete();

        return response()->json([
            'success' => true,
            'msg' => 'Permiso eliminado correctamente.'
        ]);
    }

    public function getGroups()
    {
        try {
            $groups = Permission::distinct()
                ->whereNotNull('group_name')
                ->where('group_name', '!=', '')
                ->pluck('group_name')
                ->toArray();

            sort($groups);

            return response()->json([
                'success' => true,
                'groups' => $groups
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los grupos'
            ], 500);
        }
    }
}
