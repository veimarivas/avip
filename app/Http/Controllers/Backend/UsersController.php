<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Persona;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    public function listar(Request $request)
    {
        $search = $request->get('search', '');
        $estado = $request->get('estado', '');

        $query = User::with('persona');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('persona', function ($q2) use ($search) {
                        $q2->where('carnet', 'like', "%{$search}%")
                            ->orWhere('nombres', 'like', "%{$search}%")
                            ->orWhere('apellido_paterno', 'like', "%{$search}%")
                            ->orWhere('apellido_materno', 'like', "%{$search}%");
                    });
            });
        }

        if ($estado && in_array($estado, ['Activo', 'Inactivo'])) {
            $query->where('estado', $estado);
        }

        $users = $query->paginate(10)->appends([
            'search' => $search,
            'estado' => $estado
        ]);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.users.partials.table-body', compact('users'))->render(),
                'pagination' => $users->links('pagination::bootstrap-5')->toHtml(),
                'filters' => [
                    'search' => $search,
                    'estado' => $estado
                ]
            ]);
        }

        $roles = Role::all();

        return view('admin.users.listar', compact('users', 'roles'));
    }

    public function verificarEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'id' => 'nullable|exists:users,id'
        ]);

        $email = $request->input('email');
        $id = $request->input('id');

        $query = User::where('email', $email);

        if ($id) {
            $query->where('id', '!=', $id);
        }

        return response()->json(['exists' => $query->exists()]);
    }

    public function actualizar(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $request->id,
            'role' => 'required|exists:roles,name',
            'estado' => 'required|in:Activo,Inactivo'
        ]);

        $user = User::findOrFail($request->id);

        // Actualizar información básica
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'estado' => $request->estado
        ]);

        // Sincronizar rol
        $user->syncRoles([$request->role]);

        return response()->json([
            'success' => true,
            'msg' => 'Usuario actualizado correctamente.'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($request->id);

        // Generar nueva contraseña aleatoria
        $newPassword = Str::random(10);
        $user->password = Hash::make($newPassword);
        $user->save();

        // Enviar correo con nueva contraseña (opcional)
        // Mail::to($user->email)->send(new UserPasswordReset($newPassword));

        return response()->json([
            'success' => true,
            'msg' => 'Contraseña restablecida correctamente.',
            'password' => $newPassword,
            'username' => $user->email
        ]);
    }

    public function obtenerUserData(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id'
        ]);

        $user = User::with('roles')->findOrFail($request->id);

        return response()->json([
            'user' => $user,
            'roles' => $user->getRoleNames()->toArray()
        ]);
    }

    public function registrar(Request $request)
    {
        $request->validate([
            'carnet' => 'required|string|max:20|unique:personas,carnet',
            'nombres' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|exists:roles,name',
            'estado' => 'required|in:Activo,Inactivo',
            'password' => 'required|string|min:8|confirmed',
            'fotografia' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        DB::beginTransaction();
        try {
            \Log::info('Iniciando registro de usuario', $request->all());

            // Verificar que el rol existe
            $role = Role::where('name', $request->role)->first();
            if (!$role) {
                return response()->json([
                    'success' => false,
                    'msg' => 'El rol seleccionado no existe'
                ], 422);
            }

            // Crear la persona
            $personaData = [
                'carnet' => $request->carnet,
                'nombres' => $request->nombres,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
            ];

            if ($request->hasFile('fotografia')) {
                $image = $request->file('fotografia');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                // Asegurar que el directorio existe
                $imagePath = 'uploads/personas';
                if (!file_exists(public_path($imagePath))) {
                    mkdir(public_path($imagePath), 0755, true);
                }
                $image->move(public_path($imagePath), $imageName);
                $personaData['fotografia'] = $imagePath . '/' . $imageName;
            }

            $persona = Persona::create($personaData);
            \Log::info('Persona creada:', $persona->toArray());

            // Crear el usuario
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'estado' => $request->estado,
                'persona_id' => $persona->id
            ];

            $user = User::create($userData);
            \Log::info('Usuario creado:', $user->toArray());

            // Asignar rol usando Spatie Permission
            $user->assignRole($request->role);
            \Log::info('Rol asignado:', ['role' => $request->role]);

            DB::commit();

            \Log::info('Usuario registrado exitosamente');

            return response()->json([
                'success' => true,
                'msg' => 'Usuario registrado correctamente.'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error al registrar usuario: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'msg' => 'Error al registrar el usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    public function eliminar(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id'
        ]);

        DB::beginTransaction();
        try {
            $user = User::findOrFail($request->id);

            // Eliminar usuario y persona asociada
            $personaId = $user->persona_id;
            $user->delete();

            // Eliminar persona si existe
            if ($personaId) {
                Persona::where('id', $personaId)->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'msg' => 'Usuario eliminado correctamente.'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'msg' => 'Error al eliminar el usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verificarCarnet(Request $request)
    {
        \Log::info('Verificar carnet recibido:', $request->all());

        try {
            $request->validate([
                'carnet' => 'required|string',
            ]);

            $carnet = $request->input('carnet');
            $exists = Persona::where('carnet', $carnet)->exists();

            \Log::info("Carnet {$carnet} existe: " . ($exists ? 'Sí' : 'No'));

            return response()->json(['exists' => $exists]);
        } catch (\Exception $e) {
            \Log::error('Error en verificarCarnet: ' . $e->getMessage());
            return response()->json(['exists' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
