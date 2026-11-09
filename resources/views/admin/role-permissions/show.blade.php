@extends('admin.dashboard')
@section('admin')
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0">Gestión de Permisos</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.role-permissions.index') }}">Roles</a>
                                </li>
                                <li class="breadcrumb-item active">Permisos para {{ $role->name }}</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="page-title-right">
                        <span class="badge bg-primary fs-6">{{ $role->name }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-8">
                                <div class="search-box">
                                    <input type="text" id="permissionSearch" class="form-control"
                                        placeholder="Buscar permiso...">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="button" id="clearSearch" class="btn btn-outline-secondary w-100">
                                    <i class="ri-refresh-line me-1"></i> Limpiar búsqueda
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Permissions Management Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Gestión de permisos para el rol: <span
                                class="text-primary">{{ $role->name }}</span></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Assigned Permissions -->
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">
                                        <i class="ri-checkbox-circle-line text-success me-2"></i>
                                        Permisos Asignados
                                        <span class="badge bg-success ms-2"
                                            id="assignedCount">{{ $role->permissions->count() }}</span>
                                    </h5>
                                    <button type="button" id="collapseAllAssigned" class="btn btn-sm btn-outline-primary">
                                        <i class="ri-collapse-diagonal-line"></i> Colapsar todo
                                    </button>
                                </div>
                                <div id="assignedPermissionsContainer" class="permissions-container">
                                    @foreach ($permissionGroups as $group)
                                        @php
                                            $assignedPermissions = $role->permissions
                                                ->where('group_name', $group)
                                                ->sortBy('name');
                                        @endphp

                                        @if ($assignedPermissions->isNotEmpty())
                                            <div class="card permission-group-card mb-3">
                                                <div class="card-header bg-success bg-soft d-flex justify-content-between align-items-center cursor-pointer group-header"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#assignedGroup{{ $loop->index }}">
                                                    <div>
                                                        <i class="ri-folder-2-line me-2"></i>
                                                        <strong>{{ ucfirst($group) }}</strong>
                                                        <span
                                                            class="badge bg-success ms-2">{{ $assignedPermissions->count() }}</span>
                                                    </div>
                                                    <i class="ri-arrow-down-s-line group-arrow"></i>
                                                </div>
                                                <div class="collapse show" id="assignedGroup{{ $loop->index }}">
                                                    <div class="card-body p-2">
                                                        <div class="permission-list">
                                                            @foreach ($assignedPermissions as $permission)
                                                                <div class="permission-item d-flex justify-content-between align-items-center p-2 border-bottom"
                                                                    data-permission-id="{{ $permission->id }}"
                                                                    data-permission-name="{{ strtolower($permission->name) }}">
                                                                    <div class="permission-info">
                                                                        <i
                                                                            class="ri-shield-keyhole-line text-success me-2"></i>
                                                                        <span
                                                                            class="permission-text">{{ $permission->name }}</span>
                                                                    </div>
                                                                    <button class="btn btn-sm btn-danger revoke-btn"
                                                                        data-role-id="{{ $role->id }}"
                                                                        data-permission-id="{{ $permission->id }}"
                                                                        title="Revocar permiso">
                                                                        <i class="ri-close-line"></i>
                                                                    </button>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <!-- Available Permissions -->
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">
                                        <i class="ri-add-circle-line text-primary me-2"></i>
                                        Permisos Disponibles
                                        <span class="badge bg-primary ms-2" id="availableCount">
                                            @php
                                                $totalAvailable = 0;
                                                foreach ($availablePermissionsByGroup as $group => $permissions) {
                                                    $totalAvailable += $permissions->count();
                                                }
                                            @endphp
                                            {{ $totalAvailable }}
                                        </span>
                                    </h5>
                                    <button type="button" id="collapseAllAvailable" class="btn btn-sm btn-outline-primary">
                                        <i class="ri-collapse-diagonal-line"></i> Colapsar todo
                                    </button>
                                </div>
                                <div id="availablePermissionsContainer" class="permissions-container">
                                    @foreach ($permissionGroups as $group)
                                        @php
                                            $availablePermissions = $availablePermissionsByGroup[$group] ?? collect();
                                        @endphp

                                        @if ($availablePermissions->isNotEmpty())
                                            <div class="card permission-group-card mb-3">
                                                <div class="card-header bg-primary bg-soft d-flex justify-content-between align-items-center cursor-pointer group-header"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#availableGroup{{ $loop->index }}">
                                                    <div>
                                                        <i class="ri-folder-2-line me-2"></i>
                                                        <strong>{{ ucfirst($group) }}</strong>
                                                        <span
                                                            class="badge bg-primary ms-2">{{ $availablePermissions->count() }}</span>
                                                    </div>
                                                    <i class="ri-arrow-down-s-line group-arrow"></i>
                                                </div>
                                                <div class="collapse show" id="availableGroup{{ $loop->index }}">
                                                    <div class="card-body p-2">
                                                        <div class="permission-list">
                                                            @foreach ($availablePermissions as $permission)
                                                                <div class="permission-item d-flex justify-content-between align-items-center p-2 border-bottom"
                                                                    data-permission-id="{{ $permission->id }}"
                                                                    data-permission-name="{{ strtolower($permission->name) }}">
                                                                    <div class="permission-info">
                                                                        <i
                                                                            class="ri-shield-keyhole-line text-primary me-2"></i>
                                                                        <span
                                                                            class="permission-text">{{ $permission->name }}</span>
                                                                    </div>
                                                                    <button class="btn btn-sm btn-success assign-btn"
                                                                        data-role-id="{{ $role->id }}"
                                                                        data-permission-id="{{ $permission->id }}"
                                                                        title="Asignar permiso">
                                                                        <i class="ri-add-line"></i>
                                                                    </button>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Bulk Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">Acciones rápidas</h6>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <button type="button" id="selectAllAvailable"
                                                class="btn btn-outline-primary btn-sm">
                                                <i class="ri-check-double-line me-1"></i> Seleccionar todos disponibles
                                            </button>
                                            <button type="button" id="assignSelected" class="btn btn-success btn-sm"
                                                disabled>
                                                <i class="ri-add-line me-1"></i> Asignar seleccionados
                                            </button>
                                            <button type="button" id="revokeAllAssigned"
                                                class="btn btn-outline-danger btn-sm">
                                                <i class="ri-close-circle-line me-1"></i> Revocar todos los asignados
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .search-box {
            position: relative;
        }

        .search-box .search-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #74788d;
        }

        .permission-group-card .card-header {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .permission-group-card .card-header:hover {
            background-color: rgba(var(--bs-success-rgb), 0.2) !important;
        }

        .permission-group-card.bg-primary .card-header:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.2) !important;
        }

        .permission-item {
            transition: all 0.2s ease;
            border-radius: 4px;
        }

        .permission-item:hover {
            background-color: rgba(85, 110, 230, 0.05);
        }

        .permission-text {
            font-size: 0.875rem;
            font-family: 'Courier New', monospace;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .group-arrow {
            transition: transform 0.3s ease;
        }

        .collapsed .group-arrow {
            transform: rotate(-90deg);
        }

        .permission-checkbox {
            margin-right: 8px;
        }

        .bg-soft {
            background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
        }

        .bg-success.bg-soft {
            background-color: rgba(var(--bs-success-rgb), 0.1) !important;
        }

        .bulk-selected {
            background-color: rgba(85, 110, 230, 0.1) !important;
            border-left: 3px solid #14503c;
        }
    </style>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            let debounceTimer;
            let selectedPermissions = new Set();

            // Función para mostrar notificación
            function showNotification(type, message) {
                let toastContainer = $('#toast-container');
                if (toastContainer.length === 0) {
                    toastContainer = $(
                        '<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999"></div>'
                        );
                    $('body').append(toastContainer);
                }

                const toast = $(`
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="ri-${type === 'success' ? 'check-line' : 'error-warning-line'} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `);

                toastContainer.append(toast);
                const bsToast = new bootstrap.Toast(toast[0]);
                bsToast.show();

                toast.on('hidden.bs.toast', function() {
                    $(this).remove();
                });
            }

            // Función para actualizar contadores
            function updateCounters() {
                const assignedCount = $('#assignedPermissionsContainer .permission-item').length;
                const availableCount = $('#availablePermissionsContainer .permission-item').length;

                $('#assignedCount').text(assignedCount);
                $('#availableCount').text(availableCount);
            }

            // Búsqueda de permisos
            $('#permissionSearch').on('input', function() {
                const searchTerm = $(this).val().trim().toLowerCase();
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    filterPermissions(searchTerm);
                }, 200);
            });

            // Limpiar búsqueda
            $('#clearSearch').on('click', function() {
                $('#permissionSearch').val('');
                filterPermissions('');
            });

            // Filtrar permisos
            function filterPermissions(searchTerm) {
                $('.permission-item').each(function() {
                    const permissionText = $(this).find('.permission-text').text().toLowerCase();
                    const matches = permissionText.includes(searchTerm);
                    $(this).toggle(matches);
                });

                // Mostrar/ocultar grupos vacíos
                $('.permission-group-card').each(function() {
                    const visibleItems = $(this).find('.permission-item:visible').length;
                    $(this).toggle(visibleItems > 0);
                });
            }

            // Colapsar/expandir todos los grupos
            $('#collapseAllAssigned').on('click', function() {
                const isCollapsed = $('#assignedPermissionsContainer .collapse').first().hasClass('show');
                $('#assignedPermissionsContainer .collapse').collapse(isCollapsed ? 'hide' : 'show');
                $(this).html(isCollapsed ?
                    '<i class="ri-expand-diagonal-line"></i> Expandir todo' :
                    '<i class="ri-collapse-diagonal-line"></i> Colapsar todo'
                );
            });

            $('#collapseAllAvailable').on('click', function() {
                const isCollapsed = $('#availablePermissionsContainer .collapse').first().hasClass('show');
                $('#availablePermissionsContainer .collapse').collapse(isCollapsed ? 'hide' : 'show');
                $(this).html(isCollapsed ?
                    '<i class="ri-expand-diagonal-line"></i> Expandir todo' :
                    '<i class="ri-collapse-diagonal-line"></i> Colapsar todo'
                );
            });

            // Seleccionar todos los permisos disponibles
            $('#selectAllAvailable').on('click', function() {
                const $availableItems = $('#availablePermissionsContainer .permission-item:visible');
                const allSelected = $availableItems.length === selectedPermissions.size;

                if (allSelected) {
                    // Deseleccionar todos
                    selectedPermissions.clear();
                    $availableItems.removeClass('bulk-selected');
                } else {
                    // Seleccionar todos
                    $availableItems.each(function() {
                        const permissionId = $(this).data('permission-id');
                        selectedPermissions.add(permissionId);
                        $(this).addClass('bulk-selected');
                    });
                }

                updateBulkActions();
            });

            // Actualizar estado de acciones masivas
            function updateBulkActions() {
                const hasSelection = selectedPermissions.size > 0;
                $('#assignSelected').prop('disabled', !hasSelection);
                $('#selectAllAvailable').html(
                    selectedPermissions.size > 0 ?
                    '<i class="ri-close-line me-1"></i> Deseleccionar todos' :
                    '<i class="ri-check-double-line me-1"></i> Seleccionar todos disponibles'
                );
            }

            // Asignar permiso individual
            $(document).on('click', '.assign-btn', async function() {
                const $btn = $(this);
                const roleId = $btn.data('role-id');
                const permissionId = $btn.data('permission-id');

                await assignPermission(roleId, permissionId, $btn.closest('.permission-item'));
            });

            // Revocar permiso individual
            $(document).on('click', '.revoke-btn', async function() {
                const $btn = $(this);
                const roleId = $btn.data('role-id');
                const permissionId = $btn.data('permission-id');

                await revokePermission(roleId, permissionId, $btn.closest('.permission-item'));
            });

            // Asignar permisos seleccionados
            $('#assignSelected').on('click', async function() {
                const $btn = $(this);
                const roleId = {{ $role->id }};

                if (selectedPermissions.size === 0) {
                    showNotification('warning', 'No hay permisos seleccionados');
                    return;
                }

                $btn.prop('disabled', true).html(
                    '<i class="ri-loader-4-line spin me-1"></i> Asignando...');

                try {
                    // Crear array de promesas para todas las asignaciones
                    const promises = Array.from(selectedPermissions).map(permissionId => {
                        return assignPermission(roleId, permissionId);
                    });

                    await Promise.all(promises);
                    showNotification('success',
                        `${selectedPermissions.size} permisos asignados correctamente`);
                    selectedPermissions.clear();
                    updateBulkActions();
                } catch (error) {
                    showNotification('error', 'Error al asignar algunos permisos');
                }

                $btn.prop('disabled', true).html(
                    '<i class="ri-add-line me-1"></i> Asignar seleccionados');
            });

            // Revocar todos los permisos asignados
            $('#revokeAllAssigned').on('click', async function() {
                const $btn = $(this);
                const roleId = {{ $role->id }};
                const $assignedItems = $('#assignedPermissionsContainer .permission-item');

                if ($assignedItems.length === 0) {
                    showNotification('info', 'No hay permisos asignados para revocar');
                    return;
                }

                if (!confirm(
                        `¿Está seguro de que desea revocar todos los ${$assignedItems.length} permisos asignados?`
                        )) {
                    return;
                }

                $btn.prop('disabled', true).html(
                    '<i class="ri-loader-4-line spin me-1"></i> Revocando...');

                try {
                    // Crear array de promesas para todas las revocaciones
                    const promises = $assignedItems.map(function() {
                        const permissionId = $(this).data('permission-id');
                        return revokePermission(roleId, permissionId, $(this));
                    }).get();

                    await Promise.all(promises);
                    showNotification('success', 'Todos los permisos han sido revocados correctamente');
                } catch (error) {
                    showNotification('error', 'Error al revocar algunos permisos');
                }

                $btn.prop('disabled', false).html(
                    '<i class="ri-close-circle-line me-1"></i> Revocar todos los asignados');
            });

            // Función para asignar permiso
            async function assignPermission(roleId, permissionId, $element = null) {
                try {
                    const response = await $.ajax({
                        url: "{{ route('admin.role-permissions.assign') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            role_id: roleId,
                            permission_id: permissionId
                        }
                    });

                    if (response.success) {
                        if ($element) {
                            movePermissionToAssigned($element, response.permission);
                        } else {
                            // Si no hay elemento, buscar y mover el permiso
                            const $permissionElement = $(
                                `.permission-item[data-permission-id="${permissionId}"]`);
                            if ($permissionElement.length) {
                                movePermissionToAssigned($permissionElement, response.permission);
                            }
                        }
                        selectedPermissions.delete(permissionId);
                        updateCounters();
                        updateBulkActions();
                        return true;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showNotification('error', 'Error al asignar el permiso');
                    throw error;
                }
            }

            // Función para revocar permiso
            async function revokePermission(roleId, permissionId, $element = null) {
                try {
                    const response = await $.ajax({
                        url: "{{ route('admin.role-permissions.revoke') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            role_id: roleId,
                            permission_id: permissionId
                        }
                    });

                    if (response.success) {
                        if ($element) {
                            movePermissionToAvailable($element);
                        } else {
                            // Si no hay elemento, buscar y mover el permiso
                            const $permissionElement = $(
                                `.permission-item[data-permission-id="${permissionId}"]`);
                            if ($permissionElement.length) {
                                movePermissionToAvailable($permissionElement);
                            }
                        }
                        updateCounters();
                        return true;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showNotification('error', 'Error al revocar el permiso');
                    throw error;
                }
            }

            // Mover permiso a asignados
            function movePermissionToAssigned($element, permission) {
                const groupName = permission.group_name;
                const $newElement = $element.clone();

                // Cambiar botón y estilos
                $newElement.find('.assign-btn')
                    .removeClass('btn-success assign-btn')
                    .addClass('btn-danger revoke-btn')
                    .html('<i class="ri-close-line"></i>')
                    .attr('title', 'Revocar permiso')
                    .attr('data-role-id', '{{ $role->id }}')
                    .attr('data-permission-id', permission.id);

                $newElement.find('.permission-info i')
                    .removeClass('text-primary')
                    .addClass('text-success');

                $newElement.removeClass('bulk-selected');

                // Encontrar o crear grupo en asignados
                let $targetGroup = $(`#assignedPermissionsContainer .card-header:contains("${groupName}")`).closest(
                    '.permission-group-card');

                if ($targetGroup.length === 0) {
                    // Crear nuevo grupo
                    const groupId = 'assignedGroup' + Date.now();
                    $targetGroup = $(`
                <div class="card permission-group-card mb-3">
                    <div class="card-header bg-success bg-soft d-flex justify-content-between align-items-center cursor-pointer group-header"
                         data-bs-toggle="collapse" data-bs-target="#${groupId}">
                        <div>
                            <i class="ri-folder-2-line me-2"></i>
                            <strong>${groupName}</strong>
                            <span class="badge bg-success ms-2">0</span>
                        </div>
                        <i class="ri-arrow-down-s-line group-arrow"></i>
                    </div>
                    <div class="collapse show" id="${groupId}">
                        <div class="card-body p-2">
                            <div class="permission-list"></div>
                        </div>
                    </div>
                </div>
            `);
                    $('#assignedPermissionsContainer').append($targetGroup);
                }

                // Agregar elemento al grupo
                $targetGroup.find('.permission-list').append($newElement);

                // Actualizar contador del grupo
                const count = $targetGroup.find('.permission-item').length;
                $targetGroup.find('.badge').text(count);

                // Remover elemento original
                $element.remove();

                // Actualizar contador del grupo de origen si existe
                const $sourceGroup = $element.closest('.permission-group-card');
                if ($sourceGroup.length > 0) {
                    const sourceCount = $sourceGroup.find('.permission-item').length;
                    if (sourceCount === 0) {
                        $sourceGroup.remove();
                    } else {
                        $sourceGroup.find('.badge').text(sourceCount);
                    }
                }

                // Re-inicializar tooltips
                initializeTooltips();
            }

            // Mover permiso a disponibles
            function movePermissionToAvailable($element) {
                const permissionName = $element.find('.permission-text').text();
                const groupName = $element.closest('.permission-group-card').find('.card-header strong').text()
                    .trim();
                const $newElement = $element.clone();

                // Cambiar botón y estilos
                $newElement.find('.revoke-btn')
                    .removeClass('btn-danger revoke-btn')
                    .addClass('btn-success assign-btn')
                    .html('<i class="ri-add-line"></i>')
                    .attr('title', 'Asignar permiso')
                    .attr('data-role-id', '{{ $role->id }}')
                    .attr('data-permission-id', $element.data('permission-id'));

                $newElement.find('.permission-info i')
                    .removeClass('text-success')
                    .addClass('text-primary');

                // Encontrar o crear grupo en disponibles
                let $targetGroup = $(`#availablePermissionsContainer .card-header:contains("${groupName}")`)
                    .closest('.permission-group-card');

                if ($targetGroup.length === 0) {
                    // Crear nuevo grupo
                    const groupId = 'availableGroup' + Date.now();
                    $targetGroup = $(`
                <div class="card permission-group-card mb-3">
                    <div class="card-header bg-primary bg-soft d-flex justify-content-between align-items-center cursor-pointer group-header"
                         data-bs-toggle="collapse" data-bs-target="#${groupId}">
                        <div>
                            <i class="ri-folder-2-line me-2"></i>
                            <strong>${groupName}</strong>
                            <span class="badge bg-primary ms-2">0</span>
                        </div>
                        <i class="ri-arrow-down-s-line group-arrow"></i>
                    </div>
                    <div class="collapse show" id="${groupId}">
                        <div class="card-body p-2">
                            <div class="permission-list"></div>
                        </div>
                    </div>
                </div>
            `);
                    $('#availablePermissionsContainer').append($targetGroup);
                }

                // Agregar elemento al grupo
                $targetGroup.find('.permission-list').append($newElement);

                // Actualizar contador del grupo
                const count = $targetGroup.find('.permission-item').length;
                $targetGroup.find('.badge').text(count);

                // Remover elemento original
                $element.remove();

                // Actualizar contador del grupo de origen si existe
                const $sourceGroup = $element.closest('.permission-group-card');
                if ($sourceGroup.length > 0) {
                    const sourceCount = $sourceGroup.find('.permission-item').length;
                    if (sourceCount === 0) {
                        $sourceGroup.remove();
                    } else {
                        $sourceGroup.find('.badge').text(sourceCount);
                    }
                }

                // Re-inicializar tooltips
                initializeTooltips();
            }

            // Función para inicializar tooltips
            function initializeTooltips() {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
                const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }

            // Inicializar tooltips al cargar
            initializeTooltips();
        });
    </script>

    <style>
        .spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .toast {
            min-width: 300px;
        }
    </style>

    <!-- Container para notificaciones toast -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999"></div>
@endpush
