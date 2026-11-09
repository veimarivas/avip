<tbody id="permissionsTableBody">
    @forelse ($permissions as $n => $permission)
        <tr data-nombre="{{ strtolower($permission->name) }}"
            data-grupo="{{ strtolower($permission->group_name ?? '') }}">
            <td class="text-center">{{ $permissions->firstItem() + $n }}</td>
            <td>{{ $permission->name }}</td>

            <td>
                @if ($permission->group_name)
                    <span class="badge badge-group">{{ $permission->group_name }}</span>
                @else
                    <span class="badge badge-secondary">Sin grupo</span>
                @endif
            </td>
            <td class="text-center">
                <div class="btn-group" role="group">
                    <button type="button" title="Editar Permiso" class="btn btn-warning btn-sm editBtn"
                        data-bs-obj='@json($permission)' data-bs-toggle="modal"
                        data-bs-target="#modalModificar">
                        <i class="ri-edit-line"></i>
                    </button>
                    <button type="button" title="Eliminar Permiso" class="btn btn-danger btn-sm deleteBtn"
                        data-bs-obj='@json($permission)' data-bs-toggle="modal"
                        data-bs-target="#modalEliminar">
                        <i class="ri-delete-bin-line"></i>
                    </button>

                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="text-center py-4">
                <div class="text-muted">
                    <i class="ri-inbox-line display-4"></i>
                    <p class="mt-2">No se tienen registros de Permisos</p>
                </div>
            </td>
        </tr>
    @endforelse
</tbody>
