@extends('admin.dashboard')
@section('admin')
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Gestión de Clientes</h4>
                    @if (Auth::guard('web')->user()->can('clientes.registrar'))
                        <div class="page-title-right">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registrar">
                                <i class="ri-add-line align-middle me-1"></i> Registrar Cliente
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tabs Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#todos" role="tab"
                                    aria-selected="true">
                                    <span class="d-block d-sm-none"><i class="ri-list-check"></i></span>
                                    <span class="d-none d-sm-block">Todos los Clientes</span>
                                </a>
                            </li>
                            @foreach ($localidades as $localidad)
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#localidad{{ $localidad->id }}"
                                        role="tab" aria-selected="false">
                                        <span class="d-block d-sm-none"><i class="ri-map-pin-line"></i></span>
                                        <span class="d-none d-sm-block">
                                            <i class="ri-map-pin-fill me-1" style="color: {{ $localidad->color }}"></i>
                                            {{ $localidad->nombre }}
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
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
                            <div class="col-md-10">
                                <div class="search-box">
                                    <input type="text" id="searchInput" class="form-control"
                                        placeholder="Buscar cliente por CI, nombres o apellidos...">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
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
                        <h5 class="card-title mb-0">Lista de Clientes Registrados</h5>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="todos" role="tabpanel">
                                <div id="results-container">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-centered mb-0">
                                            <thead class="table-light">
                                                <tr class="text-center">
                                                    <th width="5%">#</th>
                                                    <th>Apellidos</th>
                                                    <th>Nombres</th>
                                                    <th>Deuda</th>
                                                    <th>Localidad</th>
                                                    <th>Estado</th>
                                                    <th width="15%" class="text-center">Acciones</th>
                                                </tr>
                                            </thead>
                                            @include('admin.clientes.partials.table-body')
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div class="text-muted">
                                            Mostrando <span id="showing-count">{{ $clientes->firstItem() ?? 0 }}</span>
                                            a <span id="to-count">{{ $clientes->lastItem() ?? 0 }}</span>
                                            de <span id="total-count">{{ $clientes->total() ?? 0 }}</span> registros
                                        </div>
                                        <div id="pagination-container">
                                            {{ $clientes->links('pagination::bootstrap-5') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @foreach ($localidades as $localidad)
                                <div class="tab-pane" id="localidad{{ $localidad->id }}" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="card-title mb-0">Clientes de {{ $localidad->nombre }}</h5>
                                        @if (Auth::guard('web')->user()->can('clientes.cambiar.orden'))
                                            <button type="button" class="btn btn-info btn-sm btn-reordenar"
                                                data-localidad-id="{{ $localidad->id }}"
                                                data-localidad-nombre="{{ $localidad->nombre }}">
                                                <i class="ri-sort-asc me-1"></i> Cambiar Orden
                                            </button>
                                        @endif
                                    </div>
                                    <div id="results-container-localidad-{{ $localidad->id }}">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-centered mb-0">
                                                <thead class="table-light">
                                                    <tr class="text-center">
                                                        <th width="5%">#</th>
                                                        <th>Apellidos</th>
                                                        <th>Nombres</th>
                                                        <th>Deuda</th>
                                                        <th>Localidad</th>
                                                        <th>Estado</th>
                                                        <th width="15%" class="text-center">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="7" class="text-center py-4">
                                                            <div class="spinner-border text-primary" role="status">
                                                                <span class="visually-hidden">Cargando...</span>
                                                            </div>
                                                            <p class="mt-2">Cargando clientes de {{ $localidad->nombre }}...</p>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="text-muted mt-2">
                                            Total: <span class="total-count">0</span> clientes
                                        </div>
                                    </div>
                                </div>
                            @endforeach
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
                        <i class="ri-add-line me-2"></i>Registrar Nuevo Cliente
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
                                    <label class="form-label">CI <span class="text-danger">*</span></label>
                                    <input type="text" id="ci_registro" name="ci" class="form-control"
                                        placeholder="Ej: 1234567" required>
                                    <div id="feedback_ci_registro" class="form-text"></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Nombres <span class="text-danger">*</span></label>
                                    <input type="text" id="nombres_registro" name="nombres" class="form-control"
                                        placeholder="Ej: Juan" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                                    <input type="text" id="apellidos_registro" name="apellidos" class="form-control"
                                        placeholder="Ej: Pérez García" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Deuda</label>
                                    <input type="number" step="0.01" id="deuda_registro" name="deuda"
                                        class="form-control" placeholder="Ej: 100.00" value="0">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Orden</label>
                                    <input type="number" id="orden_registro" name="orden" class="form-control"
                                        placeholder="Ej: 1" value="0">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Localidad <span class="text-danger">*</span></label>
                                    <select id="localidade_id_registro" name="localidade_id" class="form-select"
                                        required>
                                        <option value="">Seleccione una localidad</option>
                                        @foreach ($localidades as $localidad)
                                            <option value="{{ $localidad->id }}">{{ $localidad->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="addForm" class="btn btn-primary addBtn" disabled>
                        <i class="ri-save-line me-1"></i> Registrar Cliente
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Modificar -->
    <div class="modal fade" id="modalModificar" tabindex="-1" aria-labelledby="modalModificarLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning bg-soft">
                    <h5 class="modal-title" id="modalModificarLabel">
                        <i class="ri-edit-line me-2"></i>Modificar Cliente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateForm" class="forms-sample">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" id="clienteId">
                        <div class="mb-3">
                            <label for="ci_edicion" class="form-label">CI <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ci_edicion" name="ci" required>
                            <div id="feedback_ci_edicion" class="form-text"></div>
                        </div>
                        <div class="mb-3">
                            <label for="nombres_edicion" class="form-label">Nombres <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombres_edicion" name="nombres" required>
                        </div>
                        <div class="mb-3">
                            <label for="apellidos_edicion" class="form-label">Apellidos <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="apellidos_edicion" name="apellidos" required>
                        </div>
                        <div class="mb-3">
                            <label for="deuda_edicion" class="form-label">Deuda</label>
                            <input type="number" step="0.01" class="form-control" id="deuda_edicion"
                                name="deuda">
                        </div>
                        <div class="mb-3">
                            <label for="orden_edicion" class="form-label">Orden</label>
                            <input type="number" class="form-control" id="orden_edicion" name="orden">
                        </div>
                        <div class="mb-3">
                            <label for="localidade_id_edicion" class="form-label">Localidad <span
                                    class="text-danger">*</span></label>
                            <select id="localidade_id_edicion" name="localidade_id" class="form-select" required>
                                <option value="">Seleccione una localidad</option>
                                @foreach ($localidades as $localidad)
                                    <option value="{{ $localidad->id }}">{{ $localidad->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="updateForm" class="btn btn-warning updateBtn" disabled>
                        <i class="ri-save-line me-1"></i> Actualizar Cliente
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
                        <i class="ri-delete-bin-line me-2"></i>Eliminar Cliente
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
                            <h5>¿Está seguro de eliminar este cliente?</h5>
                            <p class="text-muted">Esta acción no se puede deshacer y puede afectar a los registros
                                asociados a este cliente.</p>
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

    <!-- Modal Reordenar Clientes -->
    <div class="modal fade" id="modalReordenar" tabindex="-1" aria-labelledby="modalReordenarLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info bg-soft">
                    <h5 class="modal-title text-white" id="modalReordenarLabel">
                        <i class="ri-sort-asc me-2"></i>Reordenar Clientes
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        Arrastra y suelta los clientes para cambiar su orden. El orden actual se muestra en la columna
                        "Posición".
                    </div>

                    <input type="hidden" id="localidadReordenarId">
                    <input type="hidden" id="localidadReordenarNombre">

                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th width="10%">Posición</th>
                                    <th width="15%">CI</th>
                                    <th>Nombres</th>
                                    <th>Apellidos</th>
                                    <th width="5%">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="listaReordenarClientes">
                                <!-- Los clientes se cargarán aquí -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-info" id="guardarOrden">
                        <i class="ri-save-line me-1"></i> Guardar Orden
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Confirmar Cambiar Estado -->
    <div class="modal fade" id="modalCambiarEstado" tabindex="-1" aria-labelledby="modalCambiarEstadoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning bg-soft">
                    <h5 class="modal-title" id="modalCambiarEstadoLabel">
                        <i class="ri-switch-line me-2"></i>Confirmar Cambio de Estado
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="cambiarEstadoId">
                    <input type="hidden" id="cambiarEstadoNuevo">
                    <div class="text-center mb-4">
                        <div class="avatar-lg mx-auto mb-4">
                            <div class="avatar-title bg-warning bg-soft text-warning rounded-circle">
                                <i class="ri-switch-line fs-2"></i>
                            </div>
                        </div>
                        <h5 id="mensajeCambiarEstado"></h5>
                        <p class="text-muted">Esta acción afectará los pedidos automáticos que se generen a partir de ahora.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning btnConfirmarCambiarEstado">
                        <i class="ri-check-line me-1"></i> Sí, Continuar
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
            pointer-events: none;
            /* Esto es crucial - permite que los clics pasen al input */
            z-index: 1;
        }

        #searchInput {
            padding-right: 40px;
            /* Espacio para el icono */
            width: 100%;
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

        .bg-info.bg-soft {
            background-color: rgba(var(--bs-info-rgb), 0.1) !important;
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

        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .nav-tabs .nav-link.active {
            border-bottom: 3px solid #14503c;
            font-weight: 600;
        }

        .localidad-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            color: white;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .handle {
            cursor: move;
            transition: color 0.2s;
        }

        .handle:hover {
            color: #14503c !important;
        }

        .sortable-ghost {
            opacity: 0.4;
            background-color: #f8f9fa;
        }

        .sortable-chosen {
            background-color: #e7f1ff !important;
        }
    </style>
@endsection

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        $(document).ready(function() {
            let debounceTimer;
            let currentLocalidadId = '';
            let sortableInstance = null;

            // Función para resetear el formulario de registro
            function resetAddForm() {
                $('#addForm')[0].reset();
                $('#feedback_ci_registro').removeClass('text-success text-danger').text('');
                $('.addBtn').prop('disabled', true).html('<i class="ri-save-line me-1"></i> Registrar Cliente');
            }

            // Evento cuando se cierra el modal de registro
            $('#registrar').on('hidden.bs.modal', function() {
                resetAddForm();
            });

            // Verifica si el CI del cliente está en la BD (Registro)
            $('#ci_registro').on('input', function() {
                const ci = $(this).val().trim();
                const feedback = $('#feedback_ci_registro');
                const submitBtn = $('.addBtn');
                // Limpiar el mensaje previo
                feedback.removeClass('text-success text-danger').text('');
                if (ci.length === 0) {
                    submitBtn.prop('disabled', true);
                    return;
                }
                // Cancelar la petición anterior si el usuario sigue escribiendo
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    $.ajax({
                        url: "{{ route('admin.clientes.verificar-ci') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            ci: ci
                        },
                        success: function(res) {
                            if (res.exists) {
                                feedback.addClass('text-danger').text(
                                    '⚠️ El CI ya está registrado.');
                                submitBtn.prop('disabled', true);
                            } else {
                                feedback.addClass('text-success').text(
                                    '✅ CI disponible.');
                                submitBtn.prop('disabled', false);
                            }
                        },
                        error: function() {
                            feedback.addClass('text-danger').text(
                                '❌ Error al verificar el CI.');
                            submitBtn.prop('disabled', true);
                        }
                    });
                }, 300);
            });

            // Registro de cliente
            $('#addForm').submit(function(e) {
                e.preventDefault();
                const submitBtn = $('.addBtn');
                submitBtn.prop('disabled', true).html(
                    '<i class="ri-loader-4-line spin me-1"></i> Registrando...');
                $.ajax({
                    url: "{{ route('admin.clientes.registrar') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.success) {
                            showNotification('success', res.msg);
                            $('#registrar').modal('hide');
                            loadResults($('#searchInput').val().trim(), currentLocalidadId);
                            resetAddForm();
                        } else {
                            showNotification('error', res.msg);
                            submitBtn.prop('disabled', false).html(
                                '<i class="ri-save-line me-1"></i> Registrar Cliente');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Error al registrar el cliente.';
                        if (xhr.responseJSON?.errors) {
                            const errors = xhr.responseJSON.errors;
                            errorMsg = Object.values(errors).flat().join(', ');
                        } else if (xhr.responseJSON?.msg) {
                            errorMsg = xhr.responseJSON.msg;
                        }
                        showNotification('error', errorMsg);
                        submitBtn.prop('disabled', false).html(
                            '<i class="ri-save-line me-1"></i> Registrar Cliente');
                    }
                });
            });

            // Editar cliente
            $(document).on('click', '.editBtn', function() {
                var data = $(this).data('bs-obj');
                console.log('Datos del cliente para editar:', data);
                if (!data || !data.id) {
                    console.error('No se recibieron datos válidos para editar');
                    showNotification('error', 'Error: No se pudieron cargar los datos para editar');
                    return;
                }
                $('#clienteId').val(data.id);
                $('#ci_edicion').val(data.ci || '');
                $('#nombres_edicion').val(data.nombres || '');
                $('#apellidos_edicion').val(data.apellidos || '');
                $('#deuda_edicion').val(data.deuda || '0');
                $('#orden_edicion').val(data.orden || '0');
                $('#localidade_id_edicion').val(data.localidade_id || '');
                $('#feedback_ci_edicion').removeClass('text-success text-danger').text('');
                $('.updateBtn').prop('disabled', false);
            });

            // Validación en tiempo real para el campo de EDICIÓN (CI)
            $('#ci_edicion').on('input', function() {
                const ci = $(this).val().trim();
                const feedback = $('#feedback_ci_edicion');
                const submitBtn = $('.updateBtn');
                const id = $('#clienteId').val();
                feedback.removeClass('text-success text-danger').text('');
                if (ci.length === 0) {
                    submitBtn.prop('disabled', true);
                    return;
                }
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    $.ajax({
                        url: "{{ route('admin.clientes.verificar-ci-edicion') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            ci: ci,
                            id: id
                        },
                        success: function(res) {
                            if (res.exists) {
                                feedback.addClass('text-danger').text(
                                    '⚠️ Este CI ya está registrado.');
                                submitBtn.prop('disabled', true);
                            } else {
                                feedback.addClass('text-success').text(
                                    '✅ CI disponible.');
                                submitBtn.prop('disabled', false);
                            }
                        },
                        error: function() {
                            feedback.addClass('text-danger').text(
                                '❌ Error al verificar el CI.');
                            submitBtn.prop('disabled', true);
                        }
                    });
                }, 300);
            });

            // Actualizar cliente
            $('#updateForm').submit(function(e) {
                e.preventDefault();
                const submitBtn = $('.updateBtn');
                submitBtn.prop('disabled', true).html(
                    '<i class="ri-loader-4-line spin me-1"></i> Actualizando...');
                const clienteId = $('#clienteId').val();
                if (!clienteId) {
                    showNotification('error', 'Error: ID de cliente no válido');
                    submitBtn.prop('disabled', false).html(
                        '<i class="ri-save-line me-1"></i> Actualizar Cliente');
                    return;
                }
                $.ajax({
                    url: "{{ route('admin.clientes.modificar') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.success) {
                            showNotification('success', res.msg);
                            $('#modalModificar').modal('hide');
                            loadResults($('#searchInput').val().trim(), currentLocalidadId);
                        } else {
                            showNotification('error', res.msg);
                            submitBtn.prop('disabled', false).html(
                                '<i class="ri-save-line me-1"></i> Actualizar Cliente');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Error al actualizar el cliente.';
                        if (xhr.responseJSON?.errors) {
                            const errors = xhr.responseJSON.errors;
                            errorMsg = Object.values(errors).flat().join(', ');
                        } else if (xhr.responseJSON?.msg) {
                            errorMsg = xhr.responseJSON.msg;
                        }
                        showNotification('error', errorMsg);
                        submitBtn.prop('disabled', false).html(
                            '<i class="ri-save-line me-1"></i> Actualizar Cliente');
                    }
                });
            });

            // Eliminar cliente
            $(document).on('click', '.deleteBtn', function() {
                var data = $(this).data('bs-obj');
                console.log('Datos del cliente para eliminar:', data);
                if (!data || !data.id) {
                    console.error('No se recibieron datos válidos para eliminar');
                    showNotification('error', 'Error: No se pudieron cargar los datos para eliminar');
                    return;
                }
                $('#eliminarId').val(data.id);
            });

            // Confirmar eliminación
            $('#deleteForm').submit(function(e) {
                e.preventDefault();
                const submitBtn = $('.btnDelete');
                submitBtn.prop('disabled', true).html(
                    '<i class="ri-loader-4-line spin me-1"></i> Eliminando...');
                const eliminarId = $('#eliminarId').val();
                if (!eliminarId) {
                    showNotification('error', 'Error: ID de cliente no válido');
                    submitBtn.prop('disabled', false).html(
                        '<i class="ri-delete-bin-line me-1"></i> Sí, Eliminar');
                    return;
                }
                $.ajax({
                    url: "{{ route('admin.clientes.eliminar') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: eliminarId
                    },
                    success: function(res) {
                        if (res.success) {
                            showNotification('success', res.msg);
                            $('#modalEliminar').modal('hide');
                            loadResults($('#searchInput').val().trim(), currentLocalidadId);
                        } else {
                            showNotification('error', res.msg);
                            submitBtn.prop('disabled', false).html(
                                '<i class="ri-delete-bin-line me-1"></i> Sí, Eliminar');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Error al eliminar el cliente.';
                        if (xhr.responseJSON?.msg) {
                            errorMsg = xhr.responseJSON.msg;
                        } else if (xhr.responseJSON?.errors) {
                            const errors = xhr.responseJSON.errors;
                            errorMsg = Object.values(errors).flat().join(', ');
                        }
                        showNotification('error', errorMsg);
                        submitBtn.prop('disabled', false).html(
                            '<i class="ri-delete-bin-line me-1"></i> Sí, Eliminar');
                    }
                });
            });

            // Toggle habilitar/deshabilitar cliente - mostrar modal
            $(document).on('click', '.toggleHabilitadoBtn', function() {
                const btn = $(this);
                const clienteId = btn.data('id');
                const habilitado = btn.data('habilitado');
                const nuevoEstado = habilitado ? 0 : 1;

                $('#cambiarEstadoId').val(clienteId);
                $('#cambiarEstadoNuevo').val(nuevoEstado);

                if (nuevoEstado === 0) {
                    $('#mensajeCambiarEstado').html('¿Está seguro de <strong>deshabilitar</strong> este cliente?');
                } else {
                    $('#mensajeCambiarEstado').html('¿Está seguro de <strong>habilitar</strong> este cliente?');
                }

                $('#modalCambiarEstado').modal('show');
            });

            // Confirmar cambio de estado
            $('.btnConfirmarCambiarEstado').on('click', function() {
                const clienteId = $('#cambiarEstadoId').val();
                const nuevoEstado = parseInt($('#cambiarEstadoNuevo').val());
                const submitBtn = $(this);

                submitBtn.prop('disabled', true).html('<i class="ri-loader-4-line spin me-1"></i> Cambiando...');

                $.ajax({
                    url: "{{ route('admin.clientes.cambiar-estado') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: clienteId,
                        habilitado: nuevoEstado
                    },
                    success: function(res) {
                        if (res.success) {
                            showNotification('success', res.msg);
                            $('#modalCambiarEstado').modal('hide');
                            // Recargar página para actualizar todos los botones
                            window.location.reload();
                        } else {
                            showNotification('error', res.msg);
                            submitBtn.prop('disabled', false).html('<i class="ri-check-line me-1"></i> Sí, Continuar');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Error al cambiar estado del cliente.';
                        if (xhr.responseJSON?.msg) {
                            errorMsg = xhr.responseJSON.msg;
                        }
                        showNotification('error', errorMsg);
                        submitBtn.prop('disabled', false).html('<i class="ri-check-line me-1"></i> Sí, Continuar');
                    }
                });
            });

            // Función para cargar resultados con filtros
            function loadResults(search = '', localidadId = '') {
                let url = '{{ route('admin.clientes.listar') }}';
                let container = '#results-container';

                // Configurar diferentes URLs y contenedores según el contexto
                if (localidadId && localidadId !== 'todos' && localidadId !== '') {
                    url = '{{ url('/admin/clientes/por-localidad') }}/' + localidadId;
                    container = '#results-container-localidad-' + localidadId;

                    // Solo mostrar loading si no hay tabla
                    const tableContainer = $(container).find('table');
                    if (tableContainer.length === 0) {
                        $(container).html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando clientes de la localidad...</p>
                </div>
            `);
                    }
                }

                $.ajax({
                    url: url,
                    method: 'GET',
                    data: {
                        search: search,
                        localidad_id: localidadId || ''
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (localidadId && localidadId !== 'todos' && localidadId !== '') {
                            // Para pestañas de localidades específicas (sin paginación)
                            const tableContainer = $(container).find('table');
                            if (tableContainer.length) {
                                tableContainer.find('tbody').replaceWith(response.html);
                                $(container).find('.total-count').text(response.total || 0);
                            }
                        } else {
                            // Para la pestaña "Todos"
                            $('#results-container .table-responsive table tbody').replaceWith(response
                                .html);
                            $('#pagination-container').html(response.pagination);
                            updateCounters(response);
                        }

                        if (typeof feather !== 'undefined') {
                            feather.replace();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cargar resultados:', error);
                        showNotification('error', 'Error al cargar los resultados');
                        if (localidadId && localidadId !== 'todos' && localidadId !== '') {
                            $(container).html(`
                    <div class="alert alert-danger text-center">
                        Error al cargar los clientes de esta localidad
                    </div>
                `);
                        }
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
                    loadResults(searchTerm, currentLocalidadId);
                }, 300);
            });

            // Limpiar filtros
            $('#clearFilters').on('click', function() {
                $('#searchInput').val('');
                loadResults('', currentLocalidadId);
            });

            // Manejar clics en la paginación de la pestaña "Todos"
            $(document).on('click', '#pagination-container .pagination a', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                const search = $('#searchInput').val().trim();
                if (!url) return;

                $.ajax({
                    url: url,
                    method: 'GET',
                    data: {
                        search: search,
                        localidad_id: currentLocalidadId
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#results-container .table-responsive table tbody').replaceWith(
                            response.html);
                        $('#pagination-container').html(response.pagination);
                        updateCounters(response);
                        if (typeof feather !== 'undefined') {
                            feather.replace();
                        }
                    },
                    error: function() {
                        showNotification('error', 'Error al cargar la página');
                    }
                });
            });

            // Manejar clics en la paginación para pestañas de localidades
            $(document).on('click', '[id^="results-container-localidad-"] .pagination a', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                const search = $('#searchInput').val().trim();
                const container = $(this).closest('[id^="results-container-localidad-"]');
                const localidadId = container.attr('id').replace('results-container-localidad-', '');

                if (!url) return;

                $.ajax({
                    url: url,
                    method: 'GET',
                    data: {
                        search: search,
                        localidad_id: localidadId
                    },
                    dataType: 'json',
                    success: function(response) {
                        container.find('tbody').replaceWith(response.html);
                        container.find('.pagination-container').html(response.pagination);
                        container.find('.showing-count').text(response.from || 0);
                        container.find('.to-count').text(response.to || 0);
                        container.find('.total-count').text(response.total || 0);
                    },
                    error: function() {
                        showNotification('error', 'Error al cargar la página');
                    }
                });
            });

            // Manejar clics en las pestañas
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                const target = $(e.target).attr('href');

                if (target === '#todos') {
                    currentLocalidadId = '';
                } else {
                    currentLocalidadId = target.replace('#localidad', '');
                }

                loadResults($('#searchInput').val().trim(), currentLocalidadId);
            });

            // Inicialmente, cargamos la pestaña "Todos"
            currentLocalidadId = '';
            loadResults('', '');

            // ========== CÓDIGO PARA REORDENAMIENTO ==========

            // Botón para abrir modal de reordenar
            $(document).on('click', '.btn-reordenar', function() {
                const localidadId = $(this).data('localidad-id');
                const localidadNombre = $(this).data('localidad-nombre');

                $('#localidadReordenarId').val(localidadId);
                $('#localidadReordenarNombre').val(localidadNombre);
                $('#modalReordenarLabel').html('<i class="ri-sort-asc me-2"></i>Reordenar Clientes - ' +
                    localidadNombre);

                cargarClientesParaReordenar(localidadId);
                $('#modalReordenar').modal('show');
            });

            // Cargar clientes para reordenar
            function cargarClientesParaReordenar(localidadId) {
                $('#listaReordenarClientes').html(`
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando clientes...</p>
                        </td>
                    </tr>
                `);

                $.ajax({
                    url: "{{ url('admin/clientes/por-localidad-completa') }}/" + localidadId,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            renderizarListaReordenar(response.clientes);
                            inicializarSortable();
                        } else {
                            $('#listaReordenarClientes').html(`
                                <tr>
                                    <td colspan="5" class="text-center text-danger py-4">
                                        <i class="ri-error-warning-line display-4"></i>
                                        <p class="mt-2">${response.msg || 'Error al cargar los clientes'}</p>
                                    </td>
                                </tr>
                            `);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cargar clientes:', error);
                        $('#listaReordenarClientes').html(`
                            <tr>
                                <td colspan="5" class="text-center text-danger py-4">
                                    <i class="ri-error-warning-line display-4"></i>
                                    <p class="mt-2">Error al cargar los clientes: ${error}</p>
                                </td>
                            </tr>
                        `);
                    }
                });
            }

            // Renderizar la lista de clientes para reordenar
            function renderizarListaReordenar(clientes) {
                let html = '';

                if (clientes.length === 0) {
                    html = `
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="ri-user-search-line display-4"></i>
                                    <p class="mt-2">No hay clientes en esta localidad</p>
                                </div>
                            </td>
                        </tr>
                    `;
                } else {
                    clientes.forEach((cliente) => {
                        html += `
                            <tr data-cliente-id="${cliente.id}">
                                <td>
                                    <span class="badge bg-primary">${cliente.orden}</span>
                                </td>
                                <td><strong>${cliente.ci}</strong></td>
                                <td>${cliente.nombres}</td>
                                <td>${cliente.apellidos}</td>
                                <td>
                                    <i class="ri-draggable handle text-muted" style="cursor: move; font-size: 1.2rem;"></i>
                                </td>
                            </tr>
                        `;
                    });
                }

                $('#listaReordenarClientes').html(html);
            }

            // Inicializar SortableJS para arrastrar y soltar
            function inicializarSortable() {
                if (sortableInstance) {
                    sortableInstance.destroy();
                }

                const lista = document.getElementById('listaReordenarClientes');
                if (lista) {
                    sortableInstance = new Sortable(lista, {
                        handle: '.handle',
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        onEnd: function(evt) {
                            actualizarNumerosOrden();
                        }
                    });
                }
            }

            // Actualizar los números de orden después de arrastrar
            function actualizarNumerosOrden() {
                $('#listaReordenarClientes tr').each(function(index) {
                    const nuevoOrden = index + 1;
                    $(this).find('.badge').text(nuevoOrden).removeClass('bg-success bg-warning').addClass(
                        'bg-success');
                });
            }

            // Guardar el nuevo orden
            $('#guardarOrden').on('click', function() {
                const localidadId = $('#localidadReordenarId').val();
                const clientesData = [];

                $('#listaReordenarClientes tr[data-cliente-id]').each(function(index) {
                    const clienteId = $(this).data('cliente-id');
                    const nuevoOrden = index + 1;

                    if (clienteId) {
                        clientesData.push({
                            id: clienteId,
                            orden: nuevoOrden
                        });
                    }
                });

                if (clientesData.length === 0) {
                    showNotification('warning', 'No hay clientes para ordenar');
                    return;
                }

                const btnGuardar = $(this);
                btnGuardar.prop('disabled', true).html(
                    '<i class="ri-loader-4-line spin me-1"></i> Guardando...');

                $.ajax({
                    url: "{{ route('admin.clientes.actualizar-orden') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        localidad_id: localidadId,
                        clientes: clientesData
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('success', response.msg);
                            $('#modalReordenar').modal('hide');

                            // Recargar la pestaña actual para mostrar el nuevo orden
                            loadResults($('#searchInput').val().trim(), localidadId);
                        } else {
                            showNotification('error', response.msg);
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Error al guardar el orden';
                        if (xhr.responseJSON?.msg) {
                            errorMsg = xhr.responseJSON.msg;
                        } else if (xhr.responseJSON?.errors) {
                            const errors = xhr.responseJSON.errors;
                            errorMsg = Object.values(errors).flat().join(', ');
                        }
                        showNotification('error', errorMsg);
                    },
                    complete: function() {
                        btnGuardar.prop('disabled', false).html(
                            '<i class="ri-save-line me-1"></i> Guardar Orden');
                    }
                });
            });

            // Función para mostrar notificaciones
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
