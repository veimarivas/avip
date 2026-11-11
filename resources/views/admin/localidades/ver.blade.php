@extends('admin.dashboard')
@section('admin')
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-light rounded-3 p-3">
                    <div>
                        <h4 class="mb-1 fw-bold text-dark">Localidad: {{ $localidad->nombre }}</h4>
                        <p class="text-muted mb-0">Gestión de productos y precios para esta localidad</p>
                    </div>
                    <div class="page-title-right">
                        <a href="{{ route('admin.localidades.listar') }}"
                            class="btn btn-secondary btn-lg waves-effect waves-light">
                            <i class="ri-arrow-left-line align-bottom me-1"></i> Volver
                        </a>
                        @if (Auth::guard('web')->user()->can('localidades.actualizar.precios'))
                            <button type="button" class="btn btn-warning btn-lg waves-effect waves-light"
                                data-bs-toggle="modal" data-bs-target="#modalActualizarPrecios">
                                <i class="ri-edit-line align-bottom me-1"></i> Actualizar Precios del Día
                            </button>
                        @endif
                        @if (Auth::guard('web')->user()->can('productos.registrar'))
                            <button type="button" class="btn btn-primary btn-lg waves-effect waves-light"
                                data-bs-toggle="modal" data-bs-target="#agregarProductoModal">
                                <i class="ri-add-line align-bottom me-1"></i> Agregar Producto
                            </button>
                        @endif
                    </div>
                </div>
                {{-- Mostrar mensajes de sesión --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-3">
                        <i class="ri-checkbox-circle-line me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-3">
                        <i class="ri-error-warning-line me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mb-3">
                        <i class="ri-error-warning-line me-2"></i>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Info Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border border-primary">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-sm rounded-circle bg-primary bg-opacity-10">
                                            <i class="ri-map-pin-2-line fs-24 text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="text-muted mb-1">Nombre de la Localidad</p>
                                        <h4 class="mb-0">{{ $localidad->nombre }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-sm rounded-circle bg-primary bg-opacity-10">
                                            <i class="ri-palette-line fs-24 text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="text-muted mb-1">Color Identificador</p>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="color-badge"
                                                style="background-color: {{ $localidad->color }}"></span>
                                            <span class="fw-medium">{{ $localidad->color }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="row">
            <div class="col-12">
                <div class="card border border-light shadow-sm">
                    <div class="card-header border-bottom-dashed d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0 fw-bold">Productos de la Localidad</h5>
                        <span class="badge bg-primary">{{ $localidad->productos->count() }} productos</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-borderless table-centered align-middle table-nowrap mb-0">
                                <thead class="table-light text-muted fw-semibold">
                                    <tr>
                                        <th class="px-3 py-3" width="5%">#</th>
                                        <th class="px-3 py-3">Nombre del Producto</th>
                                        <th class="px-3 py-3">N° Caja</th>
                                        <th class="px-3 py-3 text-center" width="20%">Precio</th>
                                        <th class="px-3 py-3 text-end" width="15%">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="productosTableBody">
                                    @forelse ($localidad->productos as $n => $producto)
                                        <tr data-producto-id="{{ $producto->id }}">
                                            <td class="text-center">{{ $n + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="avatar-xs rounded-circle bg-primary bg-opacity-10">
                                                            <i class="ri-box-3-line text-primary"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2">
                                                        <h6 class="mb-0">{{ $producto->nombre }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $producto->n_caja }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex align-items-center justify-content-center gap-2">
                                                    <span class="fw-bold text-primary">Bs.</span>
                                                    <input type="number" step="0.01" min="0"
                                                        class="form-control form-control-sm precio-input text-center"
                                                        value="{{ $producto->pivot->precio }}" style="width: 100px;"
                                                        data-localidad-id="{{ $localidad->id }}"
                                                        data-producto-id="{{ $producto->id }}">
                                                    <button class="btn btn-success btn-sm btn-guardar-precio d-none">
                                                        <i class="ri-save-line"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                @if (Auth::guard('web')->user()->can('productos.eliminar'))
                                                    <button class="btn btn-danger btn-sm btn-eliminar-producto"
                                                        data-localidad-id="{{ $localidad->id }}"
                                                        data-producto-id="{{ $producto->id }}"
                                                        data-producto-nombre="{{ $producto->nombre }}">
                                                        <i class="ri-delete-bin-line"></i> Eliminar
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="ri-inbox-line display-4"></i>
                                                    <p class="mt-2">No hay productos asociados a esta localidad</p>
                                                    <button type="button" class="btn btn-primary mt-2"
                                                        data-bs-toggle="modal" data-bs-target="#agregarProductoModal">
                                                        <i class="ri-add-line me-1"></i> Agregar Primer Producto
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Producto -->
    <div class="modal fade" id="agregarProductoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary-subtle p-3">
                    <h5 class="modal-title fw-bold text-primary">
                        <i class="ri-add-line me-2 align-bottom"></i>Agregar Producto a Localidad
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="agregarProductoForm">
                    @csrf
                    <input type="hidden" name="localidad_id" value="{{ $localidad->id }}">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="producto_id" class="form-label fw-medium">Producto <span
                                    class="text-danger">*</span></label>
                            <select class="form-select form-select-lg" id="producto_id" name="producto_id" required>
                                <option value="">Seleccionar producto...</option>
                                @foreach ($todosProductos as $producto)
                                    <option value="{{ $producto->id }}">{{ $producto->nombre }} (Caja:
                                        {{ $producto->n_caja }})</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Por favor selecciona un producto</div>
                        </div>
                        <div class="mb-3">
                            <label for="precio_nuevo" class="form-label fw-medium">Precio <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Bs.</span>
                                <input type="number" step="0.01" min="0" class="form-control form-control-lg"
                                    id="precio_nuevo" name="precio" placeholder="0.00" required>
                            </div>
                            <div class="invalid-feedback">Por favor ingresa un precio válido</div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3">
                        <button type="button" class="btn btn-soft-secondary btn-lg"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="ri-add-line me-1 align-bottom"></i> Agregar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Actualizar Precios Masivo -->
    <div class="modal fade" id="modalActualizarPrecios" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning-subtle p-3">
                    <h5 class="modal-title fw-bold text-warning">
                        <i class="ri-edit-line me-2 align-bottom"></i>Actualizar Precios Masivo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formActualizarPreciosMasivo" method="POST"
                    action="{{ route('admin.localidades.actualizar-precios-masivo') }}"
                    data-deuda="{{ $deudaGenerada ? '1' : '0' }}">
                    @csrf
                    <input type="hidden" name="localidad_id" value="{{ $localidad->id }}">
                    <div class="modal-body p-4">
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>
                            <strong>Información importante:</strong><br>
                            - Se actualizarán los precios de la localidad y del último pedido generado.<br>
                            - Se permite actualizar aunque los pedidos ya estén registrados.<br>
                            - No se podrán modificar si el último pedido ya tiene deuda generada.
                        </div>

                        @if ($deudaGenerada)
                            <div class="alert alert-danger">
                                <i class="ri-lock-line me-2"></i>
                                <strong>El último pedido de la localidad ya tiene deuda generada.</strong><br>
                                Código: <strong>{{ $ultimoPedido->codigo }}</strong> ·
                                Fecha: <strong>{{ $ultimoPedido->created_at->format('d/m/Y') }}</strong><br>
                                No se pueden modificar los precios.
                            </div>
                        @elseif ($ultimoPedido)
                            <div class="alert alert-success">
                                <i class="ri-checkbox-circle-line me-2"></i>
                                Último pedido: código <strong>{{ $ultimoPedido->codigo }}</strong> ·
                                fecha <strong>{{ $ultimoPedido->created_at->format('d/m/Y') }}</strong>.
                                Puedes actualizar los precios.
                            </div>
                        @endif

                        <div class="mb-3 p-3 bg-light rounded">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Fecha de actualización:</strong><br>
                                    <span class="text-primary">{{ \Carbon\Carbon::now()->format('d/m/Y') }}</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Localidad:</strong><br>
                                    <span class="text-primary">{{ $localidad->nombre }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-centered align-middle table-nowrap mb-0">
                                <thead class="table-light text-muted fw-semibold">
                                    <tr>
                                        <th width="40%">Producto</th>
                                        <th width="25%">Precio Actual</th>
                                        <th width="35%">Nuevo Precio</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyPreciosMasivo">
                                    @foreach ($localidad->productos as $producto)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="avatar-xs rounded-circle bg-primary bg-opacity-10">
                                                            <i class="ri-box-3-line text-primary"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2">
                                                        <h6 class="mb-0">{{ $producto->nombre }}</h6>
                                                        <small class="text-muted">Caja: {{ $producto->n_caja }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-primary">Bs.
                                                    {{ number_format($producto->pivot->precio, 2) }}</span>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">Bs.</span>
                                                    <input type="number" step="0.01" min="0"
                                                        class="form-control nuevo-precio-input text-center"
                                                        name="precios[{{ $producto->id }}][precio]"
                                                        value="{{ $producto->pivot->precio }}"
                                                        data-producto-id="{{ $producto->id }}" placeholder="0.00"
                                                        @disabled($deudaGenerada)>
                                                    <input type="hidden"
                                                        name="precios[{{ $producto->id }}][producto_id]"
                                                        value="{{ $producto->id }}">
                                                </div>
                                                <small class="text-muted d-block mt-1">Precio nuevo</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-center">
                                            <small class="text-muted">
                                                <i class="ri-alert-line me-1"></i>
                                                Esta acción actualizará la localidad y el último pedido generado.
                                            </small>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3">
                        <button type="button" class="btn btn-soft-secondary btn-lg" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1 align-bottom"></i> Cancelar
                        </button>
                        @if ($deudaGenerada)
                            <button type="button" class="btn btn-secondary btn-lg" disabled>
                                <i class="ri-lock-line me-1 align-bottom"></i> Precios bloqueados por deuda generada
                            </button>
                        @else
                            <button type="submit" class="btn btn-warning btn-lg" id="btnActualizarPrecios">
                                <i class="ri-save-line me-1 align-bottom"></i> Actualizar Precios del Día
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Contenedor de notificaciones -->
    <div id="avipToastContainer" class="toast-container position-fixed top-0 end-0 p-3"
        style="z-index: 1100;" aria-live="polite" aria-atomic="true"></div>
@endsection

@push('styles')
    <style>
        .precio-input {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .precio-input:focus {
            border-color: #556ee6;
            box-shadow: 0 0 0 0.2rem rgba(85, 110, 230, 0.25);
        }

        .precio-input.modified {
            border-color: #f1b44c;
            background-color: #fffbf0;
        }

        .btn-guardar-precio {
            transition: all 0.2s ease;
        }

        .color-badge {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 4px;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .nuevo-precio-input {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .nuevo-precio-input:focus {
            border-color: #f1b44c;
            box-shadow: 0 0 0 0.2rem rgba(241, 180, 76, 0.25);
        }

        .nuevo-precio-input.is-invalid {
            border-color: #f46a6a;
            background-color: #fff5f5;
        }

        .nuevo-precio-input.modified {
            border-color: #198754;
            background-color: #f8fff9;
        }

        .bg-warning-subtle {
            background-color: rgba(241, 180, 76, 0.1) !important;
        }

        .alert-info {
            border-left: 4px solid #0dcaf0;
        }

        .table-centered th {
            text-align: center;
            vertical-align: middle;
        }

        .table-centered td {
            vertical-align: middle;
        }

        .spin {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 0.75s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .alert {
            border-radius: 8px;
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .alert-success {
            background-color: #d1f2eb;
            color: #0d9276;
            border-left: 4px solid #0d9276;
        }

        .alert-error,
        .alert-danger {
            background-color: #fde8e8;
            color: #e53e3e;
            border-left: 4px solid #e53e3e;
        }

        .alert-warning {
            background-color: #fff8e1;
            color: #d97706;
            border-left: 4px solid #d97706;
        }

        /* Notificaciones tipo toast */
        .toast-container .avip-toast {
            min-width: 340px;
            max-width: 420px;
            border: 0;
            border-left: 5px solid #2b8a5a;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 22px 45px -20px rgba(15, 36, 28, 0.55);
            background: #ffffff;
        }

        .avip-toast .toast-header {
            padding: 0.75rem 0.9rem;
            background: #ffffff;
            border-bottom: 1px solid #eef2ee;
            color: var(--avip-ink, #16352a);
        }

        .avip-toast .toast-header strong {
            font-size: 0.88rem;
            font-weight: 800;
        }

        .avip-toast .toast-body {
            padding: 0.8rem 0.95rem;
            color: var(--avip-body, #4d6155);
            font-size: 0.85rem;
            line-height: 1.45;
        }

        .avip-toast__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            margin-right: 0.55rem;
            border-radius: 9px;
            background: #e7f5ec;
            color: #2b8a5a;
            font-size: 1rem;
        }

        .avip-toast--success {
            border-left-color: #2b8a5a;
        }

        .avip-toast--success .avip-toast__icon {
            background: #e7f5ec;
            color: #2b8a5a;
        }

        .avip-toast--error {
            border-left-color: #d64545;
        }

        .avip-toast--error .avip-toast__icon {
            background: #fdecec;
            color: #d64545;
        }

        .avip-toast--warning {
            border-left-color: #d98b1c;
        }

        .avip-toast--warning .avip-toast__icon {
            background: #fdf3e2;
            color: #d98b1c;
        }

        .avip-toast--info {
            border-left-color: #3b7fb5;
        }

        .avip-toast--info .avip-toast__icon {
            background: #e9f2fa;
            color: #3b7fb5;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            let isProcessing = false;

            const toastIcons = {
                success: 'ri-checkbox-circle-fill',
                error: 'ri-error-warning-fill',
                warning: 'ri-alert-fill',
                info: 'ri-information-fill'
            };

            const toastTitles = {
                success: 'Precios actualizados',
                error: 'No se pudo completar',
                warning: 'Atención',
                info: 'Información'
            };

            // Muestra una notificación tipo toast
            function showToast(type, message, title) {
                const container = document.getElementById('avipToastContainer');
                if (!container) {
                    return;
                }

                const tipo = toastIcons[type] ? type : 'info';
                const toastEl = document.createElement('div');
                toastEl.className = 'toast avip-toast avip-toast--' + tipo;
                toastEl.setAttribute('role', 'alert');
                toastEl.setAttribute('aria-live', 'assertive');
                toastEl.setAttribute('aria-atomic', 'true');

                const header = document.createElement('div');
                header.className = 'toast-header';
                header.innerHTML =
                    '<span class="avip-toast__icon"><i class="' + toastIcons[tipo] + '"></i></span>' +
                    '<strong class="me-auto">' + (title || toastTitles[tipo]) + '</strong>' +
                    '<small class="text-muted">Ahora</small>' +
                    '<button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Cerrar"></button>';

                const body = document.createElement('div');
                body.className = 'toast-body';
                body.textContent = message || '';

                toastEl.appendChild(header);
                toastEl.appendChild(body);
                container.appendChild(toastEl);

                // Mantener como máximo 3 toasts visibles
                const toasts = container.querySelectorAll('.toast');
                if (toasts.length > 3) {
                    toasts[0].remove();
                }

                if (typeof bootstrap === 'undefined' || !bootstrap.Toast) {
                    toastEl.classList.add('show');
                    setTimeout(() => toastEl.remove(), 5000);
                    return;
                }

                const toast = bootstrap.Toast.getOrCreateInstance(toastEl, {
                    autohide: true,
                    delay: 4500
                });
                toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
                toast.show();
            }

            // Compatibilidad con el código existente
            function showNotification(type, message) {
                showToast(type, message);
            }

            // Mostrar el toast pendiente después de recargar la página
            try {
                const pendingToast = sessionStorage.getItem('avipToast');
                if (pendingToast) {
                    sessionStorage.removeItem('avipToast');
                    const data = JSON.parse(pendingToast);
                    setTimeout(() => {
                        showToast(data.type || 'success', data.message || '', data.title);
                    }, 350);
                }
            } catch (e) {
                console.warn('No se pudo mostrar la notificación pendiente', e);
            }

            $(document).on('submit', '#formActualizarPreciosMasivo', function(e) {
                e.preventDefault();
                e.stopPropagation();

                console.log('Formulario de actualización masiva interceptado');

                if (isProcessing) {
                    console.log('Procesamiento en curso, ignorando solicitud');
                    return false;
                }

                if ($(this).data('deuda') == 1) {
                    showToast('error',
                        'El último pedido de la localidad ya tiene deuda generada. No se pueden modificar los precios.'
                    );
                    return false;
                }

                const form = $(this);
                const submitBtn = $('#btnActualizarPrecios');
                const originalText = submitBtn.html();

                // Validar precios
                let preciosValidos = true;
                let preciosVacios = [];

                $('.nuevo-precio-input').each(function() {
                    const precio = $(this).val();
                    const productoNombre = $(this).closest('tr').find('h6').text();

                    if (!precio || precio === '' || precio < 0) {
                        $(this).addClass('is-invalid');
                        preciosValidos = false;
                        preciosVacios.push(productoNombre);
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                if (!preciosValidos) {
                    const productosList = preciosVacios.slice(0, 3).join(', ');
                    const mensajeExtra = preciosVacios.length > 3 ? ` y ${preciosVacios.length - 3} más` :
                        '';
                    showNotification('error',
                        `Por favor ingrese precios válidos para: ${productosList}${mensajeExtra}`);
                    return false;
                }

                isProcessing = true;
                submitBtn.prop('disabled', true).html('<span class="spin me-2"></span>Actualizando...');

                // Preparar datos del formulario
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('localidad_id', '{{ $localidad->id }}');

                // Agregar cada precio al FormData
                $('.nuevo-precio-input').each(function() {
                    const productoId = $(this).data('producto-id');
                    const precio = $(this).val();

                    formData.append(`precios[${productoId}][producto_id]`, productoId);
                    formData.append(`precios[${productoId}][precio]`, precio);
                });

                console.log('Enviando datos...');

                $.ajax({
                    url: form.attr('action'),
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        console.log('Respuesta recibida:', res);

                        if (res.success) {
                            // Guardar el mensaje para mostrarlo como toast tras la recarga
                            try {
                                sessionStorage.setItem('avipToast', JSON.stringify({
                                    type: 'success',
                                    title: 'Precios actualizados',
                                    message: res.msg
                                }));
                            } catch (e) {}

                            // Cerrar el modal con la API de Bootstrap 5
                            const modalEl = document.getElementById('modalActualizarPrecios');
                            if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                            }

                            // Recargar la página para mostrar los datos actualizados
                            setTimeout(() => window.location.reload(), 320);
                        } else {
                            showToast('error', res.msg || 'Error desconocido');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en AJAX:', status, error);

                        let errorMsg = 'Error al actualizar los precios';

                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.msg) {
                                errorMsg = response.msg;
                            } else if (response.message) {
                                errorMsg = response.message;
                            } else if (response.errors) {
                                // Si hay errores de validación
                                const firstError = Object.values(response.errors)[0];
                                errorMsg = Array.isArray(firstError) ? firstError[0] :
                                    firstError;
                            }
                        } catch (e) {
                            if (xhr.responseText && xhr.responseText.includes('html')) {
                                errorMsg = 'Error del servidor. Por favor recarga la página.';
                            }
                        }

                        showNotification('error', errorMsg);
                    },
                    complete: function() {
                        isProcessing = false;
                        submitBtn.prop('disabled', false).html(
                            '<i class="ri-save-line me-1 align-bottom"></i> Actualizar Precios del Día'
                        );
                    }
                });

                return false;
            });

            // Reset modal cuando se cierra
            $('#modalActualizarPrecios').on('hidden.bs.modal', function() {
                // Restaurar valores originales al cerrar sin guardar
                $('.nuevo-precio-input').each(function() {
                    const originalValue = $(this).data('original-value');
                    if (originalValue) {
                        $(this).val(originalValue);
                    }
                });
                $('#formActualizarPreciosMasivo')[0].reset();
            });

            // Al abrir el modal, guardar valores originales
            $('#modalActualizarPrecios').on('show.bs.modal', function() {
                $('.nuevo-precio-input').each(function() {
                    $(this).data('original-value', $(this).val());
                });
            });

            // Validación en tiempo real para los precios
            $(document).on('input', '.nuevo-precio-input', function() {
                const precio = $(this).val();
                if (precio < 0) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');

                    // Resaltar si el precio cambió
                    const precioOriginal = $(this).data('original-value');
                    if (precio !== precioOriginal) {
                        $(this).addClass('modified');
                    } else {
                        $(this).removeClass('modified');
                    }
                }
            });

            // Prevenir valores negativos y caracteres no numéricos
            $(document).on('keydown', '.nuevo-precio-input', function(e) {
                // Permitir: backspace, delete, tab, escape, enter, punto
                if ([46, 8, 9, 27, 13, 110, 190].includes(e.keyCode) ||
                    // Permitir: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                    (e.keyCode === 65 && e.ctrlKey === true) ||
                    (e.keyCode === 67 && e.ctrlKey === true) ||
                    (e.keyCode === 86 && e.ctrlKey === true) ||
                    (e.keyCode === 88 && e.ctrlKey === true) ||
                    // Permitir: home, end, left, right
                    (e.keyCode >= 35 && e.keyCode <= 39)) {
                    return;
                }

                // Evitar que se presione cualquier tecla que no sea número
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode >
                        105)) {
                    e.preventDefault();
                }
            });

            // Formatear precios al perder el foco
            $(document).on('blur', '.nuevo-precio-input', function() {
                let value = $(this).val();
                if (value && !isNaN(value)) {
                    value = parseFloat(value).toFixed(2);
                    $(this).val(value);
                }
            });

            // ===== CÓDIGO EXISTENTE PARA PRECIOS INDIVIDUALES =====
            // Actualizar precio del producto individual
            $(document).on('input', '.precio-input', function() {
                $(this).addClass('modified');
                $(this).siblings('.btn-guardar-precio').removeClass('d-none');
            });

            $(document).on('click', '.btn-guardar-precio', function() {
                const input = $(this).siblings('.precio-input');
                actualizarPrecio(input);
            });

            $(document).on('keypress', '.precio-input', function(e) {
                if (e.which === 13) { // Enter key
                    actualizarPrecio($(this));
                }
            });

            function actualizarPrecio(input) {
                if (isProcessing) return;

                const precio = input.val();
                const localidadId = input.data('localidad-id');
                const productoId = input.data('producto-id');
                const guardarBtn = input.siblings('.btn-guardar-precio');

                if (!precio || precio < 0) {
                    showNotification('error', 'Por favor ingresa un precio válido');
                    return;
                }

                isProcessing = true;
                guardarBtn.prop('disabled', true).html('<span class="spin"></span>');

                $.ajax({
                    url: "{{ route('admin.localidades.actualizar-precio') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        localidad_id: localidadId,
                        producto_id: productoId,
                        precio: precio
                    },
                    success: function(res) {
                        if (res.success) {
                            showNotification('success', res.msg);
                            input.removeClass('modified');
                            guardarBtn.addClass('d-none');
                            input.val(res.precio);
                        } else {
                            showNotification('error', res.msg);
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Error al actualizar el precio';
                        if (xhr.responseJSON?.msg) {
                            errorMsg = xhr.responseJSON.msg;
                        } else if (xhr.responseJSON?.errors?.precio) {
                            errorMsg = xhr.responseJSON.errors.precio[0];
                        }
                        showNotification('error', errorMsg);
                    },
                    complete: function() {
                        isProcessing = false;
                        guardarBtn.prop('disabled', false).html('<i class="ri-save-line"></i>');
                    }
                });
            }

            // Agregar producto a la localidad
            $('#agregarProductoForm').submit(function(e) {
                e.preventDefault();
                if (isProcessing) return;

                const form = $(this)[0];
                if (form.checkValidity() === false) {
                    form.classList.add('was-validated');
                    return;
                }

                isProcessing = true;
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();

                submitBtn.prop('disabled', true).html('<span class="spin me-2"></span>Agregando...');

                $.ajax({
                    url: "{{ route('admin.localidades.agregar-producto') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.success) {
                            showNotification('success', res.msg);
                            const agregarModal = document.getElementById('agregarProductoModal');
                            if (agregarModal && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                                bootstrap.Modal.getOrCreateInstance(agregarModal).hide();
                            }
                            agregarFilaProducto(res.producto);
                            actualizarContadorProductos();
                        } else {
                            showNotification('error', res.msg);
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Error al agregar el producto';
                        if (xhr.responseJSON?.msg) {
                            errorMsg = xhr.responseJSON.msg;
                        }
                        showNotification('error', errorMsg);
                    },
                    complete: function() {
                        isProcessing = false;
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Eliminar producto de la localidad
            $(document).on('click', '.btn-eliminar-producto', function() {
                if (isProcessing) return;

                const btn = $(this);
                const localidadId = btn.data('localidad-id');
                const productoId = btn.data('producto-id');
                const productoNombre = btn.data('producto-nombre');

                if (!confirm(
                        `¿Estás seguro de eliminar el producto "${productoNombre}" de esta localidad?`)) {
                    return;
                }

                isProcessing = true;
                btn.prop('disabled', true).html('<span class="spin me-2"></span>Eliminando...');

                $.ajax({
                    url: "{{ route('admin.localidades.eliminar-producto') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        localidad_id: localidadId,
                        producto_id: productoId
                    },
                    success: function(res) {
                        if (res.success) {
                            showNotification('success', res.msg);
                            btn.closest('tr').remove();
                            actualizarContadorProductos();
                            verificarTablaVacia();
                        } else {
                            showNotification('error', res.msg);
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Error al eliminar el producto';
                        if (xhr.responseJSON?.msg) {
                            errorMsg = xhr.responseJSON.msg;
                        }
                        showNotification('error', errorMsg);
                    },
                    complete: function() {
                        isProcessing = false;
                        btn.prop('disabled', false).html(
                            '<i class="ri-delete-bin-line"></i> Eliminar');
                    }
                });
            });

            function agregarFilaProducto(producto) {
                const tbody = $('#productosTableBody');
                const numeroFila = tbody.find('tr').length + 1;

                // Si la tabla está vacía, removemos el mensaje de vacío
                if (tbody.find('tr').length === 1 && tbody.find('td[colspan]').length > 0) {
                    tbody.empty();
                }

                const nuevaFila = `
                <tr data-producto-id="${producto.id}">
                    <td class="text-center">${numeroFila}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-xs rounded-circle bg-primary bg-opacity-10">
                                    <i class="ri-box-3-line text-primary"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-0">${producto.nombre}</h6>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-secondary">${producto.n_caja}</span>
                    </td>
                    <td class="text-center">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <span class="fw-bold text-primary">Bs.</span>
                            <input type="number" 
                                   step="0.01" 
                                   min="0" 
                                   class="form-control form-control-sm precio-input text-center" 
                                   value="${producto.pivot.precio}" 
                                   style="width: 100px;"
                                   data-localidad-id="{{ $localidad->id }}"
                                   data-producto-id="${producto.id}">
                            <button class="btn btn-success btn-sm btn-guardar-precio d-none">
                                <i class="ri-save-line"></i>
                            </button>
                        </div>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-danger btn-sm btn-eliminar-producto"
                                data-localidad-id="{{ $localidad->id }}"
                                data-producto-id="${producto.id}"
                                data-producto-nombre="${producto.nombre}">
                            <i class="ri-delete-bin-line"></i> Eliminar
                        </button>
                    </td>
                </tr>
            `;

                tbody.append(nuevaFila);
            }

            function actualizarContadorProductos() {
                const count = $('#productosTableBody').find('tr').length;
                $('.badge.bg-primary').text(count + ' productos');
            }

            function verificarTablaVacia() {
                const tbody = $('#productosTableBody');
                if (tbody.find('tr').length === 0) {
                    tbody.html(`
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <div class="text-muted">
                                <i class="ri-inbox-line display-4"></i>
                                <p class="mt-2">No hay productos asociados a esta localidad</p>
                                <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal"
                                        data-bs-target="#agregarProductoModal">
                                    <i class="ri-add-line me-1"></i> Agregar Primer Producto
                                </button>
                            </div>
                        </td>
                    </tr>
                `);
                }
            }

            // Reset modal cuando se cierra
            $('#agregarProductoModal').on('hidden.bs.modal', function() {
                $('#agregarProductoForm')[0].reset();
                $('#agregarProductoForm').removeClass('was-validated');
            });
        });
    </script>
@endpush
