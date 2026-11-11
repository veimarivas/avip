@extends('admin.dashboard')
@section('admin')
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Gestión de Usuarios</h4>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistrar">
                        <i class="ri-user-add-line me-1"></i> Registrar Usuario
                    </button>
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
                                        placeholder="Buscar usuario...">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select id="estadoFilter" class="form-select">
                                    <option value="">Todos los estados</option>
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo</option>
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
                        <h5 class="card-title mb-0">Lista de Usuarios Registrados</h5>
                    </div>
                    <div class="card-body">
                        <div id="results-container">
                            <div class="table-responsive">
                                <table class="table table-hover table-centered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">N°</th>
                                            <th width="10%">Foto</th>
                                            <th width="25%">Información Personal</th>
                                            <th width="15%">Correo</th>
                                            <th width="15%">Rol</th>
                                            <th width="10%">Estado</th>
                                            <th width="20%" class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    @include('admin.users.partials.table-body')
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted">
                                    Mostrando <span id="showing-count">{{ $users->firstItem() ?? 0 }}</span>
                                    a <span id="to-count">{{ $users->lastItem() ?? 0 }}</span>
                                    de <span id="total-count">{{ $users->total() ?? 0 }}</span> registros
                                </div>
                                <div id="pagination-container">
                                    {{ $users->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Usuario -->
    <div class="modal fade" id="modalModificar" tabindex="-1" aria-labelledby="modalModificarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning bg-soft">
                    <h5 class="modal-title" id="modalModificarLabel">
                        <i class="ri-edit-line me-2"></i>Modificar Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateForm" class="forms-sample">
                        @csrf
                        <input type="hidden" name="id" id="userId">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name_edicion" class="form-label">Nombre completo <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name_edicion" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email_edicion" class="form-label">Correo electrónico <span
                                            class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email_edicion" name="email" required>
                                    <div id="feedback_email_edicion" class="form-text"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role_edicion" class="form-label">Rol <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="role_edicion" name="role" required>
                                        <option value="">Seleccionar rol</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="estado_edicion" class="form-label">Estado <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="estado_edicion" name="estado" required>
                                        <option value="Activo">Activo</option>
                                        <option value="Inactivo">Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="updateForm" class="btn btn-warning updateBtn"
                        id="btn-actualizar-usuario" disabled>
                        <i class="ri-save-line me-1"></i> Actualizar Usuario
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Reset Contraseña -->
    <div class="modal fade" id="modalResetPassword" tabindex="-1" aria-labelledby="modalResetPasswordLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info bg-soft">
                    <h5 class="modal-title" id="modalResetPasswordLabel">
                        <i class="ri-key-line me-2"></i>Restablecer Contraseña
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-4">
                        <i class="ri-alert-line me-2"></i>
                        La contraseña del usuario se restablecerá a
                        <strong>12345678</strong>.
                        <br>
                        <strong>Comunica esta contraseña al usuario.</strong>
                    </div>
                    <form id="resetPasswordForm" class="forms-sample">
                        @csrf
                        <input type="hidden" name="id" id="userIdReset">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="resetPasswordForm" class="btn btn-info resetBtn">
                        <i class="ri-key-line me-1"></i> Restablecer Contraseña
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Resultado Contraseña -->
    <div class="modal fade" id="modalPasswordResult" tabindex="-1" aria-labelledby="modalPasswordResultLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success bg-soft">
                    <h5 class="modal-title text-white" id="modalPasswordResultLabel">
                        <i class="ri-check-line me-2"></i>Contraseña Restablecida
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="avatar-title bg-success bg-soft text-success rounded-circle">
                                <i class="ri-key-2-line fs-2"></i>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>
                            <strong>Copia y comparte estos datos con el usuario:</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Usuario:</label>
                        <input type="text" class="form-control" id="usernameResult" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nueva Contraseña:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="passwordResult" readonly>
                            <button class="btn btn-outline-secondary" type="button" id="copyPasswordBtn">
                                <i class="ri-clipboard-line"></i>
                            </button>
                        </div>
                    </div>
                    <div class="alert alert-warning mt-3">
                        <i class="ri-alert-line me-2"></i>
                        <strong>Recomendación:</strong> solicita al usuario que cambie esta contraseña
                        desde su perfil.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Registrar Usuario -->
    <div class="modal fade" id="modalRegistrar" tabindex="-1" aria-labelledby="modalRegistrarLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary bg-soft">
                    <h5 class="modal-title text-white" id="modalRegistrarLabel">
                        <i class="ri-user-add-line me-2"></i>Registrar Nuevo Usuario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createForm" class="forms-sample" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="carnet" class="form-label">Carnet <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="carnet" name="carnet" required>
                                    <div id="feedback_carnet" class="form-text"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nombres" class="form-label">Nombres <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nombres" name="nombres" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="apellido_paterno" class="form-label">Apellido Paterno <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="apellido_paterno"
                                        name="apellido_paterno" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="apellido_materno" class="form-label">Apellido Materno <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="apellido_materno"
                                        name="apellido_materno" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fotografia" class="form-label">Fotografía</label>
                                    <input type="file" class="form-control" id="fotografia" name="fotografia"
                                        accept="image/*">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nombre de Usuario <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Correo electrónico <span
                                            class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                    <div id="feedback_email" class="form-text"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña <span
                                            class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirmar Contraseña <span
                                            class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label">Rol <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="">Seleccionar rol</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="estado" class="form-label">Estado <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="estado" name="estado" required>
                                        <option value="Activo">Activo</option>
                                        <option value="Inactivo">Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="createForm" class="btn btn-primary" id="btn-crear-usuario" disabled>
                        <i class="ri-save-line me-1"></i> Registrar Usuario
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar Usuario -->
    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger bg-soft">
                    <h5 class="modal-title text-white" id="modalEliminarLabel">
                        <i class="ri-delete-bin-line me-2"></i>Eliminar Usuario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-4">
                        <i class="ri-alert-line me-2"></i>
                        <strong>¿Estás seguro de eliminar este usuario?</strong><br>
                        Esta acción no se puede deshacer.
                    </div>
                    <form id="deleteForm" class="forms-sample">
                        @csrf
                        <input type="hidden" name="id" id="userIdDelete">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="deleteForm" class="btn btn-danger">
                        <i class="ri-delete-bin-line me-1"></i> Eliminar
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

        .bg-success.bg-soft {
            background-color: rgba(var(--bs-success-rgb), 0.1) !important;
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

        /* Estilos para la foto de usuario */
        .user-photo {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border: 2px solid #e9ecef;
        }

        /* Estilos para badges de roles */
        .badge-group {
            background-color: rgba(85, 110, 230, 0.1);
            color: #14503c;
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
            margin: 2px;
            border-radius: 0.375rem;
            display: inline-block;
        }

        /* Badge para sin rol */
        .badge-secondary {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }

        /* Estilos para badges de estado */
        .badge-status {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
        }

        /* Estilos para los botones de acción */
        .btn-group .btn {
            margin: 0 1px;
            border-radius: 0.375rem;
        }

        .btn-group {
            gap: 0.2rem;
        }

        /* Asegurar que los botones se mantengan en una línea */
        .text-center .btn-group {
            display: flex;
            justify-content: center;
            flex-wrap: nowrap;
        }

        /* Estilos para información personal */
        .text-muted small {
            font-size: 0.8rem;
        }

        .fst-italic {
            font-style: italic !important;
        }
    </style>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            let debounceTimer;

            // === FUNCIONES PARA GESTIÓN DE USUARIOS ===
            // Verificar disponibilidad de email en edición
            function verificarEmailDisponibilidad() {
                const email = $('#email_edicion').val().trim();
                const feedback = $('#feedback_email_edicion');
                const userId = $('#userId').val();

                feedback.removeClass('text-success text-danger').text('');

                if (!email) {
                    $('#btn-actualizar-usuario').prop('disabled', true);
                    return;
                }

                if (!/^\S+@\S+\.\S+$/.test(email)) {
                    feedback.addClass('text-danger').text('⚠️ Formato de correo inválido');
                    $('#btn-actualizar-usuario').prop('disabled', true);
                    return;
                }

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    $.ajax({
                        url: "{{ route('admin.users.verificar-email') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            email: email,
                            id: userId
                        },
                        success: function(res) {
                            if (res.exists) {
                                feedback.addClass('text-danger').text(
                                    '⚠️ Este correo ya está registrado');
                                $('#btn-actualizar-usuario').prop('disabled', true);
                            } else {
                                feedback.addClass('text-success').text('✅ Correo disponible');
                                verificarFormularioCompletoEdicion();
                            }
                        },
                        error: function() {
                            feedback.addClass('text-danger').text(
                                '❌ Error al verificar el correo');
                            $('#btn-actualizar-usuario').prop('disabled', true);
                        }
                    });
                }, 300);
            }

            // Verificar si el formulario de edición está completo
            function verificarFormularioCompletoEdicion() {
                const emailOk = $('#feedback_email_edicion').hasClass('text-success');
                const role = $('#role_edicion').val();
                const estado = $('#estado_edicion').val();
                const name = $('#name_edicion').val().trim();

                const formCompleto = emailOk && role && estado && name;
                $('#btn-actualizar-usuario').prop('disabled', !formCompleto);
            }

            // Obtener datos del usuario para editar
            $(document).on('click', '.editBtn', function() {
                const userId = $(this).data('id');

                $.ajax({
                    url: "{{ route('admin.users.obtener-data') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: userId
                    },
                    success: function(res) {
                        if (res.user) {
                            $('#userId').val(res.user.id);
                            $('#name_edicion').val(res.user.name);
                            $('#email_edicion').val(res.user.email);
                            $('#role_edicion').val(res.user.roles.length > 0 ? res.user.roles[0]
                                .name : '');
                            $('#estado_edicion').val(res.user.estado);

                            // Limpiar feedback y verificar correo
                            $('#feedback_email_edicion').removeClass('text-success text-danger')
                                .text('');
                            verificarEmailDisponibilidad();
                        }
                    },
                    error: function() {
                        showNotification('error', 'No se pudo cargar los datos del usuario.');
                    }
                });
            });

            // Eventos para el formulario de edición
            $('#email_edicion').on('input', verificarEmailDisponibilidad);
            $('#name_edicion, #role_edicion, #estado_edicion').on('input change',
                verificarFormularioCompletoEdicion);

            // Actualizar usuario
            $('#updateForm').submit(function(e) {
                e.preventDefault();
                const submitBtn = $('#btn-actualizar-usuario');

                const emailFeedback = $('#feedback_email_edicion');
                if (!emailFeedback.hasClass('text-success')) {
                    showNotification('warning',
                        'Por favor, verifica que el correo electrónico esté disponible.');
                    return;
                }

                submitBtn.prop('disabled', true).html(
                    '<i class="ri-loader-4-line spin me-1"></i> Actualizando...');

                $.ajax({
                    url: "{{ route('admin.users.actualizar') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.success) {
                            showNotification('success', res.msg);
                            $('#modalModificar').modal('hide');
                            loadResults($('#searchInput').val().trim(), $('#estadoFilter')
                                .val());
                        } else {
                            showNotification('error', res.msg ||
                                'Error al actualizar el usuario');
                            submitBtn.prop('disabled', false).html(
                                '<i class="ri-save-line me-1"></i> Actualizar Usuario');
                        }
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.msg ||
                            xhr.responseJSON?.errors?.email?.[0] ||
                            'Error al actualizar el usuario';
                        showNotification('error', errorMsg);
                        submitBtn.prop('disabled', false).html(
                            '<i class="ri-save-line me-1"></i> Actualizar Usuario');
                    }
                });
            });

            // Abrir modal para resetear contraseña
            $(document).on('click', '.resetBtn[data-id]', function() {
                const userId = $(this).data('id');
                $('#userIdReset').val(userId);
            });

            // Resetear contraseña
            $('#resetPasswordForm').submit(function(e) {
                e.preventDefault();
                const submitBtn = $('#modalResetPassword .resetBtn');

                submitBtn.prop('disabled', true).html(
                    '<i class="ri-loader-4-line spin me-1"></i> Restableciendo...');

                $.ajax({
                    url: "{{ route('admin.users.reset-password') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.success) {
                            $('#usernameResult').val(res.username);
                            $('#passwordResult').val(res.password);

                            const resetModalEl = document.getElementById('modalResetPassword');
                            if (resetModalEl && typeof bootstrap !== 'undefined') {
                                bootstrap.Modal.getOrCreateInstance(resetModalEl).hide();
                            }

                            const resultModalEl = document.getElementById('modalPasswordResult');
                            if (resultModalEl && typeof bootstrap !== 'undefined') {
                                bootstrap.Modal.getOrCreateInstance(resultModalEl).show();
                            }

                            showNotification('success',
                                'Contraseña restablecida a 12345678');
                        } else {
                            showNotification('error', res.msg ||
                                'Error al restablecer la contraseña');
                        }
                        submitBtn.prop('disabled', false).html(
                            '<i class="ri-key-line me-1"></i> Restablecer Contraseña');
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.msg ||
                            'Error al restablecer la contraseña';
                        showNotification('error', errorMsg);
                        submitBtn.prop('disabled', false).html(
                            '<i class="ri-key-line me-1"></i> Restablecer Contraseña');
                    }
                });
            });

            // Copiar contraseña al portapapeles
            $(document).on('click', '#copyPasswordBtn', function() {
                const password = $('#passwordResult').val();
                navigator.clipboard.writeText(password).then(() => {
                    showNotification('success', 'La contraseña se ha copiado al portapapeles.',
                        1500);
                }).catch(err => {
                    console.error('Error al copiar al portapapeles:', err);
                    showNotification('error', 'Error al copiar la contraseña');
                });
            });

            // === FUNCIONES PARA FILTROS Y BÚSQUEDA ===
            function loadResults(search = '', estado = '') {
                $.ajax({
                    url: '{{ route('admin.users.listar') }}',
                    method: 'GET',
                    data: {
                        search: search,
                        estado: estado
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#results-container .table-responsive table').find('tbody').replaceWith(
                            response.html);
                        $('#pagination-container').html(response.pagination);

                        // Actualizar contadores
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
                                    '&estado=' + encodeURIComponent(estado);
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
                    loadResults(searchTerm, $('#estadoFilter').val());
                }, 300);
            });

            // Filtro por estado
            $('#estadoFilter').on('change', function() {
                const estado = $(this).val();
                loadResults($('#searchInput').val().trim(), estado);
            });

            // Limpiar filtros
            $('#clearFilters').on('click', function() {
                $('#searchInput').val('');
                $('#estadoFilter').val('');
                loadResults();
            });

            // Manejar clics en la paginación
            $(document).on('click', '#pagination-container .pagination a', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                const search = $('#searchInput').val().trim();
                const estado = $('#estadoFilter').val();

                if (!url) return;

                $.ajax({
                    url: url,
                    method: 'GET',
                    data: {
                        search: search,
                        estado: estado
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
                                    '&estado=' + encodeURIComponent(estado);
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
            function showNotification(type, message, timer = 5000) {
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
                const bsToast = new bootstrap.Toast(toast[0], {
                    autohide: true,
                    delay: timer
                });
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

            // === INICIALIZACIÓN ===
            // Inicializar filtros
            @if (request()->has('estado'))
                $('#estadoFilter').val("{{ request('estado') }}");
            @endif

            // Verificar formulario inicialmente
            verificarFormularioCompletoEdicion();

            function verificarCarnetDisponibilidad() {
                const carnet = $('#carnet').val().trim();
                const feedback = $('#feedback_carnet');

                feedback.removeClass('text-success text-danger').text('');

                if (!carnet) {
                    $('#btn-crear-usuario').prop('disabled', true);
                    return;
                }

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    $.ajax({
                        url: "{{ route('admin.users.verificar-carnet') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            carnet: carnet
                        },
                        success: function(res) {
                            console.log('Respuesta carnet:', res);
                            if (res.exists) {
                                feedback.addClass('text-danger').text(
                                    '⚠️ Este carnet ya está registrado');
                                $('#btn-crear-usuario').prop('disabled', true);
                            } else {
                                feedback.addClass('text-success').text('✅ Carnet disponible');
                                verificarFormularioCompletoRegistro();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error en verificación carnet:', error);
                            feedback.addClass('text-danger').text(
                                '❌ Error al verificar el carnet');
                            $('#btn-crear-usuario').prop('disabled', true);
                        }
                    });
                }, 500);
            }

            function verificarEmailDisponibilidadRegistro() {
                const email = $('#email').val().trim();
                const feedback = $('#feedback_email');

                feedback.removeClass('text-success text-danger').text('');

                if (!email) {
                    $('#btn-crear-usuario').prop('disabled', true);
                    return;
                }

                if (!/^\S+@\S+\.\S+$/.test(email)) {
                    feedback.addClass('text-danger').text('⚠️ Formato de correo inválido');
                    $('#btn-crear-usuario').prop('disabled', true);
                    return;
                }

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    $.ajax({
                        url: "{{ route('admin.users.verificar-email') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            email: email
                        },
                        success: function(res) {
                            if (res.exists) {
                                feedback.addClass('text-danger').text(
                                    '⚠️ Este correo ya está registrado');
                                $('#btn-crear-usuario').prop('disabled', true);
                            } else {
                                feedback.addClass('text-success').text('✅ Correo disponible');
                                verificarFormularioCompletoRegistro();
                            }
                        },
                        error: function() {
                            feedback.addClass('text-danger').text(
                                '❌ Error al verificar el correo');
                            $('#btn-crear-usuario').prop('disabled', true);
                        }
                    });
                }, 300);
            }

            function verificarFormularioCompletoRegistro() {
                const emailOk = $('#feedback_email').hasClass('text-success');
                const carnetOk = $('#feedback_carnet').hasClass('text-success');
                const role = $('#role').val();
                const estado = $('#estado').val();
                const name = $('#name').val().trim();
                const nombres = $('#nombres').val().trim();
                const apellidoPaterno = $('#apellido_paterno').val().trim();
                const apellidoMaterno = $('#apellido_materno').val().trim();
                const password = $('#password').val();
                const passwordConfirmation = $('#password_confirmation').val();

                const formCompleto = emailOk && carnetOk && role && estado && name && nombres &&
                    apellidoPaterno && apellidoMaterno && password && passwordConfirmation;
                $('#btn-crear-usuario').prop('disabled', !formCompleto);
            }

            // Eventos para el formulario de registro
            $('#carnet').on('input', verificarCarnetDisponibilidad);
            $('#email').on('input', verificarEmailDisponibilidadRegistro);
            $('#name, #nombres, #apellido_paterno, #apellido_materno, #password, #password_confirmation, #role, #estado')
                .on('input change', verificarFormularioCompletoRegistro);

            // Registrar usuario
            // Registrar usuario
            $('#createForm').submit(function(e) {
                e.preventDefault();
                const submitBtn = $('#btn-crear-usuario');

                const emailFeedback = $('#feedback_email');
                const carnetFeedback = $('#feedback_carnet');

                if (!emailFeedback.hasClass('text-success') || !carnetFeedback.hasClass('text-success')) {
                    showNotification('warning',
                        'Por favor, verifica que el correo y carnet estén disponibles.');
                    return;
                }

                // Verificar que las contraseñas coincidan
                const password = $('#password').val();
                const passwordConfirmation = $('#password_confirmation').val();
                if (password !== passwordConfirmation) {
                    showNotification('warning', 'Las contraseñas no coinciden.');
                    return;
                }

                submitBtn.prop('disabled', true).html(
                    '<i class="ri-loader-4-line spin me-1"></i> Registrando...');

                // Crear FormData para enviar archivos
                const formData = new FormData(this);

                // Debug: mostrar datos del FormData
                for (let pair of formData.entries()) {
                    console.log(pair[0] + ': ' + pair[1]);
                }

                $.ajax({
                    url: "{{ route('admin.users.registrar') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        console.log('Respuesta del servidor:', res); // Debug
                        if (res.success) {
                            showNotification('success', res.msg);
                            $('#modalRegistrar').modal('hide');
                            $('#createForm')[0].reset();
                            $('#feedback_email, #feedback_carnet').removeClass(
                                'text-success text-danger').text('');
                            loadResults($('#searchInput').val().trim(), $('#estadoFilter')
                                .val());
                        } else {
                            // Mostrar errores de validación si existen
                            if (res.errors) {
                                let errorMsg = 'Errores de validación:<br>';
                                for (let field in res.errors) {
                                    errorMsg += `- ${res.errors[field][0]}<br>`;
                                }
                                showNotification('error', errorMsg);
                            } else {
                                showNotification('error', res.msg ||
                                    'Error al registrar el usuario');
                            }
                        }
                        submitBtn.prop('disabled', false).html(
                            '<i class="ri-save-line me-1"></i> Registrar Usuario');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en AJAX:', xhr.responseJSON); // Debug
                        let errorMsg = 'Error al registrar el usuario';

                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.errors) {
                                errorMsg = 'Errores de validación:<br>';
                                for (let field in xhr.responseJSON.errors) {
                                    errorMsg += `- ${xhr.responseJSON.errors[field][0]}<br>`;
                                }
                            } else if (xhr.responseJSON.msg) {
                                errorMsg = xhr.responseJSON.msg;
                            }
                        }

                        showNotification('error', errorMsg);
                        submitBtn.prop('disabled', false).html(
                            '<i class="ri-save-line me-1"></i> Registrar Usuario');
                    }
                });
            });

            // === FUNCIONES PARA ELIMINAR USUARIO ===
            $(document).on('click', '.deleteBtn', function() {
                const userId = $(this).data('id');
                $('#userIdDelete').val(userId);
            });

            $('#deleteForm').submit(function(e) {
                e.preventDefault();
                const submitBtn = $(this).find('.btn-danger');

                submitBtn.prop('disabled', true).html(
                    '<i class="ri-loader-4-line spin me-1"></i> Eliminando...');

                $.ajax({
                    url: "{{ route('admin.users.eliminar') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.success) {
                            showNotification('success', res.msg);
                            $('#modalEliminar').modal('hide');
                            loadResults($('#searchInput').val().trim(), $('#estadoFilter')
                                .val());
                        } else {
                            showNotification('error', res.msg ||
                                'Error al eliminar el usuario');
                        }
                        submitBtn.prop('disabled', false).html(
                            '<i class="ri-delete-bin-line me-1"></i> Eliminar');
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.msg ||
                            'Error al eliminar el usuario';
                        showNotification('error', errorMsg);
                        submitBtn.prop('disabled', false).html(
                            '<i class="ri-delete-bin-line me-1"></i> Eliminar');
                    }
                });
            });

            // Limpiar formulario de registro al cerrar modal
            $('#modalRegistrar').on('hidden.bs.modal', function() {
                $('#createForm')[0].reset();
                $('#feedback_email, #feedback_carnet').removeClass('text-success text-danger').text('');
                $('#btn-crear-usuario').prop('disabled', true);
            });
        });
    </script>
@endpush
