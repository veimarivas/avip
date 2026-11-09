<tbody id="usersTableBody">
    @forelse ($users as $n => $user)
        <tr data-nombre="{{ strtolower($user->name) }}" data-estado="{{ strtolower($user->estado ?? '') }}">
            <td class="text-center">{{ $users->firstItem() + $n }}</td>
            <td class="text-center">
                @if ($user->persona && $user->persona->fotografia && file_exists(public_path($user->persona->fotografia)))
                    <img src="{{ asset($user->persona->fotografia) }}" alt="Foto de {{ $user->name }}"
                        class="rounded-circle user-photo">
                @else
                    @php
                        $nameParts = collect(explode(' ', trim(($user->persona->nombres ?? '') . ' ' . ($user->persona->apellido_paterno ?? ''))))
                            ->filter()
                            ->take(2)
                            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                            ->implode('');
                        $userInitials = $nameParts ?: mb_strtoupper(mb_substr($user->name, 0, 2));
                    @endphp
                    <span class="avip-avatar user-photo-fallback">{{ $userInitials }}</span>
                @endif
            </td>
            <td>
                @if ($user->persona)
                    <strong>{{ $user->persona->apellido_paterno }} {{ $user->persona->apellido_materno }}</strong><br>
                    <span class="text-muted">{{ $user->persona->nombres }}</span><br>
                    <small class="text-muted">
                        <i class="ri-id-card-line"></i> {{ $user->persona->carnet }}
                    </small>
                @else
                    <span class="text-muted fst-italic">Sin datos personales</span>
                @endif
            </td>
            <td>{{ $user->email }}</td>
            <td>
                @php
                    $roles = $user->getRoleNames()->toArray();
                @endphp

                @if (count($roles) > 0)
                    @foreach ($roles as $role)
                        <span class="badge badge-group">{{ ucfirst(str_replace('-', ' ', $role)) }}</span>
                    @endforeach
                @else
                    <span class="badge badge-secondary">Sin rol asignado</span>
                @endif
            </td>
            <td class="text-center">
                @if ($user->estado == 'Activo')
                    <span class="badge bg-success">Activo</span>
                @else
                    <span class="badge bg-danger">Inactivo</span>
                @endif
            </td>
            <td class="text-center">
                <div class="btn-group" role="group">

                    <button type="button" title="Editar Usuario" class="btn btn-warning btn-sm editBtn"
                        data-id="{{ $user->id }}" data-bs-toggle="modal" data-bs-target="#modalModificar">
                        <i class="ri-edit-line"></i>
                    </button>
                    <button type="button" title="Restablecer Contraseña" class="btn btn-info btn-sm resetBtn"
                        data-id="{{ $user->id }}" data-bs-toggle="modal" data-bs-target="#modalResetPassword">
                        <i class="ri-key-line"></i>
                    </button>
                    <button type="button" title="Eliminar Usuario" class="btn btn-danger btn-sm deleteBtn"
                        data-id="{{ $user->id }}" data-bs-toggle="modal" data-bs-target="#modalEliminar">
                        <i class="ri-delete-bin-line"></i>
                    </button>

                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="7" class="text-center py-4">
                <div class="text-muted">
                    <i class="ri-inbox-line display-4"></i>
                    <p class="mt-2">No se encontraron usuarios</p>
                </div>
            </td>
        </tr>
    @endforelse
</tbody>
