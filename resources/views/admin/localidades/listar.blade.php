@extends('admin.dashboard')
@section('admin')
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-light rounded-3 p-3">
                    <div>
                        <h4 class="mb-1 fw-bold text-dark">Gestión de Localidades</h4>
                        <p class="text-muted mb-0">Administra las zonas de entrega y sus colores de identificación</p>
                    </div>

                    @if (Auth::guard('web')->user()->can('localidades.registrar'))
                        <div class="page-title-right">
                            <button type="button" class="btn btn-primary btn-lg waves-effect waves-light"
                                data-bs-toggle="modal" data-bs-target="#registrar">
                                <i class="ri-add-line align-bottom me-1"></i> Nueva Localidad
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <!-- Filters & Stats Cards -->
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="card border border-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-primary bg-opacity-10">
                                    <i class="ri-map-pin-2-line fs-24 text-primary"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1">Total Localidades</p>
                                <h4 class="mb-0">{{ $localidades->total() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-9">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-3">
                            <div class="flex-grow-1">
                                <div class="search-box position-relative">
                                    <input type="text" id="searchInput" class="form-control search form-control-lg ps-5"
                                        placeholder="Buscar localidad..." value="{{ request('search') ?? '' }}">
                                    <i
                                        class="ri-search-line search-icon position-absolute top-50 start-0 translate-middle-y text-muted ms-3"></i>
                                </div>
                            </div>
                            <div>
                                <button type="button" id="clearFilters" class="btn btn-outline-secondary btn-lg">
                                    <i class="ri-refresh-line align-bottom me-1"></i> Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Results Table -->
        <div class="row">
            <div class="col-12">
                <div class="card border border-light shadow-sm">
                    <div class="card-header border-bottom-dashed d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0 fw-bold">Listado de Localidades</h5>
                        <div class="dropdown">
                            <button class="btn btn-soft-secondary btn-sm dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-equalizer-line align-bottom me-1"></i> Vista
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item active" href="#"><i
                                            class="ri-list-check align-bottom me-2"></i> Tabla</a></li>
                                <li><a class="dropdown-item" href="#"><i class="ri-grid-line align-bottom me-2"></i>
                                        Cuadrícula</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-borderless table-centered align-middle table-nowrap mb-0">
                                <thead class="table-light text-muted fw-semibold">
                                    <tr>
                                        <th class="px-3 py-3" width="5%">#</th>
                                        <th class="px-3 py-3">Nombre de la Localidad</th>
                                        <th class="px-3 py-3 text-center" width="15%">
                                            <i class="ri-checkbox-multiple-blank-line align-bottom me-1"></i> Color
                                        </th>
                                        <th class="px-3 py-3 text-end" width="15%">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="localidadesTableBody">
                                    @include('admin.localidades.partials.table-body')
                                </tbody>
                            </table>
                        </div>
                        @if ($localidades->total() > 0)
                            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">
                                <div class="results-count text-muted">
                                    Mostrando <span class="fw-medium">{{ $localidades->firstItem() }}</span> a
                                    <span class="fw-medium">{{ $localidades->lastItem() }}</span> de
                                    <span class="fw-medium">{{ $localidades->total() }}</span> resultados
                                </div>
                                <div class="pagination-container">
                                    {{ $localidades->appends(request()->input())->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Registrar -->
    <div class="modal fade" id="registrar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary-subtle p-3 position-relative">
                    <h5 class="modal-title fw-bold text-primary" id="registrarLabel">
                        <i class="ri-add-line me-2 align-bottom"></i>Registrar Nueva Localidad
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="addForm" class="needs-validation" novalidate>
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-lg-8">
                                <div class="mb-3">
                                    <label for="nombre_registro" class="form-label fw-medium">Nombre de la Localidad <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="nombre_registro" name="nombre"
                                        class="form-control form-control-lg" placeholder="Ej: Zona Norte - Centro"
                                        required>
                                    <div class="invalid-feedback">Por favor ingresa el nombre de la localidad</div>
                                    <small id="feedback_registro" class="form-text mt-1"></small>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label for="color_registro" class="form-label fw-medium">Color <span
                                            class="text-danger">*</span></label>
                                    <div class="d-flex align-items-center gap-3">
                                        <input type="color" id="color_registro" name="color"
                                            class="form-control form-control-color shadow-none p-1" value="#14503c"
                                            title="Selecciona un color">
                                        <div class="color-preview bg-primary rounded"
                                            style="width: 40px; height: 40px; border-radius: 8px; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                        </div>
                                    </div>
                                    <small class="text-muted">Selecciona un color para identificar visualmente esta
                                        localidad</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3">
                        <button type="button" class="btn btn-soft-secondary btn-lg"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-lg addBtn" disabled>
                            <i class="ri-save-3-line me-1 align-bottom"></i>
                            <span class="submit-text">Registrar Localidad</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Modificar -->
    <div class="modal fade" id="modalModificar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning-subtle p-3 position-relative">
                    <h5 class="modal-title fw-bold text-warning" id="modalModificarLabel">
                        <i class="ri-edit-line me-2 align-bottom"></i>Editar Localidad
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="updateForm" class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="localidadId">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-lg-8">
                                <div class="mb-3">
                                    <label for="nombre_edicion" class="form-label fw-medium">Nombre de la Localidad <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg" id="nombre_edicion"
                                        name="nombre" required>
                                    <div class="invalid-feedback">Por favor ingresa el nombre de la localidad</div>
                                    <small id="feedback_edicion" class="form-text mt-1"></small>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label for="color_edicion" class="form-label fw-medium">Color <span
                                            class="text-danger">*</span></label>
                                    <div class="d-flex align-items-center gap-3">
                                        <input type="color" id="color_edicion" name="color"
                                            class="form-control form-control-color shadow-none p-1"
                                            title="Selecciona un color">
                                        <div id="color_preview_edicion"
                                            style="width: 40px; height: 40px; border-radius: 8px; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                        </div>
                                    </div>
                                    <small class="text-muted">Selecciona un color para identificar visualmente esta
                                        localidad</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3">
                        <button type="button" class="btn btn-soft-secondary btn-lg"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning btn-lg updateBtn" disabled>
                            <i class="ri-refresh-line me-1 align-bottom"></i>
                            <span class="submit-text">Actualizar Localidad</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar -->
    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger-subtle p-3 position-relative">
                    <h5 class="modal-title fw-bold text-danger" id="modalEliminarLabel">
                        <i class="ri-delete-bin-line me-2 align-bottom"></i>Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="deleteForm">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="id" id="eliminarId">
                    <div class="modal-body p-4 text-center">
                        <div class="mb-4">
                            <div class="avatar-xl mx-auto">
                                <div class="avatar-title bg-danger bg-opacity-10 text-danger rounded-circle fs-2xl">
                                    <i class="ri-alert-line"></i>
                                </div>
                            </div>
                        </div>
                        <h4 class="mb-3 fw-bold">¿Estás seguro de eliminar esta localidad?</h4>
                        <p class="text-muted mb-0">Esta acción no se puede deshacer. Si hay registros asociados a esta
                            localidad, deberás actualizarlos manualmente.</p>
                        <div class="mt-3 p-3 bg-light rounded">
                            <small class="text-danger fw-medium">Advertencia:</small>
                            <small class="text-muted">Se recomienda actualizar los registros asociados antes de eliminar
                                esta localidad.</small>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center bg-light p-3">
                        <button type="button" class="btn btn-soft-secondary btn-lg"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger btn-lg btnDelete">
                            <i class="ri-delete-bin-line me-1 align-bottom"></i>
                            <span class="submit-text">Sí, Eliminar</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        /* Responsive Table */
        .table-nowrap td,
        .table-nowrap th {
            white-space: nowrap;
        }

        /* Color badges */
        .color-badge {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 4px;
            margin-right: 8px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        /* Action buttons */
        .btn-action {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-size: 0.85rem;
            transition: all 0.2s ease;
        }

        .btn-action i {
            font-size: 1rem;
            line-height: 1;
        }

        .btn-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Empty state */
        .empty-state {
            padding: 3rem 1.5rem;
            text-align: center;
        }

        .empty-icon {
            font-size: 4rem;
            color: #adb5bd;
            margin-bottom: 1rem;
        }

        /* Pagination */
        .pagination {
            --bs-pagination-border-radius: 6px;
            --bs-pagination-hover-bg: var(--bs-primary);
            --bs-pagination-hover-color: #fff;
        }

        /* Mobile optimization */
        @media (max-width: 767.98px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 10px;
            }

            .page-title-box {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 15px;
            }

            .d-flex.justify-content-between.align-items-center {
                flex-direction: column;
                gap: 12px;
            }

            .search-box {
                width: 100%;
            }

            .modal-dialog {
                margin: 0.5rem;
                max-width: calc(100% - 1rem);
            }

            .table-responsive {
                font-size: 0.875rem;
            }

            .btn-group .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }

        /* Loading spinner */
        .btn .spin {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Toast notifications */
        .toast-container {
            z-index: 9999;
        }

        .toast {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            max-width: 400px;
        }

        .toast-body {
            padding: 12px 16px;
            font-weight: 500;
        }

        /* Color previews */
        .color-preview,
        #color_preview_edicion {
            transition: all 0.2s ease;
            cursor: pointer;
        }
    </style>
@endpush

@push('script')
    <script>
        $(document).ready(function() {
            let debounceTimer;
            let isProcessing = false;

            // Inicializar tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Actualizar vista previa del color en registro
            $(document).on('input', '#color_registro', function() {
                const color = $(this).val();
                $('.color-preview').css('background-color', color);
            });

            // Actualizar vista previa del color en edición
            $(document).on('input', '#color_edicion', function() {
                const color = $(this).val();
                $('#color_preview_edicion').css('background-color', color);
                // Asegurar que el valor sea un color hexadecimal válido
                if (!/^#[0-9A-F]{6}$/i.test(color)) {
                    $(this).val('#14503c');
                    $('#color_preview_edicion').css('background-color', '#14503c');
                }
            });

            // Inicializar la vista previa cuando se abre el modal de registro
            $('#registrar').on('show.bs.modal', function() {
                const initialColor = $('#color_registro').val();
                $('.color-preview').css('background-color', initialColor);
            });

            // Resetear el formulario y la vista previa al cerrar el modal de registro
            $('#registrar').on('hidden.bs.modal', function() {
                $('#addForm')[0].reset();
                $('#color_registro').val('#14503c');
                $('.color-preview').css('background-color', '#14503c');
                $('#feedback_registro').removeClass('text-success text-danger').text('');
                $('.addBtn').prop('disabled', true);
                $('#addForm').removeClass('was-validated');
            });

            // Verificar nombre al registrar
            $('#nombre_registro').on('input', function() {
                const nombre = $(this).val().trim();
                const feedback = $('#feedback_registro');
                const submitBtn = $('.addBtn');
                const form = $(this).closest('form');

                feedback.removeClass('text-success text-danger').text('');
                form.find('.invalid-feedback').hide();

                if (nombre.length === 0) {
                    submitBtn.prop('disabled', true);
                    return;
                }

                if (nombre.length < 3) {
                    feedback.addClass('text-danger').text('El nombre debe tener al menos 3 caracteres');
                    submitBtn.prop('disabled', true);
                    return;
                }

                if (nombre.length > 100) {
                    feedback.addClass('text-danger').text('El nombre no puede exceder los 100 caracteres');
                    submitBtn.prop('disabled', true);
                    return;
                }

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    $.ajax({
                        url: "{{ route('admin.localidades.verificar') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            nombre: nombre
                        },
                        beforeSend: function() {
                            feedback.html(
                                '<i class="ri-loader-4-line spin me-1"></i> Verificando...'
                            );
                        },
                        success: function(res) {
                            if (res.exists) {
                                feedback.addClass('text-danger').html(
                                    '<i class="ri-error-warning-line me-1"></i> Esta localidad ya existe'
                                );
                                submitBtn.prop('disabled', true);
                                $(this).addClass('is-invalid');
                            } else {
                                feedback.addClass('text-success').html(
                                    '<i class="ri-checkbox-circle-line me-1"></i> Nombre disponible'
                                );
                                submitBtn.prop('disabled', false);
                                $(this).removeClass('is-invalid');
                            }
                        }.bind(this),
                        error: function() {
                            feedback.addClass('text-danger').html(
                                '<i class="ri-alert-line me-1"></i> Error al verificar el nombre'
                            );
                            submitBtn.prop('disabled', true);
                        }
                    });
                }, 400);
            });

            // Verificar nombre al editar
            $('#nombre_edicion').on('input', function() {
                const nombre = $(this).val().trim();
                const feedback = $('#feedback_edicion');
                const submitBtn = $('.updateBtn');
                const id = $('#localidadId').val();
                const form = $(this).closest('form');

                feedback.removeClass('text-success text-danger').text('');
                form.find('.invalid-feedback').hide();

                if (nombre.length === 0) {
                    submitBtn.prop('disabled', true);
                    return;
                }

                if (nombre.length < 3) {
                    if (nombre.length > 0) {
                        feedback.addClass('text-danger').text('El nombre debe tener al menos 3 caracteres');
                    }
                    submitBtn.prop('disabled', true);
                    return;
                }

                if (nombre.length > 100) {
                    feedback.addClass('text-danger').text('El nombre no puede exceder los 100 caracteres');
                    submitBtn.prop('disabled', true);
                    return;
                }

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    $.ajax({
                        url: "{{ route('admin.localidades.verificaredicion') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            nombre: nombre,
                            id: id
                        },
                        beforeSend: function() {
                            feedback.html(
                                '<i class="ri-loader-4-line spin me-1"></i> Verificando...'
                            );
                        },
                        success: function(res) {
                            if (res.exists) {
                                feedback.addClass('text-danger').html(
                                    '<i class="ri-error-warning-line me-1"></i> Esta localidad ya existe'
                                );
                                submitBtn.prop('disabled', true);
                                $(this).addClass('is-invalid');
                            } else {
                                feedback.addClass('text-success').html(
                                    '<i class="ri-checkbox-circle-line me-1"></i> Nombre disponible'
                                );
                                submitBtn.prop('disabled', false);
                                $(this).removeClass('is-invalid');
                            }
                        }.bind(this),
                        error: function() {
                            feedback.addClass('text-danger').html(
                                '<i class="ri-alert-line me-1"></i> Error al verificar el nombre'
                            );
                            submitBtn.prop('disabled', true);
                        }
                    });
                }, 400);
            });

            // Editar localidad
            // Editar localidad - versión mejorada
            $(document).on('click', '.editBtn', function() {
                var data = $(this).data('bs-obj');
                $('#localidadId').val(data.id);
                $('#nombre_edicion').val(data.nombre);
                $('#color_edicion').val(data.color);
                $('#color_preview_edicion').css('background-color', data.color);

                // Reset feedback y habilitar botón
                $('#feedback_edicion').removeClass('text-success text-danger').text('');
                $('.updateBtn').prop('disabled', false);
                $('#updateForm').removeClass('was-validated');

                // Forzar la verificación del nombre
                $('#nombre_edicion').trigger('input');
            });

            // Eliminar localidad
            $(document).on('click', '.deleteBtn', function() {
                var data = $(this).data('bs-obj');
                $('#eliminarId').val(data.id);
            });

            // Registro de localidad
            $('#addForm').submit(function(e) {
                e.preventDefault();
                if (isProcessing) return;
                isProcessing = true;

                const form = $(this)[0];
                if (form.checkValidity() === false) {
                    e.stopPropagation();
                    form.classList.add('was-validated');
                    isProcessing = false;
                    return;
                }

                const submitBtn = $('.addBtn');
                const originalText = submitBtn.find('.submit-text').text();
                submitBtn.prop('disabled', true).html(
                    '<span class="spin me-2"></span><span class="submit-text">Registrando...</span>'
                );

                $.ajax({
                    url: "{{ route('admin.localidades.registrar') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.success) {
                            showNotification('success', res.msg);
                            $('#registrar').modal('hide');
                            $('#addForm')[0].reset();
                            $('#color_registro').val('#14503c');
                            $('.color-preview').css('background-color', '#14503c');
                            $('.addBtn').prop('disabled', true);
                            $('#feedback_registro').removeClass('text-success text-danger')
                                .text('');

                            // Recargar con un pequeño delay para asegurar que el servidor procesó el registro
                            setTimeout(() => {
                                loadResults($('#searchInput').val().trim());
                            }, 500);
                        } else {
                            showNotification('error', res.msg ||
                                'Error al registrar la localidad');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg =
                            'Error al registrar la localidad. Por favor intenta nuevamente.';
                        if (xhr.responseJSON?.errors?.nombre) {
                            errorMsg = xhr.responseJSON.errors.nombre[0];
                        } else if (xhr.responseJSON?.msg) {
                            errorMsg = xhr.responseJSON.msg;
                        }
                        showNotification('error', errorMsg);
                    },
                    complete: function() {
                        isProcessing = false;
                        submitBtn.prop('disabled', false).html(
                            '<i class="ri-save-3-line me-1 align-bottom"></i><span class="submit-text">' +
                            originalText + '</span>'
                        );
                    }
                });
            });

            // Actualizar localidad
            // Actualizar localidad - VERSIÓN CORREGIDA
            $('#updateForm').submit(function(e) {
                e.preventDefault();
                if (isProcessing) return;
                isProcessing = true;

                const form = $(this)[0];
                if (form.checkValidity() === false) {
                    e.stopPropagation();
                    form.classList.add('was-validated');
                    isProcessing = false;
                    return;
                }

                const submitBtn = $('.updateBtn');
                const originalText = submitBtn.find('.submit-text').text();
                submitBtn.prop('disabled', true).html(
                    '<span class="spin me-2"></span><span class="submit-text">Actualizando...</span>'
                );

                $.ajax({
                    url: "{{ route('admin.localidades.modificar') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.success) {
                            showNotification('success', res.msg);
                            $('#modalModificar').modal('hide');

                            // FORZAR ACTUALIZACIÓN INMEDIATA
                            loadResults($('#searchInput').val().trim());

                            // RESET DEL FORMULARIO DE EDICIÓN
                            $('#updateForm')[0].reset();
                            $('#feedback_edicion').removeClass('text-success text-danger').text(
                                '');
                        } else {
                            showNotification('error', res.msg ||
                                'Error al actualizar la localidad');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg =
                            'Error al actualizar la localidad. Por favor intenta nuevamente.';
                        if (xhr.responseJSON?.errors?.nombre) {
                            errorMsg = xhr.responseJSON.errors.nombre[0];
                        } else if (xhr.responseJSON?.msg) {
                            errorMsg = xhr.responseJSON.msg;
                        }
                        showNotification('error', errorMsg);
                    },
                    complete: function() {
                        isProcessing = false;
                        submitBtn.prop('disabled', false).html(
                            '<i class="ri-refresh-line me-1 align-bottom"></i><span class="submit-text">' +
                            originalText + '</span>'
                        );
                    }
                });
            });

            // Eliminar localidad
            // Eliminar localidad - VERSIÓN CORREGIDA
            $('#deleteForm').submit(function(e) {
                e.preventDefault();
                if (isProcessing) return;
                isProcessing = true;

                const submitBtn = $('.btnDelete');
                const originalText = submitBtn.find('.submit-text').text();
                submitBtn.prop('disabled', true).html(
                    '<span class="spin me-2"></span><span class="submit-text">Eliminando...</span>'
                );

                $.ajax({
                    url: "{{ route('admin.localidades.eliminar') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.success) {
                            showNotification('success', res.msg);
                            $('#modalEliminar').modal('hide');

                            // FORZAR ACTUALIZACIÓN INMEDIATA
                            loadResults($('#searchInput').val().trim());

                            // RESET DEL FORMULARIO DE ELIMINACIÓN
                            $('#deleteForm')[0].reset();
                        } else {
                            showNotification('error', res.msg ||
                                'Error al eliminar la localidad');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg =
                            'Error al eliminar la localidad. Por favor intenta nuevamente.';
                        if (xhr.responseJSON?.msg) {
                            errorMsg = xhr.responseJSON.msg;
                        }
                        showNotification('error', errorMsg);
                    },
                    complete: function() {
                        isProcessing = false;
                        submitBtn.prop('disabled', false).html(
                            '<i class="ri-delete-bin-line me-1 align-bottom"></i><span class="submit-text">' +
                            originalText + '</span>'
                        );
                    }
                });
            });

            // Función para cargar resultados con filtros - VERSIÓN MEJORADA
            function loadResults(search = '') {
                if (isProcessing) return;
                isProcessing = true;

                console.log('Cargando resultados con búsqueda:', search); // Para debug

                $.ajax({
                    url: '{{ route('admin.localidades.listar') }}',
                    method: 'GET',
                    data: {
                        search: search
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        $('#localidadesTableBody').html(`
                <tr>
                    <td colspan="4" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando resultados...</p>
                    </td>
                </tr>
            `);
                    },
                    success: function(response) {
                        console.log('Respuesta recibida:', response); // Para debug

                        // Actualizar SOLO el contenido del tbody
                        $('#localidadesTableBody').html(response.html);

                        // Actualizar la paginación de manera más robusta
                        const paginationHtml = $(response.pagination).find('ul.pagination');
                        if (paginationHtml.length) {
                            $('.pagination-container').html(paginationHtml);
                        } else {
                            $('.pagination-container').html(response.pagination);
                        }

                        // Actualizar los contadores de resultados
                        if (response.total !== undefined) {
                            $('.results-count').html(
                                `Mostrando <span class="fw-medium">${response.from || 0}</span> a 
                    <span class="fw-medium">${response.to || 0}</span> de 
                    <span class="fw-medium">${response.total}</span> resultados`
                            );
                        }

                        // Actualizar el contador de total en la tarjeta
                        $('.card h4').text(response.total);

                        // Re-inicializar tooltips en los nuevos elementos
                        const tooltipTriggerList = [].slice.call(document.querySelectorAll(
                            '[data-bs-toggle="tooltip"]'));
                        tooltipTriggerList.forEach(function(tooltipTriggerEl) {
                            if (tooltipTriggerEl._tooltip) {
                                tooltipTriggerEl._tooltip.dispose();
                            }
                            new bootstrap.Tooltip(tooltipTriggerEl);
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading results:', error);
                        showNotification('error',
                            'Error al cargar los resultados. Por favor intenta nuevamente.');
                        $('#localidadesTableBody').html(`
                <tr>
                    <td colspan="4" class="text-center py-4 text-danger">
                        <i class="ri-alert-line display-5"></i>
                        <p class="mt-2">No se pudieron cargar los resultados</p>
                        <button class="btn btn-primary btn-sm mt-2" onclick="loadResults('${search}')">
                            Reintentar
                        </button>
                    </td>
                </tr>
            `);
                    },
                    complete: function() {
                        isProcessing = false;
                    }
                });
            }

            // Búsqueda por texto
            $('#searchInput').on('input', function() {
                const searchTerm = $(this).val().trim();
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    loadResults(searchTerm);
                }, 400);
            });

            // Limpiar filtros
            $('#clearFilters').on('click', function() {
                $('#searchInput').val('');
                loadResults();
            });

            // Manejar clics en la paginación
            // Manejar clics en la paginación - método mejorado
            $(document).on('click', '.pagination a', function(e) {
                e.preventDefault();
                if (isProcessing) return;

                const url = $(this).attr('href');
                const search = $('#searchInput').val().trim();

                // Hacer la petición directamente a la URL de paginación
                $.ajax({
                    url: url,
                    method: 'GET',
                    data: {
                        search: search
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        $('#localidadesTableBody').html(`
                <tr>
                    <td colspan="4" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando resultados...</p>
                    </td>
                </tr>
            `);
                    },
                    success: function(response) {
                        $('#localidadesTableBody').html(response.html);

                        const paginationContainer = $('.pagination-container');
                        if (paginationContainer.length) {
                            paginationContainer.html(response.pagination);
                        }

                        if (response.total !== undefined) {
                            $('.results-count').html(
                                `Mostrando <span class="fw-medium">${response.from || 0}</span> a 
                     <span class="fw-medium">${response.to || 0}</span> de 
                     <span class="fw-medium">${response.total}</span> resultados`
                            );
                        }

                        // Actualizar URL en el navegador sin recargar
                        window.history.pushState({}, '', url + (search ? '&search=' +
                            encodeURIComponent(search) : ''));
                    },
                    error: function() {
                        showNotification('error', 'Error al cargar la página');
                    }
                });
            });

            // Función para mostrar notificaciones
            function showNotification(type, message) {
                let toastContainer = $('#toast-container');
                if (toastContainer.length === 0) {
                    toastContainer = $(
                        '<div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999"></div>'
                    );
                    $('body').append(toastContainer);
                }

                const icons = {
                    success: 'ri-check-line',
                    error: 'ri-close-circle-line',
                    warning: 'ri-alert-line',
                    info: 'ri-information-line'
                };

                const toast = $(`
                <div class="toast align-items-center text-white bg-${type} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body d-flex align-items-center">
                            <i class="${icons[type] || 'ri-information-line'} fs-4 me-2"></i>
                            <div>${message}</div>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `);

                toastContainer.prepend(toast);
                const bsToast = new bootstrap.Toast(toast[0], {
                    autohide: true,
                    delay: 3000
                });
                bsToast.show();

                // Remover el toast después de que se oculte
                toast.on('hidden.bs.toast', function() {
                    $(this).remove();
                });
            }
        });

        // Validación de formularios Bootstrap
        (function() {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
@endpush
