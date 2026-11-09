@extends('admin.dashboard')
@section('admin')
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Gestión de Permisos</h4>
                    <div class="page-title-right">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registrar">
                            <i class="ri-add-line align-middle me-1"></i> Registrar Permiso
                        </button>
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
                            <div class="col-md-6">
                                <div class="search-box">
                                    <input type="text" id="searchInput" class="form-control"
                                        placeholder="Buscar permiso...">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select id="groupFilter" class="form-select">
                                    <option value="">Todos los grupos</option>
                                    @foreach ($permissionGroups as $group)
                                        <option value="{{ $group }}">{{ $group }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="clearFilters" class="btn btn-outline-secondary w-100">
                                    <i class="ri-refresh-line me-1"></i> Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Lista de Permisos Registrados</h5>
                    </div>
                    <div class="card-body">
                        <div id="results-container">
                            <div class="table-responsive">
                                <table class="table table-hover table-centered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">ID</th>
                                            <th>Nombre del Permiso</th>
                                            <th>Grupo</th>
                                            <th width="15%" class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    @include('admin.permissions.partials.table-body')
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted">
                                    Mostrando <span id="showing-count">{{ $permissions->firstItem() ?? 0 }}</span>
                                    a <span id="to-count">{{ $permissions->lastItem() ?? 0 }}</span>
                                    de <span id="total-count">{{ $permissions->total() ?? 0 }}</span> registros
                                </div>
                                <div id="pagination-container">
                                    {{ $permissions->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Registrar -->
    <div class="modal fade" id="registrar" tabindex="-1" aria-labelledby="registrarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary bg-soft">
                    <h5 class="modal-title text-white" id="registrarLabel">
                        <i class="ri-add-line me-2"></i>Registrar Nuevo Permiso
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addForm" class="forms-sample">
                        @csrf
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Nombre del Permiso <span class="text-danger">*</span></label>
                                    <input type="text" id="name_registro" name="name" class="form-control"
                                        placeholder="Ej: areas.listar" required>
                                    <div id="feedback_registro" class="form-text"></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Grupo del Permiso</label>
                                    <input type="text" id="group_name_registro" name="group_name" class="form-control"
                                        placeholder="Ej: areas">
                                    <div class="form-text">Organiza los permisos por grupos para mejor gestión</div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="addForm" class="btn btn-primary addBtn" disabled>
                        <i class="ri-save-line me-1"></i> Registrar Permiso
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Modificar -->
    <div class="modal fade" id="modalModificar" tabindex="-1" aria-labelledby="modalModificarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning bg-soft">
                    <h5 class="modal-title" id="modalModificarLabel">
                        <i class="ri-edit-line me-2"></i>Modificar Permiso
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateForm" class="forms-sample">
                        @csrf
                        <input type="hidden" name="id" id="permissionId">
                        <div class="mb-3">
                            <label for="name_edicion" class="form-label">Nombre del Permiso <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name_edicion" name="name" required>
                            <div id="feedback_edicion" class="form-text"></div>
                        </div>
                        <div class="mb-3">
                            <label for="group_name_edicion" class="form-label">Grupo del Permiso</label>
                            <input type="text" class="form-control" id="group_name_edicion" name="group_name">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="updateForm" class="btn btn-warning updateBtn" disabled>
                        <i class="ri-save-line me-1"></i> Actualizar Permiso
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar -->
    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger bg-soft">
                    <h5 class="modal-title text-white" id="modalEliminarLabel">
                        <i class="ri-delete-bin-line me-2"></i>Eliminar Permiso
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="deleteForm" class="forms-sample">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="id" id="eliminarId">
                        <div class="text-center mb-4">
                            <div class="avatar-lg mx-auto mb-4">
                                <div class="avatar-title bg-danger bg-soft text-danger rounded-circle">
                                    <i class="ri-error-warning-line fs-2"></i>
                                </div>
                            </div>
                            <h5>¿Está seguro de eliminar este permiso?</h5>
                            <p class="text-muted">Esta acción no se puede deshacer y puede afectar a los roles que tengan
                                este permiso asignado.</p>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="deleteForm" class="btn btn-danger btnDelete">
                        <i class="ri-delete-bin-line me-1"></i> Sí, Eliminar
                    </button>
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

        .table-hover tbody tr:hover {
            background-color: rgba(85, 110, 230, 0.04);
        }

        .bg-soft {
            background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
        }

        .bg-warning.bg-soft {
            background-color: rgba(var(--bs-warning-rgb), 0.1) !important;
        }

        .bg-danger.bg-soft {
            background-color: rgba(var(--bs-danger-rgb), 0.1) !important;
        }

        .avatar-lg {
            height: 5rem;
            width: 5rem;
        }

        .avatar-title {
            align-items: center;
            display: flex;
            height: 100%;
            justify-content: center;
            width: 100%;
        }

        .badge-group {
            background-color: #e7f1ff;
            color: #14503c;
            font-size: 0.75rem;
        }

        .badge-guard {
            background-color: #f0f5ff;
            color: #495057;
            font-size: 0.75rem;
        }

        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    </style>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            let debounceTimer;
            let currentGroups = {!! json_encode($permissionGroups) !!};

            // Función para resetear el formulario de registro
            function resetAddForm() {
                $('#addForm')[0].reset();
                $('#feedback_registro').removeClass('text-success text-danger').text('');
                $('.addBtn').prop('disabled', true).html('<i class="ri-save-line me-1"></i> Registrar Permiso');
            }

            // Función para actualizar la lista de grupos en el filtro
            function updateGroupFilter() {
                const groupFilter = $('#groupFilter');
                const currentValue = groupFilter.val();

                // Limpiar opciones excepto la primera
                groupFilter.find('option:not(:first)').remove();

                // Ordenar grupos alfabéticamente
                currentGroups.sort();

                // Agregar grupos actualizados
                currentGroups.forEach(function(group) {
                    groupFilter.append($('<option>', {
                        value: group,
                        text: group
                    }));
                });

                // Restaurar el valor seleccionado si existe
                if (currentValue && currentGroups.includes(currentValue)) {
                    groupFilter.val(currentValue);
                }
            }

            // Función para agregar un nuevo grupo si no existe
            function addNewGroupIfNotExists(newGroup) {
                if (newGroup && newGroup.trim() !== '' && !currentGroups.includes(newGroup)) {
                    currentGroups.push(newGroup);
                    updateGroupFilter();
                }
            }

            // Evento cuando se cierra el modal de registro
            $('#registrar').on('hidden.bs.modal', function() {
                resetAddForm();
            });

            // Verifica si el nombre del permiso está en la BD (Registro)
            $('#name_registro').on('input', function() {
                const name = $(this).val().trim();
                const feedback = $('#feedback_registro');
                const submitBtn = $('.addBtn');

                // Limpiar el mensaje previo
                feedback.removeClass('text-success text-danger').text('');
                if (name.length === 0) {
                    submitBtn.prop('disabled', true);
                    return;
                }

                // Cancelar la petición anterior si el usuario sigue escribiendo
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    $.ajax({
                        url: "{{ route('admin.permissions.verificar') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            name: name
                        },
                        success: function(res) {
                            if (res.exists) {
                                feedback.addClass('text-danger').text(
                                    '⚠️ El permiso ya está registrado.');
                                submitBtn.prop('disabled', true);
                            } else {
                                feedback.addClass('text-success').text(
                                    '✅ Nombre disponible.');
                                submitBtn.prop('disabled', false);
                            }
                        },
                        error: function() {
                            feedback.addClass('text-danger').text(
                                '❌ Error al verificar el nombre.');
                            submitBtn.prop('disabled', true);
                        }
                    });
                }, 300);
            });

            // Registro de permiso
            $('#addForm').submit(function(e) {
                e.preventDefault();
                const submitBtn = $('.addBtn');
                submitBtn.prop('disabled', true).html(
                    '<i class="ri-loader-4-line spin me-1"></i> Registrando...');

                var formData = $(this).serialize();
                const newGroup = $('#group_name_registro').val().trim();

                $.ajax({
                    url: "{{ route('admin.permissions.registrar') }}",
                    type: "POST",
                    data: formData,
                    success: function(res) {
                        if (res.success) {
                            showNotification('success', res.msg);
                            $('#registrar').modal('hide');

                            // Agregar el nuevo grupo si es necesario
                            addNewGroupIfNotExists(newGroup);

                            // Recargar los resultados sin recargar la página
                            loadResults($('#searchInput').val().trim(), $('#groupFilter')
                                .val());

                            // Resetear el formulario
                            resetAddForm();
                        } else {
                            showNotification('error', res.msg);
                            submitBtn.prop('disabled', false).html(
                                '<i class="ri-save-line me-1"></i> Registrar Permiso');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Error al registrar el permiso.';
                        if (xhr.responseJSON?.errors?.name) {
                            errorMsg = xhr.responseJSON.errors.name[0];
                        } else if (xhr.responseJSON?.msg) {
                            errorMsg = xhr.responseJSON.msg;
                        }
                        showNotification('error', errorMsg);
                        submitBtn.prop('disabled', false).html(
                            '<i class="ri-save-line me-1"></i> Registrar Permiso');
                    }
                });
            });

            // Editar permiso - delegamos el evento ya que los botones se cargan dinámicamente
            $(document).on('click', '.editBtn', function() {
                var data = $(this).data('bs-obj');
                $('#permissionId').val(data.id);
                $('#name_edicion').val(data.name);
                $('#group_name_edicion').val(data.group_name);

                // Habilitar el botón inicialmente si hay datos válidos
                if (data.name && data.name.trim().length > 0) {
                    $('.updateBtn').prop('disabled', false);
                }

                // Verificar disponibilidad del nombre
                verificarDisponibilidadNombre(
                    data.name,
                    data.id,
                    $('#feedback_edicion'),
                    $('.updateBtn')
                );
            });

            // Validación en tiempo real para el campo de EDICIÓN
            $('#name_edicion').on('input', function() {
                const name = $(this).val().trim();
                const feedback = $('#feedback_edicion');
                const submitBtn = $('.updateBtn');
                const id = $('#permissionId').val();

                // Limpiar el mensaje previo
                feedback.removeClass('text-success text-danger').text('');
                if (name.length === 0) {
                    submitBtn.prop('disabled', true);
                    return;
                }

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    $.ajax({
                        url: "{{ route('admin.permissions.verificaredicion') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            name: name,
                            id: id
                        },
                        success: function(res) {
                            if (res.exists) {
                                feedback.addClass('text-danger').text(
                                    '⚠️ Este permiso ya está registrado.');
                                submitBtn.prop('disabled', true);
                            } else {
                                feedback.addClass('text-success').text(
                                    '✅ Nombre disponible.');
                                submitBtn.prop('disabled', false);
                            }
                        },
                        error: function() {
                            feedback.addClass('text-danger').text(
                                '❌ Error al verificar el nombre.');
                            submitBtn.prop('disabled', true);
                        }
                    });
                }, 300);
            });

            // Actualizar permiso
            $('#updateForm').submit(function(e) {
                e.preventDefault();
                const submitBtn = $('.updateBtn');
                submitBtn.prop('disabled', true).html(
                    '<i class="ri-loader-4-line spin me-1"></i> Actualizando...');
                var formData = $(this).serialize();
                const newGroup = $('#group_name_edicion').val().trim();

                $.ajax({
                    url: "{{ route('admin.permissions.modificar') }}",
                    type: "POST",
                    data: formData,
                    success: function(res) {
                        if (res.success) {
                            showNotification('success', res.msg);
                            $('#modalModificar').modal('hide');

                            // Agregar el nuevo grupo si es necesario
                            addNewGroupIfNotExists(newGroup);

                            loadResults($('#searchInput').val().trim(), $('#groupFilter')
                                .val());
                        } else {
                            showNotification('error', res.msg);
                            submitBtn.prop('disabled', false).html(
                                '<i class="ri-save-line me-1"></i> Actualizar Permiso');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Error al actualizar el permiso.';
                        if (xhr.responseJSON?.errors?.name) {
                            errorMsg = xhr.responseJSON.errors.name[0];
                        } else if (xhr.responseJSON?.msg) {
                            errorMsg = xhr.responseJSON.msg;
                        }
                        showNotification('error', errorMsg);
                        submitBtn.prop('disabled', false).html(
                            '<i class="ri-save-line me-1"></i> Actualizar Permiso');
                    }
                });
            });

            // Eliminar permiso - delegamos el evento
            $(document).on('click', '.deleteBtn', function() {
                var data = $(this).data('bs-obj');
                $('#eliminarId').val(data.id);
            });

            // Confirmar eliminación
            $('#deleteForm').submit(function(e) {
                e.preventDefault();
                const submitBtn = $('.btnDelete');
                submitBtn.prop('disabled', true).html(
                    '<i class="ri-loader-4-line spin me-1"></i> Eliminando...');
                var formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('admin.permissions.eliminar') }}",
                    type: "POST",
                    data: formData,
                    success: function(res) {
                        if (res.success) {
                            showNotification('success', res.msg);
                            $('#modalEliminar').modal('hide');

                            // Recargar grupos después de eliminar (por si se eliminó el último permiso de un grupo)
                            loadGroups();

                            loadResults($('#searchInput').val().trim(), $('#groupFilter')
                                .val());
                        } else {
                            showNotification('error', res.msg);
                            submitBtn.prop('disabled', false).html(
                                '<i class="ri-delete-bin-line me-1"></i> Sí, Eliminar');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Error al eliminar el permiso.';
                        if (xhr.responseJSON?.msg) {
                            errorMsg = xhr.responseJSON.msg;
                        }
                        showNotification('error', errorMsg);
                        submitBtn.prop('disabled', false).html(
                            '<i class="ri-delete-bin-line me-1"></i> Sí, Eliminar');
                    }
                });
            });

            // Función para cargar grupos desde el servidor
            function loadGroups() {
                $.ajax({
                    url: "{{ route('admin.permissions.groups') }}", // Necesitarás crear esta ruta
                    type: "GET",
                    success: function(res) {
                        if (res.success && res.groups) {
                            currentGroups = res.groups;
                            updateGroupFilter();
                        }
                    },
                    error: function() {
                        console.error('Error al cargar grupos desde el servidor');
                    }
                });
            }

            // Función para verificar disponibilidad del nombre
            function verificarDisponibilidadNombre(name, id, feedbackElement, buttonElement) {
                if (name.trim() === '') {
                    buttonElement.prop('disabled', true);
                    feedbackElement.text('').removeClass('text-success text-danger');
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.permissions.verificaredicion') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        name: name,
                        id: id
                    },
                    success: function(res) {
                        if (res.exists) {
                            feedbackElement.addClass('text-danger').text(
                                '⚠️ Este permiso ya está registrado.');
                            buttonElement.prop('disabled', true);
                        } else {
                            feedbackElement.addClass('text-success').text('✅ Nombre disponible.');
                            buttonElement.prop('disabled', false);
                        }
                    },
                    error: function() {
                        feedbackElement.addClass('text-danger').text('❌ Error al verificar.');
                        buttonElement.prop('disabled', true);
                    }
                });
            }

            // Función para cargar resultados con filtros
            function loadResults(search = '', group = '') {
                $.ajax({
                    url: '{{ route('admin.permissions.listar') }}',
                    method: 'GET',
                    data: {
                        search: search,
                        group: group
                    },
                    dataType: 'json',
                    success: function(response) {
                        // Actualizar la tabla y paginación
                        $('#results-container .table-responsive table').find('tbody').replaceWith(
                            response.html);
                        $('#pagination-container').html(response.pagination);

                        // Actualizar contadores si están disponibles en la respuesta
                        if (response.total !== undefined) {
                            updateCounters(response);
                        }

                        // Actualizar los parámetros de los enlaces de paginación
                        $('#pagination-container .pagination a').each(function() {
                            const href = $(this).attr('href');
                            if (href) {
                                const separator = href.includes('?') ? '&' : '?';
                                const newHref = href + separator +
                                    'search=' + encodeURIComponent(search) +
                                    '&group=' + encodeURIComponent(group);
                                $(this).attr('href', newHref);
                            }
                        });

                        if (typeof feather !== 'undefined') {
                            feather.replace();
                        }
                    },
                    error: function() {
                        showNotification('error', 'Error al cargar los resultados');
                    }
                });
            }

            // Actualizar contadores de registros
            function updateCounters(response) {
                const total = response.total || 0;
                const from = response.from || 0;
                const to = response.to || 0;

                $('#showing-count').text(from);
                $('#to-count').text(to);
                $('#total-count').text(total);
            }

            // Búsqueda por texto
            $('#searchInput').on('input', function() {
                const searchTerm = $(this).val().trim();
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    loadResults(searchTerm, $('#groupFilter').val());
                }, 300);
            });

            // Filtro por grupo
            $('#groupFilter').on('change', function() {
                const group = $(this).val();
                loadResults($('#searchInput').val().trim(), group);
            });

            // Limpiar filtros
            $('#clearFilters').on('click', function() {
                $('#searchInput').val('');
                $('#groupFilter').val('');
                loadResults();
            });

            // Manejar clics en la paginación
            $(document).on('click', '#pagination-container .pagination a', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                const search = $('#searchInput').val().trim();
                const group = $('#groupFilter').val();

                if (!url) return;

                $.ajax({
                    url: url,
                    method: 'GET',
                    data: {
                        search: search,
                        group: group
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#results-container .table-responsive table').find('tbody')
                            .replaceWith(response.html);
                        $('#pagination-container').html(response.pagination);

                        // Actualizar contadores si están disponibles
                        if (response.total !== undefined) {
                            updateCounters(response);
                        }

                        // Actualizar los parámetros de los enlaces de paginación
                        $('#pagination-container .pagination a').each(function() {
                            const href = $(this).attr('href');
                            if (href) {
                                const separator = href.includes('?') ? '&' : '?';
                                const newHref = href + separator +
                                    'search=' + encodeURIComponent(search) +
                                    '&group=' + encodeURIComponent(group);
                                $(this).attr('href', newHref);
                            }
                        });

                        if (typeof feather !== 'undefined') {
                            feather.replace();
                        }
                    },
                    error: function() {
                        showNotification('error', 'Error al cargar la página');
                    }
                });
            });

            // Función para mostrar notificaciones
            function showNotification(type, message) {
                // Si ya existe un toast container, usarlo, si no crearlo
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
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `);

                toastContainer.append(toast);
                const bsToast = new bootstrap.Toast(toast[0]);
                bsToast.show();

                // Remover el toast después de que se oculte
                toast.on('hidden.bs.toast', function() {
                    $(this).remove();
                });
            }

            // Inicializar tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
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
            min-width: 250px;
        }
    </style>
@endpush
