<tbody id="rolesTableBody">
    @forelse ($roles as $n => $role)
        <tr data-nombre="{{ strtolower($role->name) }}">
            <td class="text-center align-middle">{{ $roles->firstItem() + $n }}</td>
            <td class="align-middle">
                <span class="badge bg-primary fs-6">{{ $role->name }}</span>
            </td>
            <td>
                @if ($role->permissions->count() > 0)
                    <div class="permissions-container">
                        <div class="permissions-list">
                            @php
                                $visiblePermissions = $role->permissions->take(3);
                                $hiddenPermissions = $role->permissions->slice(3);
                            @endphp

                            @foreach ($visiblePermissions as $permission)
                                <span class="badge badge-permission me-1 mb-1">
                                    <i class="ri-shield-keyhole-line me-1"></i>
                                    {{ $permission->name }}
                                </span>
                            @endforeach

                            @if ($hiddenPermissions->count() > 0)
                                <div class="more-permissions d-none mt-2">
                                    @foreach ($hiddenPermissions as $permission)
                                        <span class="badge badge-permission me-1 mb-1">
                                            <i class="ri-shield-keyhole-line me-1"></i>
                                            {{ $permission->name }}
                                        </span>
                                    @endforeach
                                </div>

                                <button class="btn btn-sm btn-outline-primary btn-more show-more-permissions mt-1"
                                    data-role-id="{{ $role->id }}">
                                    Ver más <i class="ri-arrow-down-s-line"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                @else
                    <span class="text-muted fst-italic">
                        <i class="ri-information-line me-1"></i>
                        Sin permisos asignados
                    </span>
                @endif
            </td>

            <td class="text-center align-middle">
                <a href="{{ route('admin.role-permissions.show', $role->id) }}" class="btn btn-primary btn-sm"
                    title="Gestionar permisos" data-bs-toggle="tooltip">
                    <i class="ri-settings-4-line"></i>
                </a>
            </td>

        </tr>
    @empty
        <tr>
            <td colspan="4" class="text-center py-4">
                <div class="text-muted">
                    <i class="ri-inbox-line display-4"></i>
                    <p class="mt-2">No se tienen registros de Roles</p>
                </div>
            </td>
        </tr>
    @endforelse
</tbody>
