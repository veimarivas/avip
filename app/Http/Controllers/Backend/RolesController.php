<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    public function rolesListar(Request $request)
    {
        $search = $request->get('search', '');
        $query = Role::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $roles = $query->paginate(10)->appends(['search' => $search]);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.roles.partials.table-body', compact('roles'))->render(),
                'pagination' => $roles->links('pagination::bootstrap-5')->toHtml()
            ]);
        }

        return view('admin.roles.listar', compact('roles'));
    }

    public function rolesRegistrar(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web'
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Rol registrado correctamente.',
            'role' => $role
        ]);
    }

    public function verificarNombre(Request $request)
    {
        $name = $request->input('name');
        $id = $request->input('id');

        $query = Role::where('name', $name);

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

        $exists = Role::where('name', $name)
            ->when($id, function ($query) use ($id) {
                return $query->where('id', '!=', $id);
            })
            ->exists();

        return response()->json(['exists' => $exists]);
    }

    public function rolesModificar(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:roles,id',
            'name' => 'required|unique:roles,name,' . $request->id,
        ]);

        Role::where('id', $request->id)->update([
            'name' => $request->name
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Rol actualizado correctamente.'
        ]);
    }

    public function rolesEliminar(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:roles,id'
        ]);

        Role::where('id', $request->id)->delete();

        return response()->json([
            'success' => true,
            'msg' => 'Rol eliminado correctamente.'
        ]);
    }
}
