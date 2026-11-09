@extends('admin.dashboard')
@section('admin')
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-light rounded-3 p-3">
                    <div>
                        <h4 class="mb-1 fw-bold text-dark">Cliente: {{ $cliente->nombres }} {{ $cliente->apellidos }}</h4>
                        <p class="text-muted mb-0">CI: {{ $cliente->ci }} | Deuda total: <span
                                class="fw-bold text-danger">Bs. {{ number_format($deudaTotal, 2) }}</span></p>
                    </div>
                    <div class="page-title-right">
                        <a href="{{ route('admin.clientes.listar') }}"
                            class="btn btn-secondary btn-lg waves-effect waves-light">
                            <i class="ri-arrow-left-line align-bottom me-1"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Cliente -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border border-primary">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-sm rounded-circle bg-primary bg-opacity-10">
                                            <i class="ri-user-line fs-24 text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="text-muted mb-1">CI/NIT</p>
                                        <h4 class="mb-0">{{ $cliente->ci }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-sm rounded-circle bg-primary bg-opacity-10">
                                            <i class="ri-map-pin-line fs-24 text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="text-muted mb-1">Localidad</p>
                                        <h4 class="mb-0">{{ $cliente->localidad->nombre ?? 'No asignada' }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-sm rounded-circle bg-primary bg-opacity-10">
                                            <i class="ri-money-dollar-circle-line fs-24 text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="text-muted mb-1">Deuda Total</p>
                                        <h4 class="mb-0 text-danger">Bs. {{ number_format($deudaTotal, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-sm rounded-circle bg-primary bg-opacity-10">
                                            <i class="ri-list-check-2 fs-24 text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="text-muted mb-1">Orden</p>
                                        <h4 class="mb-0">{{ $cliente->orden }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs para Pedidos y Recibos -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-transparent border-bottom">
                        <ul class="nav nav-tabs-custom card-header-tabs border-bottom-0 nav-justified" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" data-bs-toggle="tab" href="#pedidos-pendientes" role="tab">
                                    <i class="ri-alarm-warning-line me-1 align-bottom"></i> Pedidos Pendientes
                                    <span class="badge bg-danger ms-1">{{ $pedidosPendientes->total() }}</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-bs-toggle="tab" href="#pedidos-pagados" role="tab">
                                    <i class="ri-check-double-line me-1 align-bottom"></i> Pedidos Pagados
                                    <span class="badge bg-success ms-1">{{ $pedidosPagados->total() }}</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-bs-toggle="tab" href="#historial-recibos" role="tab">
                                    <i class="ri-receipt-line me-1 align-bottom"></i> Historial de Recibos
                                    <span class="badge bg-info ms-1">{{ $pagos->total() }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="pedidos-pendientes" role="tabpanel">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-warning">{{ $pedidosPendientes->total() }} pendientes</span>
                                    <button type="button" class="btn btn-success btn-sm ms-2" id="btnPagoMultiple">
                                        <i class="ri-money-dollar-circle-line me-1"></i> Pago Múltiple
                                    </button>
                                </div>
                                <div id="tabla-pendientes">
                                    @include('admin.clientes.partials.pedidos-pendientes-table', [
                                        'pedidosPendientes' => $pedidosPendientes,
                                    ])
                                </div>
                            </div>

                            <div class="tab-pane fade" id="pedidos-pagados" role="tabpanel">
                                <div id="tabla-pagados">
                                    @include('admin.clientes.partials.pedidos-pagados-table', [
                                        'pedidosPagados' => $pedidosPagados,
                                    ])
                                </div>
                            </div>

                            <div class="tab-pane fade" id="historial-recibos" role="tabpanel">
                                <div id="tabla-recibos">
                                    @include('admin.clientes.partials.recibos-table', [
                                        'pagos' => $pagos,
                                    ])
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal para pago con descuento y tipo de pago -->
    <!-- Modal para pago con descuento y tipo de pago - VERSIÓN MEJORADA -->
    <div class="modal fade" id="modalPagoConDescuento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- Añadido modal-lg para más ancho -->
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary-subtle p-3">
                    <h5 class="modal-title fw-bold text-primary">
                        <i class="ri-calculator-line me-2 align-bottom"></i>Registrar Pago - Calculadora
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formPagoConDescuento">
                    @csrf
                    <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
                    <input type="hidden" name="pedido_id" id="pedidoIdDescuento">
                    <div class="modal-body p-4">
                        <div class="row">
                            <!-- Columna izquierda: Información del pedido -->
                            <div class="col-md-6 border-end">
                                <div class="mb-4">
                                    <h6 class="text-primary fw-bold mb-3">
                                        <i class="ri-information-line me-2"></i>Información del Pedido
                                    </h6>
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <label class="form-label small text-muted">Pedido</label>
                                            <p class="form-control-plaintext fw-bold fs-5"
                                                id="pedidoDescripcionDescuento"></p>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small text-muted">Subtotal</label>
                                            <p class="form-control-plaintext fw-bold text-primary" id="subtotalPedido">Bs.
                                                0.00</p>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small text-muted">Pagado Anterior</label>
                                            <p class="form-control-plaintext fw-bold text-success" id="pagadoAnterior">Bs.
                                                0.00</p>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small text-muted">Descuento Anterior</label>
                                            <p class="form-control-plaintext fw-bold text-warning" id="descuentoAnterior">
                                                Bs. 0.00</p>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small text-muted">Pendiente Actual</label>
                                            <p class="form-control-plaintext fw-bold text-danger" id="pendienteActual">Bs.
                                                0.00</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Panel de cálculos en tiempo real -->
                                <div class="card border-info">
                                    <div class="card-header bg-info-subtle py-2">
                                        <h6 class="mb-0 text-info">
                                            <i class="ri-calculator-line me-2"></i>Cálculos Automáticos
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label small text-muted">Monto a Pagar</label>
                                                <p class="form-control-plaintext fw-bold text-primary"
                                                    id="montoCalculado">Bs. 0.00</p>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small text-muted">Descuento Nuevo</label>
                                                <p class="form-control-plaintext fw-bold text-warning"
                                                    id="descuentoCalculado">Bs. 0.00</p>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small text-muted">Total Reducción</label>
                                                <p class="form-control-plaintext fw-bold text-success"
                                                    id="totalReduccion">Bs. 0.00</p>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small text-muted">Deuda Restante</label>
                                                <p class="form-control-plaintext fw-bold text-danger" id="deudaRestante">
                                                    Bs. 0.00</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Columna derecha: Formulario de pago -->
                            <div class="col-md-6">
                                <h6 class="text-primary fw-bold mb-3">
                                    <i class="ri-money-dollar-circle-line me-2"></i>Datos del Pago
                                </h6>

                                <div class="mb-3">
                                    <label for="montoPagoDescuento" class="form-label fw-medium">
                                        Monto a Pagar (Bs.) <span class="text-danger">*</span>
                                        <small class="text-muted ms-1">(Máximo: <span id="maxPago"
                                                class="fw-bold text-primary">Bs. 0.00</span>)</small>
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-primary-subtle text-primary fw-bold">Bs.</span>
                                        <input type="number" step="0.01" min="0.01" class="form-control"
                                            id="montoPagoDescuento" name="monto_pago" required placeholder="Ej: 100.00">
                                        <button type="button" class="btn btn-outline-primary" id="btnPagarTodo">
                                            <i class="ri-check-double-line"></i> Todo
                                        </button>
                                    </div>
                                    <div class="form-text">Ingrese el monto que el cliente está pagando</div>
                                </div>

                                <div class="mb-3">
                                    <label for="descuentoPago" class="form-label fw-medium">
                                        Descuento (Bs.)
                                        <small class="text-muted ms-1">(Máximo descuento: <span id="maxDescuento"
                                                class="fw-bold text-warning">Bs. 0.00</span>)</small>
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-warning-subtle text-warning fw-bold">Bs.</span>
                                        <input type="number" step="0.01" min="0" class="form-control"
                                            id="descuentoPago" name="descuento_bs" value="0"
                                            placeholder="Ej: 10.00">
                                        <button type="button" class="btn btn-outline-warning" id="btnSinDescuento">
                                            <i class="ri-close-line"></i> Cero
                                        </button>
                                    </div>
                                    <div class="form-text">Si aplica descuento, ingrese el monto</div>
                                </div>

                                <div class="mb-4">
                                    <label for="tipoPago" class="form-label fw-medium">
                                        Tipo de Pago <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select form-select-lg" id="tipoPago" name="tipo_pago" required>
                                        <option value="">Seleccionar método...</option>
                                        <option value="efectivo">💵 Efectivo</option>
                                        <option value="qr">📱 QR / Transferencia</option>
                                        <option value="parcial">💰 Pago Parcial (Efectivo + QR)</option>
                                    </select>
                                </div>

                                <!-- Campos para pago parcial (se muestran solo cuando se selecciona "parcial") -->
                                <div id="camposParcial" style="display: none;" class="border rounded p-3 bg-light">
                                    <h6 class="text-info fw-bold mb-3">
                                        <i class="ri-money-cny-circle-line me-2"></i>Distribución del Pago
                                    </h6>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="montoEfectivo" class="form-label fw-medium">Monto Efectivo
                                                (Bs.)</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Bs.</span>
                                                <input type="number" step="0.01" min="0" class="form-control"
                                                    id="montoEfectivo" name="monto_efectivo" value="0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="montoQr" class="form-label fw-medium">Monto QR (Bs.)</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Bs.</span>
                                                <input type="number" step="0.01" min="0" class="form-control"
                                                    id="montoQr" name="monto_qr" value="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="alert alert-info py-2 mb-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="ri-information-line me-2"></i>
                                                <strong>Total parcial:</strong>
                                                <span id="totalParcial" class="fw-bold ms-2">Bs. 0.00</span>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-info"
                                                id="btnDistribuir">
                                                <i class="ri-share-line me-1"></i> Distribuir
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mensaje de estado -->
                                <div class="alert alert-success mt-3" id="mensajeExito" style="display: none;">
                                    <i class="ri-checkbox-circle-line me-2"></i>
                                    <span id="textoMensaje">El pedido quedará completamente pagado</span>
                                </div>

                                <div class="alert alert-warning mt-3" id="mensajeAdvertencia" style="display: none;">
                                    <i class="ri-alert-line me-2"></i>
                                    <span id="textoAdvertencia">El pedido tendrá deuda pendiente</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3">
                        <div class="w-100 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small">Pendiente final:</span>
                                <span id="pendienteFinal" class="fw-bold text-danger ms-2 fs-5">Bs. 0.00</span>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-secondary btn-lg me-2"
                                    data-bs-dismiss="modal">
                                    <i class="ri-close-line me-1"></i> Cancelar
                                </button>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="ri-check-double-line me-1"></i> Confirmar Pago
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para pagar todo -->
    <div class="modal fade" id="modalPagarCompleto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success-subtle p-3">
                    <h5 class="modal-title fw-bold text-success">
                        <i class="ri-check-double-line me-2 align-bottom"></i>Pagar Pedido Completo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formPagarCompleto">
                    @csrf
                    <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
                    <input type="hidden" name="pedido_id" id="pedidoIdCompleto">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Pedido</label>
                            <p class="form-control-plaintext fw-bold" id="pedidoDescripcionCompleto"></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">Monto a Pagar</label>
                            <p class="form-control-plaintext fw-bold text-success" id="montoPendienteCompleto">Bs. 0.00
                            </p>
                        </div>

                        <div class="mb-3">
                            <label for="tipoPagoCompleto" class="form-label fw-medium">Tipo de Pago <span
                                    class="text-danger">*</span></label>
                            <select class="form-select form-select-lg" id="tipoPagoCompleto" name="tipo_pago" required>
                                <option value="">Seleccionar...</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="qr">Qr</option>
                                <option value="parcial">Parcial</option>
                            </select>
                        </div>

                        <!-- Campos para pago parcial (se muestran solo cuando se selecciona "parcial") -->
                        <div id="camposParcialCompleto" style="display: none;">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="montoEfectivoCompleto" class="form-label fw-medium">Monto Efectivo
                                        (Bs.)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Bs.</span>
                                        <input type="number" step="0.01" min="0" class="form-control"
                                            id="montoEfectivoCompleto" name="monto_efectivo" value="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="montoQrCompleto" class="form-label fw-medium">Monto QR (Bs.)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Bs.</span>
                                        <input type="number" step="0.01" min="0" class="form-control"
                                            id="montoQrCompleto" name="monto_qr" value="0">
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-info">
                                <i class="ri-information-line me-2"></i>
                                Total parcial: <span id="totalParcialCompleto" class="fw-bold">Bs. 0.00</span>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="ri-alert-line me-2"></i>
                            ¿Está seguro de marcar este pedido como completamente pagado?
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3">
                        <button type="button" class="btn btn-soft-secondary btn-lg"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="ri-check-double-line me-1 align-bottom"></i> Confirmar Pago Completo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Detalles de Pago -->
    <div class="modal fade" id="modalDetalles" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary-subtle">
                    <h5 class="modal-title text-primary">Detalles del Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detallesPagoContent">
                    <!-- Los detalles se cargarán aquí via AJAX -->
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando detalles del pago...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Pago Múltiple -->
    <div class="modal fade" id="modalPagoMultiple" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl"> <!-- modal-xl para más espacio -->
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success-subtle p-3">
                    <h5 class="modal-title fw-bold text-success">
                        <i class="ri-money-dollar-circle-line me-2 align-bottom"></i>Pago Múltiple - Distribución
                        Automática
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formPagoMultiple">
                    @csrf
                    <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
                    <div class="modal-body p-4">
                        <div class="row">
                            <!-- Columna izquierda: Selección de pedidos -->
                            <div class="col-md-5 border-end">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="ri-list-check-2 me-2"></i>Seleccionar Pedidos Pendientes
                                </h6>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAllPedidos">
                                        <label class="form-check-label fw-medium" for="selectAllPedidos">
                                            Seleccionar todos los pedidos
                                        </label>
                                    </div>
                                </div>

                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <!-- En la sección de la lista de pedidos pendientes dentro del modal -->
                                    <table class="table table-sm table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>Fecha</th>
                                                <th>Pedido</th>
                                                <th>Tipo</th> <!-- Columna nueva -->
                                                <th class="text-end">Pendiente</th>
                                                <th class="text-center">Sel.</th>
                                            </tr>
                                        </thead>
                                        <tbody id="listaPedidosPendientes">
                                            @foreach ($pedidosPendientesTodos->sortBy('created_at') as $index => $pedido)
                                                @php
                                                    $pagadoBs = $pedido->pagado_bs ?? 0;
                                                    $descuentoBs = $pedido->descuento_bs ?? 0;
                                                    $pendiente = $pedido->subtotal - $pagadoBs - $descuentoBs;

                                                    // Determinar el tipo de pedido basado en pedido_dia
                                                    $tipoPedido = 'Regular';
                                                    $badgeClass = 'bg-primary';

                                                    if (
                                                        $pedido->pedidos_productos &&
                                                        $pedido->pedidos_productos->count() > 0
                                                    ) {
                                                        $primerProducto = $pedido->pedidos_productos->first();

                                                        if ($primerProducto->pedido_dia == 1) {
                                                            $tipoPedido = 'Frial';
                                                            $badgeClass = 'bg-success';
                                                        } elseif ($primerProducto->pedido_dia == 2) {
                                                            $tipoPedido = 'Frial Sofia';
                                                            $badgeClass = 'bg-info';
                                                        }
                                                    }
                                                @endphp
                                                <tr>
                                                    <td class="text-center">{{ $index + 1 }}</td>
                                                    <td>{{ $pedido->created_at->format('d/m/Y') }}</td>
                                                    <td>#{{ $pedido->codigo ?? $pedido->id }}</td>
                                                    <td>
                                                        <span
                                                            class="badge {{ $badgeClass }}">{{ $tipoPedido }}</span>
                                                    </td>
                                                    <td class="text-end">Bs. {{ number_format($pendiente, 2) }}</td>
                                                    <td class="text-center">
                                                        <input type="checkbox" class="pedido-check"
                                                            data-pedido-id="{{ $pedido->id }}"
                                                            data-pendiente="{{ $pendiente }}"
                                                            data-codigo="{{ $pedido->codigo ?? $pedido->id }}"
                                                            data-fecha="{{ $pedido->created_at->format('d/m/Y') }}"
                                                            data-tipo="{{ $tipoPedido }}"
                                                            data-badge-class="{{ $badgeClass }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3">
                                    <div class="card border-success">
                                        <div class="card-body p-3">
                                            <div class="row">
                                                <div class="col-6">
                                                    <p class="mb-1 text-muted small">Total seleccionado:</p>
                                                    <p class="mb-0 fw-bold fs-5 text-success" id="totalSeleccionado">Bs.
                                                        0.00</p>
                                                </div>
                                                <div class="col-6">
                                                    <p class="mb-1 text-muted small">Total pendiente:</p>
                                                    <p class="mb-0 fw-bold text-danger" id="totalPendienteTodos">
                                                        Bs.
                                                        {{ number_format(
                                                            $pedidosPendientesTodos->sum(function ($p) {
                                                                $pagadoBs = $p->pagado_bs ?? 0;
                                                                $descuentoBs = $p->descuento_bs ?? 0;
                                                                return $p->subtotal - $pagadoBs - $descuentoBs;
                                                            }),
                                                            2,
                                                        ) }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Columna derecha: Datos del pago -->
                            <div class="col-md-7">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="ri-calculator-line me-2"></i>Datos del Pago
                                </h6>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="montoTotalPago" class="form-label fw-medium">
                                                Monto Total a Pagar (Bs.) <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group input-group-lg">
                                                <span
                                                    class="input-group-text bg-success-subtle text-success fw-bold">Bs.</span>
                                                <input type="number" step="0.01" min="0.01" class="form-control"
                                                    id="montoTotalPago" name="monto_total_pago" required
                                                    placeholder="Ej: 500.00">
                                                <button type="button" class="btn btn-outline-success"
                                                    id="btnPagarTodoMultiple">
                                                    <i class="ri-check-double-line"></i> Todo
                                                </button>
                                            </div>
                                            <div class="form-text small">Mįximo disponible: <span id="maxPagoMultiple"
                                                    class="fw-bold text-success">Bs. 0.00</span></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="descuentoTotal" class="form-label fw-medium">
                                                Descuento Total (Bs.)
                                            </label>
                                            <div class="input-group input-group-lg">
                                                <span
                                                    class="input-group-text bg-warning-subtle text-warning fw-bold">Bs.</span>
                                                <input type="number" step="0.01" min="0" class="form-control"
                                                    id="descuentoTotal" name="descuento_total" value="0"
                                                    placeholder="Ej: 20.00">
                                                <button type="button" class="btn btn-outline-warning"
                                                    id="btnSinDescuentoMultiple">
                                                    <i class="ri-close-line"></i> Cero
                                                </button>
                                            </div>
                                            <div class="form-text small">Descuento total a distribuir</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="tipoPagoMultiple" class="form-label fw-medium">
                                        Tipo de Pago <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select form-select-lg" id="tipoPagoMultiple" name="tipo_pago"
                                        required>
                                        <option value="">Seleccionar método...</option>
                                        <option value="efectivo">💵 Efectivo</option>
                                        <option value="qr">📱 QR / Transferencia</option>
                                        <option value="parcial">💰 Pago Parcial (Efectivo + QR)</option>
                                    </select>
                                </div>

                                <!-- Campos para pago parcial -->
                                <div id="camposParcialMultiple" style="display: none;"
                                    class="border rounded p-3 bg-light">
                                    <h6 class="text-info fw-bold mb-3">
                                        <i class="ri-money-cny-circle-line me-2"></i>Distribución del Pago
                                    </h6>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="montoEfectivoMultiple" class="form-label fw-medium">Monto Efectivo
                                                (Bs.)</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Bs.</span>
                                                <input type="number" step="0.01" min="0" class="form-control"
                                                    id="montoEfectivoMultiple" name="monto_efectivo" value="0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="montoQrMultiple" class="form-label fw-medium">Monto QR
                                                (Bs.)</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Bs.</span>
                                                <input type="number" step="0.01" min="0" class="form-control"
                                                    id="montoQrMultiple" name="monto_qr" value="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="alert alert-info py-2 mb-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="ri-information-line me-2"></i>
                                                <strong>Total parcial:</strong>
                                                <span id="totalParcialMultiple" class="fw-bold ms-2">Bs. 0.00</span>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-info"
                                                id="btnDistribuirMultiple">
                                                <i class="ri-share-line me-1"></i> Distribuir 50/50
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- En la sección de Distribución Automática -->
                                <div class="card border-primary mt-3">
                                    <div class="card-header bg-primary-subtle py-2">
                                        <h6 class="mb-0 text-primary">
                                            <i class="ri-arrow-right-line me-2"></i>Distribución Automática (más antiguos
                                            primero)
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Pedido</th>
                                                        <th>Tipo</th> <!-- Columna nueva -->
                                                        <th class="text-end">Pendiente</th>
                                                        <th class="text-end">Monto a Pagar</th>
                                                        <th class="text-end">Descuento</th>
                                                        <th class="text-end">Restante</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tablaDistribucion">
                                                    <!-- Se llenará dinámicamente -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Resumen -->
                                <div class="row mt-3">
                                    <div class="col-md-4">
                                        <div class="card border-success">
                                            <div class="card-body text-center p-2">
                                                <p class="mb-0 text-muted small">Total a Pagar</p>
                                                <p class="mb-0 fw-bold text-success fs-4" id="totalPagarResumen">Bs. 0.00
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-warning">
                                            <div class="card-body text-center p-2">
                                                <p class="mb-0 text-muted small">Total Descuento</p>
                                                <p class="mb-0 fw-bold text-warning fs-4" id="totalDescuentoResumen">Bs.
                                                    0.00</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-danger">
                                            <div class="card-body text-center p-2">
                                                <p class="mb-0 text-muted small">Deuda Restante</p>
                                                <p class="mb-0 fw-bold text-danger fs-4" id="deudaRestanteResumen">Bs.
                                                    0.00</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3">
                        <div class="w-100 d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-0 text-muted small">Pedidos a procesar: <span
                                        id="cantidadPedidosSeleccionados" class="fw-bold">0</span></p>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-secondary btn-lg me-2"
                                    data-bs-dismiss="modal">
                                    <i class="ri-close-line me-1"></i> Cancelar
                                </button>
                                <button type="submit" class="btn btn-success btn-lg" id="btnConfirmarPagoMultiple">
                                    <i class="ri-check-double-line me-1"></i> Confirmar Pago Múltiple
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal para ver productos del pedido -->
    <div class="modal fade" id="modalVerProductosPedido" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-info-subtle p-3">
                    <h5 class="modal-title fw-bold text-info">
                        <i class="ri-shopping-bag-line me-2 align-bottom"></i>
                        Productos del Pedido
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="contenedorProductosPedido">
                    <div class="text-center">
                        <div class="spinner-border text-info" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando productos...</p>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script>
        $(document).ready(function() {
            let isProcessing = false;
            let pendienteActual = 0;
            let subtotalPedido = 0;
            let pagadoAnterior = 0;
            let descuentoAnterior = 0;

            // Variables globales para pago múltiple
            let pedidosSeleccionados = [];
            let distribucionPago = [];

            // ========== PAGO MÚLTIPLE ==========
            // Usar delegación de eventos para el botón dinámico
            $(document).on('click', '#btnPagoMultiple', function(e) {
                e.preventDefault();
                console.log('Botón Pago Múltiple clickeado');

                // Reiniciar selección
                $('.pedido-check').prop('checked', false);
                $('#selectAllPedidos').prop('checked', false);
                pedidosSeleccionados = [];
                distribucionPago = [];

                // Resetear formulario
                $('#montoTotalPago').val('');
                $('#descuentoTotal').val('0.00');
                $('#tipoPagoMultiple').val('');
                $('#camposParcialMultiple').hide();
                $('#montoEfectivoMultiple').val('0.00');
                $('#montoQrMultiple').val('0.00');

                // Actualizar total pendiente
                calcularTotalSeleccionado();

                // Mostrar el modal
                const modal = new bootstrap.Modal(document.getElementById('modalPagoMultiple'));
                modal.show();
            });

            // Seleccionar/deseleccionar todos los pedidos
            $('#selectAllPedidos').change(function() {
                const isChecked = $(this).prop('checked');
                $('.pedido-check').prop('checked', isChecked);

                // Actualizar array de pedidos seleccionados
                if (isChecked) {
                    $('.pedido-check').each(function() {
                        const pedidoId = $(this).data('pedido-id');
                        const pendiente = parseFloat($(this).data('pendiente')) || 0;
                        const codigo = $(this).data('codigo');
                        const fecha = $(this).data('fecha');

                        if (!pedidosSeleccionados.some(p => p.id === pedidoId)) {
                            pedidosSeleccionados.push({
                                id: pedidoId,
                                pendiente: pendiente,
                                codigo: codigo,
                                fecha: fecha,
                                descripcion: `Pedido #${codigo} (${fecha})`
                            });
                        }
                    });
                } else {
                    pedidosSeleccionados = [];
                }

                calcularTotalSeleccionado();
                calcularDistribucionAutomatica();
            });

            // Actualizar la función que agrega pedidos al array
            $(document).on('change', '.pedido-check', function() {
                const pedidoId = $(this).data('pedido-id');
                const pendiente = parseFloat($(this).data('pendiente')) || 0;
                const codigo = $(this).data('codigo');
                const fecha = $(this).data('fecha');
                const tipo = $(this).data('tipo'); // Nuevo: tipo de pedido
                const badgeClass = $(this).data('badge-class'); // Nuevo: clase del badge

                if ($(this).prop('checked')) {
                    // Agregar a la lista
                    if (!pedidosSeleccionados.some(p => p.id === pedidoId)) {
                        pedidosSeleccionados.push({
                            id: pedidoId,
                            pendiente: pendiente,
                            codigo: codigo,
                            fecha: fecha,
                            tipo: tipo, // Guardar el tipo
                            badgeClass: badgeClass, // Guardar la clase del badge
                            descripcion: `Pedido #${codigo} (${fecha})`
                        });
                    }
                } else {
                    // Remover de la lista
                    pedidosSeleccionados = pedidosSeleccionados.filter(p => p.id !== pedidoId);
                    $('#selectAllPedidos').prop('checked', false);
                }

                calcularTotalSeleccionado();
                calcularDistribucionAutomatica();
            });

            // Calcular total seleccionado
            function calcularTotalSeleccionado() {
                let total = 0;
                pedidosSeleccionados.forEach(pedido => {
                    total += pedido.pendiente;
                });

                $('#totalSeleccionado').text('Bs. ' + total.toFixed(2));
                $('#maxPagoMultiple').text('Bs. ' + total.toFixed(2));
                $('#cantidadPedidosSeleccionados').text(pedidosSeleccionados.length);

                // Si hay pedidos seleccionados, activar campos
                if (pedidosSeleccionados.length > 0) {
                    $('#montoTotalPago').prop('disabled', false);
                    $('#descuentoTotal').prop('disabled', false);
                    $('#tipoPagoMultiple').prop('disabled', false);

                    // Si hay solo un pedido seleccionado, sugerir pagar todo
                    if (pedidosSeleccionados.length === 1) {
                        $('#montoTotalPago').val(total.toFixed(2));
                    }
                } else {
                    $('#montoTotalPago').prop('disabled', true);
                    $('#descuentoTotal').prop('disabled', true);
                    $('#tipoPagoMultiple').prop('disabled', true);
                    $('#montoTotalPago').val('');
                }

                return total;
            }

            // Botón para pagar todo el seleccionado
            $('#btnPagarTodoMultiple').click(function() {
                const totalSeleccionado = calcularTotalSeleccionado();
                $('#montoTotalPago').val(totalSeleccionado.toFixed(2));
                calcularDistribucionAutomatica();
            });

            // Botón para quitar descuento
            $('#btnSinDescuentoMultiple').click(function() {
                $('#descuentoTotal').val('0.00');
                calcularDistribucionAutomatica();
            });

            // Mostrar/ocultar campos de pago parcial
            $('#tipoPagoMultiple').change(function() {
                if ($(this).val() === 'parcial') {
                    $('#camposParcialMultiple').show();
                    const montoPago = parseFloat($('#montoTotalPago').val()) || 0;
                    $('#montoEfectivoMultiple').val(montoPago.toFixed(2));
                    $('#montoQrMultiple').val('0.00');
                    calcularTotalParcialMultiple();
                } else {
                    $('#camposParcialMultiple').hide();
                }
            });

            // Botón para distribuir 50/50
            $('#btnDistribuirMultiple').click(function() {
                const montoPago = parseFloat($('#montoTotalPago').val()) || 0;
                if (montoPago > 0) {
                    const mitad = (montoPago / 2).toFixed(2);
                    $('#montoEfectivoMultiple').val(mitad);
                    $('#montoQrMultiple').val(mitad);
                    calcularTotalParcialMultiple();
                }
            });

            // Calcular total parcial
            function calcularTotalParcialMultiple() {
                let efectivo = parseFloat($('#montoEfectivoMultiple').val()) || 0;
                let qr = parseFloat($('#montoQrMultiple').val()) || 0;
                let total = efectivo + qr;
                $('#totalParcialMultiple').text('Bs. ' + total.toFixed(2));
                return total;
            }

            // Modificar la función calcularDistribucionAutomatica para incluir el tipo
            function calcularDistribucionAutomatica() {
                const montoTotalPago = parseFloat($('#montoTotalPago').val()) || 0;
                const descuentoTotal = parseFloat($('#descuentoTotal').val()) || 0;

                // Reiniciar distribución
                distribucionPago = [];
                let montoRestante = montoTotalPago;
                let descuentoRestante = descuentoTotal;
                let htmlTabla = '';

                // Ordenar pedidos por fecha (más antiguos primero)
                pedidosSeleccionados.sort((a, b) => {
                    const parseFecha = (fechaStr) => {
                        const [day, month, year] = fechaStr.split('/');
                        return new Date(year, month - 1, day);
                    };

                    try {
                        const dateA = parseFecha(a.fecha);
                        const dateB = parseFecha(b.fecha);
                        return dateA - dateB;
                    } catch (e) {
                        return 0;
                    }
                });

                // Calcular total pendiente seleccionado
                const totalPendienteSeleccionado = pedidosSeleccionados.reduce((sum, pedido) => sum + pedido
                    .pendiente, 0);

                // Validar que el monto a pagar no sea mayor al total pendiente
                if (montoTotalPago > totalPendienteSeleccionado) {
                    showNotification('warning',
                        `El monto a pagar (Bs. ${montoTotalPago.toFixed(2)}) supera el total pendiente seleccionado (Bs. ${totalPendienteSeleccionado.toFixed(2)}). Se ajustará automáticamente.`
                    );
                    $('#montoTotalPago').val(totalPendienteSeleccionado.toFixed(2));
                    calcularDistribucionAutomatica();
                    return;
                }

                // Para cada pedido, calcular cuánto se le asignará
                pedidosSeleccionados.forEach((pedido, index) => {
                    let montoAsignado = 0;
                    let descuentoAsignado = 0;

                    if (montoRestante > 0 || descuentoRestante > 0) {
                        // Primero, calcular cuánto se puede asignar a este pedido
                        const disponibleParaEstePedido = pedido.pendiente;

                        // Asignar monto a pagar
                        if (montoRestante > 0) {
                            montoAsignado = Math.min(disponibleParaEstePedido, montoRestante);
                            montoRestante -= montoAsignado;
                        }

                        // Asignar descuento (si hay espacio disponible después del pago)
                        const espacioDisponible = pedido.pendiente - montoAsignado;
                        if (espacioDisponible > 0 && descuentoRestante > 0) {
                            descuentoAsignado = Math.min(espacioDisponible, descuentoRestante);
                            descuentoRestante -= descuentoAsignado;
                        }
                    }

                    const totalAsignado = montoAsignado + descuentoAsignado;
                    const nuevoPendiente = pedido.pendiente - totalAsignado;

                    // Agregar a la distribución
                    distribucionPago.push({
                        pedido_id: pedido.id,
                        monto_asignado: parseFloat(montoAsignado.toFixed(2)),
                        descuento_asignado: parseFloat(descuentoAsignado.toFixed(2)),
                        pendiente_inicial: parseFloat(pedido.pendiente.toFixed(2)),
                        nuevo_pendiente: parseFloat(nuevoPendiente.toFixed(2))
                    });

                    // Generar fila para la tabla CON EL TIPO DE PEDIDO
                    htmlTabla += `
        <tr>
            <td>${pedido.descripcion || pedido.codigo}</td>
            <td><span class="badge ${pedido.badgeClass}">${pedido.tipo}</span></td>
            <td class="text-end">Bs. ${pedido.pendiente.toFixed(2)}</td>
            <td class="text-end ${montoAsignado > 0 ? 'text-success fw-bold' : ''}">Bs. ${montoAsignado.toFixed(2)}</td>
            <td class="text-end ${descuentoAsignado > 0 ? 'text-warning fw-bold' : ''}">Bs. ${descuentoAsignado.toFixed(2)}</td>
            <td class="text-end ${nuevoPendiente > 0 ? 'text-danger fw-bold' : ''}">Bs. ${nuevoPendiente.toFixed(2)}</td>
        </tr>
    `;
                });

                // Actualizar tabla
                $('#tablaDistribucion').html(htmlTabla);

                // Calcular resumen
                const totalPagado = montoTotalPago - montoRestante;
                const totalDescuento = descuentoTotal - descuentoRestante;
                const deudaRestante = totalPendienteSeleccionado - totalPagado - totalDescuento;

                $('#totalPagarResumen').text('Bs. ' + totalPagado.toFixed(2));
                $('#totalDescuentoResumen').text('Bs. ' + totalDescuento.toFixed(2));
                $('#deudaRestanteResumen').text('Bs. ' + deudaRestante.toFixed(2));

                // Mostrar advertencia si no se aplicó todo el monto
                if (montoRestante > 0.01 || descuentoRestante > 0.01) {
                    showNotification('warning',
                        `Atención: No se pudo aplicar todo el monto. Restante: Bs. ${(montoRestante + descuentoRestante).toFixed(2)}`
                    );
                }

                console.log('Distribución calculada:', distribucionPago);
            }

            // Eventos para cálculos en tiempo real
            $('#montoTotalPago, #descuentoTotal').on('input', function() {
                calcularDistribucionAutomatica();
            });

            $('#montoEfectivoMultiple, #montoQrMultiple').on('input', function() {
                const tipoPago = $('#tipoPagoMultiple').val();
                if (tipoPago === 'parcial') {
                    const totalParcial = calcularTotalParcialMultiple();
                    $('#montoTotalPago').val(totalParcial.toFixed(2));
                    calcularDistribucionAutomatica();
                }
            });

            // Enviar formulario de pago múltiple
            $('#formPagoMultiple').submit(function(e) {
                e.preventDefault();

                // Si solo hay un pedido seleccionado, usar pago individual
                if (pedidosSeleccionados.length === 1) {
                    // Preparar datos para pago individual
                    const pedido = pedidosSeleccionados[0];
                    const distribucion = distribucionPago.find(d => d.pedido_id == pedido.id);

                    if (!distribucion) {
                        showNotification('error',
                            'No se encontró distribución para el pedido seleccionado');
                        return;
                    }

                    // Usar el modal de pago individual
                    $('#pedidoIdDescuento').val(pedido.id);
                    $('#montoPagoDescuento').val(distribucion.monto_asignado);
                    $('#descuentoPago').val(distribucion.descuento_asignado);
                    $('#tipoPago').val($('#tipoPagoMultiple').val());

                    // Cerrar modal múltiple y abrir modal individual
                    $('#modalPagoMultiple').modal('hide');
                    $('#modalPagoConDescuento').modal('show');
                    return;
                }
                if (isProcessing) return;

                // Validar que haya pedidos seleccionados
                if (pedidosSeleccionados.length === 0) {
                    showNotification('error',
                        'Seleccione al menos un pedido para realizar el pago múltiple.');
                    return;
                }

                // Validar tipo de pago
                const tipoPago = $('#tipoPagoMultiple').val();
                if (!tipoPago) {
                    showNotification('error', 'Seleccione un tipo de pago.');
                    return;
                }

                // Validar monto a pagar
                const montoTotalPago = parseFloat($('#montoTotalPago').val()) || 0;
                if (montoTotalPago <= 0) {
                    showNotification('error', 'El monto a pagar debe ser mayor a cero.');
                    return;
                }

                // Validar que si es parcial, la suma sea igual al monto total
                if (tipoPago === 'parcial') {
                    const montoEfectivo = parseFloat($('#montoEfectivoMultiple').val()) || 0;
                    const montoQr = parseFloat($('#montoQrMultiple').val()) || 0;
                    const totalParcial = montoEfectivo + montoQr;

                    if (Math.abs(montoTotalPago - totalParcial) > 0.01) {
                        showNotification('error',
                            'La suma de los montos en efectivo y QR debe ser igual al monto total a pagar.'
                        );
                        return;
                    }
                }

                // Validar que la distribución se haya calculado
                if (distribucionPago.length === 0) {
                    showNotification('error',
                        'Error en la distribución del pago. Por favor, verifique los montos.');
                    return;
                }

                // Confirmar con el usuario
                if (!confirm('¿Está seguro de realizar este pago múltiple?')) {
                    return;
                }

                isProcessing = true;
                const submitBtn = $('#btnConfirmarPagoMultiple');
                const originalText = submitBtn.html();

                submitBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...');

                // Preparar datos para enviar
                const pedidosIds = pedidosSeleccionados.map(p => p.id);

                // Crear FormData para enviar los datos correctamente
                const formData = new FormData();
                formData.append('_token', "{{ csrf_token() }}");
                formData.append('cliente_id', "{{ $cliente->id }}");
                formData.append('monto_total_pago', montoTotalPago.toFixed(2));
                formData.append('descuento_total', parseFloat($('#descuentoTotal').val()).toFixed(2) ||
                    '0.00');
                formData.append('tipo_pago', tipoPago);

                if (tipoPago === 'parcial') {
                    formData.append('monto_efectivo', parseFloat($('#montoEfectivoMultiple').val()).toFixed(
                        2) || '0.00');
                    formData.append('monto_qr', parseFloat($('#montoQrMultiple').val()).toFixed(2) ||
                        '0.00');
                }

                // Agregar IDs de pedidos como array
                pedidosIds.forEach((id, index) => {
                    formData.append(`pedidos_ids[${index}]`, id);
                });

                // Agregar distribución
                distribucionPago.forEach((dist, index) => {
                    formData.append(`distribucion[${index}][pedido_id]`, dist.pedido_id);
                    formData.append(`distribucion[${index}][monto_asignado]`, dist.monto_asignado);
                    formData.append(`distribucion[${index}][descuento_asignado]`, dist
                        .descuento_asignado);
                });

                console.log('Enviando datos de pago múltiple:', {
                    cliente_id: "{{ $cliente->id }}",
                    pedidos_ids: pedidosIds,
                    monto_total_pago: montoTotalPago,
                    descuento_total: parseFloat($('#descuentoTotal').val()) || 0,
                    tipo_pago: tipoPago,
                    distribucion: distribucionPago
                });

                $.ajax({
                    url: "{{ route('admin.clientes.pago-multiple-priorizado') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        console.log('Respuesta del servidor:', res);
                        if (res.success) {
                            showNotification('success', res.msg);
                            $('#modalPagoMultiple').modal('hide');

                            // Si hay recibos, abrir el primero en nueva pestaña
                            if (res.recibos && res.recibos.length > 0) {
                                setTimeout(() => {
                                    window.open(
                                        "/" +
                                        res.recibos[0], '_blank');
                                }, 500);
                            }

                            // Recargar la página después de un tiempo
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        } else {
                            showNotification('error', res.msg ||
                                'Error desconocido del servidor');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la petición:', xhr.responseText);
                        let errorMsg = 'Error al procesar el pago múltiple';

                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.msg) {
                                errorMsg = response.msg;
                            } else if (response.errors) {
                                errorMsg = Object.values(response.errors).flat().join(', ');
                            } else if (response.message) {
                                errorMsg = response.message;
                            }
                        } catch (e) {
                            if (xhr.responseText) {
                                errorMsg = xhr.responseText.substring(0, 200);
                            }
                        }

                        showNotification('error', errorMsg);
                    },
                    complete: function() {
                        isProcessing = false;
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // ========== FUNCIÓN PARA NOTIFICACIONES ==========
            function showNotification(type, message) {
                // Crear contenedor si no existe
                if ($('.toast-container').length === 0) {
                    $('body').append(
                        '<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999"></div>'
                    );
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

                $('.toast-container').append(toast);
                const bsToast = new bootstrap.Toast(toast[0]);
                bsToast.show();

                toast.on('hidden.bs.toast', function() {
                    $(this).remove();
                });
            }

            // ========== FUNCIONES PARA PAGO INDIVIDUAL (EXISTENTES) ==========
            // Función para abrir modal de pago con descuento
            $(document).on('click', '.pagarConDescuento', function() {
                const pedidoId = $(this).data('pedido-id');
                const pedidoDesc = $(this).data('pedido-desc');
                subtotalPedido = parseFloat($(this).data('subtotal')) || 0;
                pagadoAnterior = parseFloat($(this).data('pagado')) || 0;
                descuentoAnterior = parseFloat($(this).data('descuento')) || 0;
                pendienteActual = parseFloat($(this).data('pendiente')) || 0;

                // Mostrar datos del pedido
                $('#pedidoIdDescuento').val(pedidoId);
                $('#pedidoDescripcionDescuento').text(pedidoDesc || 'Pedido #' + pedidoId);
                $('#subtotalPedido').text('Bs. ' + subtotalPedido.toFixed(2));
                $('#pagadoAnterior').text('Bs. ' + pagadoAnterior.toFixed(2));
                $('#descuentoAnterior').text('Bs. ' + descuentoAnterior.toFixed(2));
                $('#pendienteActual').text('Bs. ' + pendienteActual.toFixed(2));
                $('#maxPago').text('Bs. ' + pendienteActual.toFixed(2));
                $('#maxDescuento').text('Bs. ' + pendienteActual.toFixed(2));

                // Resetear valores del formulario
                $('#montoPagoDescuento').val(pendienteActual.toFixed(2));
                $('#descuentoPago').val('0.00');
                $('#tipoPago').val('');
                $('#camposParcial').hide();
                $('#montoEfectivo').val('0.00');
                $('#montoQr').val('0.00');
                $('#mensajeExito').hide();
                $('#mensajeAdvertencia').hide();

                // Calcular valores iniciales
                calcularYMostrarResultados();

                $('#modalPagoConDescuento').modal('show');
            });

            // Botón para pagar todo
            $('#btnPagarTodo').click(function() {
                $('#montoPagoDescuento').val(pendienteActual.toFixed(2));
                calcularYMostrarResultados();
            });

            // Botón para quitar descuento
            $('#btnSinDescuento').click(function() {
                $('#descuentoPago').val('0.00');
                calcularYMostrarResultados();
            });

            // Botón para distribuir pago entre efectivo y QR
            $('#btnDistribuir').click(function() {
                const montoPago = parseFloat($('#montoPagoDescuento').val()) || 0;
                if (montoPago > 0) {
                    const mitad = (montoPago / 2).toFixed(2);
                    $('#montoEfectivo').val(mitad);
                    $('#montoQr').val(mitad);
                    calcularTotalParcial();
                    calcularYMostrarResultados();
                }
            });

            // Mostrar/ocultar campos de pago parcial
            $('#tipoPago').change(function() {
                if ($(this).val() === 'parcial') {
                    $('#camposParcial').show();
                    const montoPago = parseFloat($('#montoPagoDescuento').val()) || 0;
                    $('#montoEfectivo').val(montoPago.toFixed(2));
                    $('#montoQr').val('0.00');
                    calcularTotalParcial();
                    calcularYMostrarResultados();
                } else {
                    $('#camposParcial').hide();
                }
            });

            // Calcular total parcial
            function calcularTotalParcial() {
                let efectivo = parseFloat($('#montoEfectivo').val()) || 0;
                let qr = parseFloat($('#montoQr').val()) || 0;
                let total = efectivo + qr;
                $('#totalParcial').text('Bs. ' + total.toFixed(2));
                return total;
            }

            // Calcular y mostrar resultados en tiempo real
            function calcularYMostrarResultados() {
                // Obtener valores del formulario
                let montoPago = parseFloat($('#montoPagoDescuento').val()) || 0;
                let descuentoNuevo = parseFloat($('#descuentoPago').val()) || 0;
                const tipoPago = $('#tipoPago').val();

                // Validar que no se exceda el pendiente
                if (montoPago + descuentoNuevo > pendienteActual) {
                    // Si se excede, ajustar proporcionalmente
                    if (descuentoNuevo > 0) {
                        // Si hay descuento, priorizar el pago sobre el descuento
                        descuentoNuevo = Math.min(descuentoNuevo, pendienteActual);
                        montoPago = pendienteActual - descuentoNuevo;
                        $('#descuentoPago').val(descuentoNuevo.toFixed(2));
                        $('#montoPagoDescuento').val(montoPago.toFixed(2));
                    } else {
                        montoPago = pendienteActual;
                        $('#montoPagoDescuento').val(montoPago.toFixed(2));
                    }
                    showNotification('warning', 'Ajustado al máximo disponible: Bs. ' + pendienteActual.toFixed(2));
                }

                // Si es pago parcial, validar distribución
                if (tipoPago === 'parcial') {
                    const totalParcial = calcularTotalParcial();
                    if (Math.abs(montoPago - totalParcial) > 0.01) {
                        // Si la distribución no coincide con el monto, ajustar
                        $('#montoEfectivo').val(montoPago.toFixed(2));
                        $('#montoQr').val('0.00');
                        calcularTotalParcial();
                    }
                }

                // Calcular valores
                const totalReduccion = montoPago + descuentoNuevo;
                const deudaRestante = pendienteActual - totalReduccion;
                const pendienteFinal = deudaRestante;

                // Mostrar valores calculados
                $('#montoCalculado').text('Bs. ' + montoPago.toFixed(2));
                $('#descuentoCalculado').text('Bs. ' + descuentoNuevo.toFixed(2));
                $('#totalReduccion').text('Bs. ' + totalReduccion.toFixed(2));
                $('#deudaRestante').text('Bs. ' + deudaRestante.toFixed(2));
                $('#pendienteFinal').text('Bs. ' + pendienteFinal.toFixed(2));

                // Mostrar mensajes según el caso
                if (deudaRestante <= 0.01) {
                    $('#mensajeExito').show();
                    $('#mensajeAdvertencia').hide();
                    $('#textoMensaje').html('<strong>✓ El pedido quedará completamente pagado</strong><br>' +
                        '<small class="text-muted">No habrá deuda pendiente</small>');
                } else {
                    $('#mensajeExito').hide();
                    $('#mensajeAdvertencia').show();
                    $('#textoAdvertencia').html('<strong>⚠ El pedido tendrá deuda pendiente</strong><br>' +
                        '<small class="text-muted">Deuda restante: <span class="fw-bold text-danger">Bs. ' +
                        deudaRestante.toFixed(2) + '</span></small>');
                }

                // Actualizar máximo de descuento
                const maxDescuento = pendienteActual - montoPago;
                $('#maxDescuento').text('Bs. ' + maxDescuento.toFixed(2));
            }

            // Eventos para cálculos en tiempo real
            $('#montoPagoDescuento, #descuentoPago').on('input', function() {
                calcularYMostrarResultados();
            });

            $('#montoEfectivo, #montoQr').on('input', function() {
                const tipoPago = $('#tipoPago').val();
                if (tipoPago === 'parcial') {
                    const totalParcial = calcularTotalParcial();
                    $('#montoPagoDescuento').val(totalParcial.toFixed(2));
                    calcularYMostrarResultados();
                }
            });

            // Enviar formulario de pago con descuento
            $('#formPagoConDescuento').submit(function(e) {
                e.preventDefault();
                if (isProcessing) return;

                const form = $(this)[0];
                if (form.checkValidity() === false) {
                    form.classList.add('was-validated');
                    return;
                }

                // Validar tipo de pago
                const tipoPago = $('#tipoPago').val();
                if (!tipoPago) {
                    showNotification('error', 'Seleccione un tipo de pago');
                    return;
                }

                // Validar que si es parcial, la suma sea igual al monto a pagar
                const montoPago = parseFloat($('#montoPagoDescuento').val()) || 0;

                if (tipoPago === 'parcial') {
                    const montoEfectivo = parseFloat($('#montoEfectivo').val()) || 0;
                    const montoQr = parseFloat($('#montoQr').val()) || 0;
                    const totalParcial = montoEfectivo + montoQr;

                    // Usar tolerancia de 0.01 para manejar decimales
                    if (Math.abs(montoPago - totalParcial) > 0.01) {
                        showNotification('error',
                            'La suma de los montos en efectivo y QR debe ser igual al monto a pagar.');
                        return;
                    }
                }

                isProcessing = true;
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();

                submitBtn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...');

                const formData = {
                    _token: "{{ csrf_token() }}",
                    cliente_id: "{{ $cliente->id }}",
                    pedido_id: $('#pedidoIdDescuento').val(),
                    monto_pago: montoPago,
                    descuento_bs: parseFloat($('#descuentoPago').val()) || 0,
                    tipo_pago: tipoPago,
                    monto_efectivo: tipoPago === 'parcial' ? parseFloat($('#montoEfectivo').val()) ||
                        0 : 0,
                    monto_qr: tipoPago === 'parcial' ? parseFloat($('#montoQr').val()) || 0 : 0
                };

                $.ajax({
                    url: "{{ route('admin.clientes.pago-con-descuento') }}",
                    type: "POST",
                    data: formData,
                    success: function(res) {
                        if (res.success) {
                            showNotification('success', res.msg);
                            $('#modalPagoConDescuento').modal('hide');

                            // Abrir el recibo en nueva pestaña
                            if (res.recibo_url) {
                                setTimeout(() => {
                                    window.open(res.recibo_url, '_blank');
                                }, 500);
                            }

                            // Recargar la página después de un tiempo
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        } else {
                            showNotification('error', res.msg);
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Error al procesar el pago';
                        if (xhr.responseJSON?.msg) {
                            errorMsg = xhr.responseJSON.msg;
                        } else if (xhr.responseJSON?.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors).flat().join(', ');
                        }
                        showNotification('error', errorMsg);
                    },
                    complete: function() {
                        isProcessing = false;
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Validar que los montos sean números válidos
            $('input[type="number"]').on('blur', function() {
                let value = parseFloat($(this).val());
                if (isNaN(value) || value < 0) {
                    $(this).val('0.00');
                    calcularYMostrarResultados();
                } else {
                    $(this).val(value.toFixed(2));
                }
            });

            // Inicializar cuando se abra el modal de pago múltiple
            $('#modalPagoMultiple').on('shown.bs.modal', function() {
                // Recalcular distribución automática
                calcularDistribucionAutomatica();
            });

            // ========== VER PRODUCTOS DEL PEDIDO ==========
            $(document).on('click', '.verProductosBtn', function(e) {
                e.preventDefault();
                const pedidoId = $(this).data('pedido-id');
                const modal = new bootstrap.Modal(document.getElementById('modalVerProductosPedido'));

                // Mostrar modal con spinner
                $('#contenedorProductosPedido').html(`
        <div class="text-center">
            <div class="spinner-border text-info" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 text-muted">Cargando productos...</p>
        </div>
    `);
                modal.show();

                // Petición AJAX
                $.ajax({
                    url: "/admin/pedidos/" + pedidoId + "/detalle-completo",
                    type: "GET",
                    dataType: "json", // fuerza JSON
                    beforeSend: function() {
                        console.log('Enviando petición para pedido:', pedidoId);
                    },
                    success: function(res) {
                        console.log('Respuesta recibida:', res);
                        if (res.success) {
                            const pedido = res.pedido;
                            const cliente = res.cliente;
                            const localidad = res.localidad;
                            const productosNormales = res.productos_normales || [];
                            const polloPrimera = res.pollo_primera_items || [];
                            const totales = res.totales_generales;

                            let html = `
            <div class="mb-3 pb-2 border-bottom">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Pedido:</strong> #${pedido.codigo}</p>
                        <p class="mb-1"><strong>Tipo:</strong> ${pedido.tipo_pedido}</p>
                        <p class="mb-1"><strong>Fecha:</strong> ${pedido.created_at}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Cliente:</strong> ${cliente.nombre_completo}</p>
                        <p class="mb-1"><strong>CI/NIT:</strong> ${cliente.ci}</p>
                        <p class="mb-1"><strong>Localidad:</strong> ${localidad.nombre}</p>
                    </div>
                </div>
            </div>
        `;

                            // --- Productos normales ---
                            if (productosNormales.length > 0) {
                                html += `<h6 class="fw-bold text-primary">Productos</h6>`;
                                html += `<div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr>
                <th>Producto</th>
                <th class="text-end">Peso (kg)</th>
                <th class="text-end">Precio/kg</th>
                <th class="text-end">Subtotal</th>
            </tr></thead><tbody>`;

                                productosNormales.forEach(p => {
                                    // Convertir a número con valor por defecto 0
                                    const pesoReal = Number(p.peso_real) || 0;
                                    const precio = Number(p.precio_unitario) || 0;
                                    const subtotal = Number(p.subtotal) || 0;

                                    html += `<tr>
                    <td>${p.producto_nombre || 'N/A'}</td>
                    <td class="text-end">${pesoReal.toFixed(2)}</td>
                    <td class="text-end">Bs. ${precio.toFixed(2)}</td>
                    <td class="text-end">Bs. ${subtotal.toFixed(2)}</td>
                </tr>`;
                                });
                                html += `</tbody></table></div>`;
                            }

                            // --- Pollo primera (con tara) ---
                            if (polloPrimera.length > 0) {
                                html +=
                                    `<h6 class="fw-bold text-success mt-3">Pollo Primera</h6>`;
                                html += `<div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr>
                <th>Producto</th>
                <th class="text-end">Cajas</th>
                <th class="text-end">Peso con tara</th>
                <th class="text-end">Tara</th>
                <th class="text-end">Peso real</th>
                <th class="text-end">Precio</th>
                <th class="text-end">Subtotal</th>
            </tr></thead><tbody>`;

                                polloPrimera.forEach(p => {
                                    const nCajas = Number(p.n_cajas) || 0;
                                    const peso = Number(p.peso) || 0;
                                    const tara = Number(p.tara) || 0;
                                    const pesoReal = Number(p.peso_real) || 0;
                                    const precio = Number(p.precio_unitario) || 0;
                                    const subtotal = Number(p.subtotal) || 0;

                                    html += `<tr>
                    <td>${p.producto_nombre || 'N/A'}</td>
                    <td class="text-end">${nCajas}</td>
                    <td class="text-end">${peso.toFixed(2)}</td>
                    <td class="text-end">${tara.toFixed(2)}</td>
                    <td class="text-end">${pesoReal.toFixed(2)}</td>
                    <td class="text-end">Bs. ${precio.toFixed(2)}</td>
                    <td class="text-end">Bs. ${subtotal.toFixed(2)}</td>
                </tr>`;
                                });
                                html += `</tbody></table></div>`;
                            }

                            // --- Totales ---
                            const subtotalTotal = Number(totales.subtotal_total) || 0;
                            const descuento = Number(pedido.descuento_bs) || 0;
                            const pagado = Number(pedido.pagado_bs) || 0; // ← CORREGIDO
                            const pendiente = Number(totales.deuda_pendiente) || 0;

                            html += `
            <div class="row mt-3 pt-2 border-top">
                <div class="col-md-6 offset-md-6">
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Total subtotal:</span>
                        <span class="text-primary">Bs. ${subtotalTotal.toFixed(2)}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Descuento aplicado:</span>
                        <span class="text-warning">Bs. ${descuento.toFixed(2)}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Pagado:</span>
                        <span class="text-success">Bs. ${pagado.toFixed(2)}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Pendiente:</span>
                        <span class="text-danger">Bs. ${pendiente.toFixed(2)}</span>
                    </div>
                </div>
            </div>
        `;

                            $('#contenedorProductosPedido').html(html);
                        } else {
                            $('#contenedorProductosPedido').html(`
            <div class="alert alert-danger mb-0">
                <i class="ri-error-warning-line me-2"></i>
                No se pudieron cargar los productos.
            </div>
        `);
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Error al cargar los productos';
                        if (xhr.responseJSON?.msg) msg = xhr.responseJSON.msg;
                        $('#contenedorProductosPedido').html(`
                <div class="alert alert-danger mb-0">
                    <i class="ri-error-warning-line me-2"></i>
                    ${msg}
                </div>
            `);
                    }
                });
            });
        });
    </script>

    <style>
        .spinner-border {
            width: 1rem;
            height: 1rem;
            border-width: 0.2em;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #405189;
            box-shadow: 0 0 0 0.15rem rgba(64, 81, 137, 0.25);
        }

        #mensajeExito {
            background-color: #d1f7c4;
            border-color: #2ca87f;
            color: #0c622f;
        }

        #mensajeAdvertencia {
            background-color: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }

        .card-header.bg-info-subtle {
            background-color: rgba(13, 202, 240, 0.1) !important;
        }

        .border-info {
            border-color: #0dcaf0 !important;
        }

        .tab-pagination .pagination {
            flex-wrap: wrap;
            justify-content: flex-end;
            margin-bottom: 0;
        }

        .tab-pagination .page-link {
            white-space: nowrap;
        }

        @media (max-width: 575.98px) {
            .tab-pagination .pagination {
                justify-content: center;
            }
        }
    </style>
@endpush

@push('script')
    <script>
        $(document).ready(function() {
            const tabUrls = {
                pendientes: "{{ route('admin.clientes.tab', [$cliente->id, 'pendientes']) }}",
                pagados: "{{ route('admin.clientes.tab', [$cliente->id, 'pagados']) }}",
                recibos: "{{ route('admin.clientes.tab', [$cliente->id, 'recibos']) }}"
            };

            const containers = {
                pendientes: '#tabla-pendientes',
                pagados: '#tabla-pagados',
                recibos: '#tabla-recibos'
            };

            $(document).on('click', '.tab-pagination .pagination a', function(e) {
                e.preventDefault();

                const $link = $(this);
                const tab = $link.closest('.tab-pagination').data('tab');

                if (!tab || !tabUrls[tab]) {
                    return;
                }

                const linkUrl = new URL($link.attr('href'), window.location.origin);
                const page = linkUrl.searchParams.get('pendientes_page') ||
                    linkUrl.searchParams.get('pagados_page') ||
                    linkUrl.searchParams.get('pagos_page') ||
                    linkUrl.searchParams.get('page') ||
                    1;

                const $container = $(containers[tab]);
                $container.css('opacity', 0.55);

                $.ajax({
                    url: tabUrls[tab],
                    type: 'GET',
                    data: { page: page },
                    dataType: 'json',
                    success: function(response) {
                        if (response.html) {
                            $container.html(response.html);
                        }
                    },
                    error: function() {
                        alert('No se pudo cargar la página del tab. Intenta nuevamente.');
                    },
                    complete: function() {
                        $container.css('opacity', 1);
                    }
                });
            });
        });
    </script>
@endpush
