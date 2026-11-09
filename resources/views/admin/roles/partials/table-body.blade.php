<tbody id="rolesTableBody">
    @forelse ($roles as $n => $role)
        <tr data-nombre="{{ strtolower($role->name) }}">
            <td class="text-center">{{ $roles->firstItem() + $n }}</td>
            <td>
                <span class="badge bg-primary">{{ $role->name }}</span>
            </td>
            <td class="text-center">
                <div class="btn-group" role="group">
                    <button type="button" title="Editar Rol" class="btn btn-warning btn-sm editBtn"
                        data-bs-obj='@json($role)' data-bs-toggle="modal"
                        data-bs-target="#modalModificar">
                        <i class="ri-edit-line"></i>
                    </button>
                    <button type="button" title="Eliminar Rol" class="btn btn-danger btn-sm deleteBtn"
                        data-bs-obj='@json($role)' data-bs-toggle="modal"
                        data-bs-target="#modalEliminar">
                        <i class="ri-delete-bin-line"></i>
                    </button>

                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="3" class="text-center py-4">
                <div class="text-muted">
                    <i class="ri-inbox-line display-4"></i>
                    <p class="mt-2">No se tienen registros de Roles</p>
                </div>
            </td>
        </tr>
    @endforelse
</tbody>
