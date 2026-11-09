<style>
    /* Agregar al final de los estilos CSS */
    .number-cell {
        font-family: monospace;
        text-align: right;
        white-space: nowrap;
    }

    /* Estilos para números con separadores de miles */
    .formatted-number {
        font-variant-numeric: tabular-nums;
    }
</style>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">Lista de Pedidos {{ $titulo }}</h6>
    <div class="d-flex gap-2">


        <!-- Botón para ver ventas -->
        @if (!$pedidos->isEmpty())
            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                data-bs-target="#modalVentas{{ $tipoPedido }}">
                <i class="ri-bar-chart-line me-1"></i> Reporte día
            </button>
        @endif

        <!-- Botón para reporte de deudas -->
        @if (!$pedidos->isEmpty())
            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                data-bs-target="#modalDeudas{{ $tipoPedido }}">
                <i class="ri-money-dollar-circle-line me-1"></i> Imprimir reporte deudas del día
            </button>
        @endif

        <!-- Botón para reporte de detalle de pedidos -->
        @if (!$pedidos->isEmpty())
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                data-bs-target="#modalDetallePedidos{{ $tipoPedido }}">
                <i class="ri-file-list-line me-1"></i> Imprimir reportes de pedidos concluidos
            </button>
        @endif

        <!-- Botón para reporte consolidado -->

        <!-- Buscador para esta tabla específica -->
        <form method="GET" class="d-flex gap-2">
            <!-- Mantener todos los parámetros de filtro existentes -->
            <input type="hidden" name="fecha" value="{{ request('fecha') }}">
            <input type="hidden" name="localidad_id" value="{{ request('localidad_id') }}">
            <input type="hidden" name="tipo_pedido" value="{{ $tipoPedido }}">

            <div class="input-group input-group-sm" style="width: 300px;">
                <input type="text" name="search" class="form-control"
                    placeholder="Buscar por nombre, apellido o CI..." value="{{ request('search') }}">
                <button class="btn btn-outline-primary" type="submit">
                    <i class="ri-search-line"></i>
                </button>
                @if (request('search'))
                    <a href="{{ request()->url() }}?fecha={{ request('fecha') }}&localidad_id={{ request('localidad_id') }}&tipo_pedido={{ $tipoPedido }}"
                        class="btn btn-outline-secondary">
                        <i class="ri-close-line"></i>
                    </a>
                @endif
            </div>
        </form>

        <!-- Botón para generar pedidos si no existen y está permitido -->
        @if ($pedidos->isEmpty() && $puedeGenerar)
            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                data-bs-target="#modalGenerarPedidos{{ $tipoPedido }}">
                <i class="ri-add-line me-1"></i> Generar Pedidos {{ $titulo }}
            </button>
        @elseif($pedidos->isEmpty() && !$puedeGenerar && $tipoPedido != 0)
            <button type="button" class="btn btn-sm btn-secondary" disabled
                title="Solo se pueden generar pedidos {{ $titulo }} el mismo día de los pedidos regulares ({{ $fechaPedidosRegulares ?? 'N/A' }})">
                <i class="ri-time-line me-1"></i> No disponible
            </button>
        @endif
    </div>
</div>

@if ($pedidos->isEmpty())
    <div class="text-center py-4">
        <div class="text-muted">
            <i class="ri-shopping-cart-line display-4"></i>
            <p class="mt-2">No se encontraron pedidos {{ $titulo }}</p>

            @if ($puedeGenerar)
                <button type="button" class="btn btn-success mt-2" data-bs-toggle="modal"
                    data-bs-target="#modalGenerarPedidos{{ $tipoPedido }}">
                    <i class="ri-add-line me-1"></i> Generar Pedidos {{ $titulo }}
                </button>
            @elseif($tipoPedido != 0)
                <div class="alert alert-warning mt-3">
                    <i class="ri-information-line me-2"></i>
                    @if ($fechaPedidosRegulares)
                        Los pedidos {{ $titulo }} solo se pueden generar el mismo día de los pedidos
                        regulares.<br>
                        <strong>Fecha pedidos regulares:</strong>
                        {{ \Carbon\Carbon::parse($fechaPedidosRegulares)->format('d/m/Y') }}<br>
                        <strong>Fecha actual:</strong> {{ \Carbon\Carbon::parse($fechaActual)->format('d/m/Y') }}
                    @else
                        No hay pedidos regulares para esta localidad y código.
                    @endif
                </div>
            @endif
        </div>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm">
            <thead class="table-light">
                <tr>
                    <th width="50">#</th>
                    <th width="100">Cliente</th>
                    <th width="120">Estado</th>
                    <th width="200">Acciones</th>
                    <th width="120">Subtotal</th>
                    <th width="120">Descuento</th>
                    <th width="120">Pendiente</th>

                </tr>
            </thead>
            <tbody>
                @foreach ($pedidos as $pedido)
                    @php
                        $tienePedidosRegistrados = $pedido->pedidos_productos->where('realizado', '1')->count() > 0;
                        $pedidoProcesado = $pedido->pedidos_productos->where('procesado', '1')->count() > 0;

                        // Para TODOS los tipos de pedido, la fecha de entrega es la fecha del pedido (created_at)
                        $fechaEntrega = \Carbon\Carbon::parse($pedido->created_at)->format('Y-m-d');
                        $fechaActual = now()->format('Y-m-d');

                        // Lógica diferente según el tipo de pedido
                        if ($pedido->pedido_dia == 0) {
                            // Pedidos regulares: disponibles el día anterior y el día de entrega
                            $fechaDisponibleDesde = \Carbon\Carbon::parse($pedido->created_at)
                                ->subDay()
                                ->format('Y-m-d');
                            $esFechaValida = $fechaActual >= $fechaDisponibleDesde && $fechaActual <= $fechaEntrega;
                        } else {
                            // Pedidos Frial y Frial Sofia: disponibles solo el mismo día de entrega
                            $esFechaValida = $fechaActual === $fechaEntrega;
                        }
                    @endphp
                    <tr>
                        <td>{{ ($pedidos->currentPage() - 1) * $pedidos->perPage() + $loop->iteration }}</td>
                        <td>
                            @if ($pedido->cliente)
                                <strong>{{ $pedido->cliente->nombres }} {{ $pedido->cliente->apellidos }}</strong>
                                <br>
                                <small class="text-muted">{{ $pedido->cliente->ci }}</small>
                                <br>
                                <small class="text-info">
                                    <i class="ri-calendar-line"></i>
                                    Entrega: {{ \Carbon\Carbon::parse($fechaEntrega)->format('d/m/Y') }}
                                    @if ($pedido->pedido_dia == 0)
                                        <span class="badge bg-primary">Regular</span>
                                    @elseif($pedido->pedido_dia == 1)
                                        <span class="badge bg-success">Frial</span>
                                    @elseif($pedido->pedido_dia == 2)
                                        <span class="badge bg-info">Frial Sofia</span>
                                    @endif
                                </small>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if ($pedido->subtotal > 0)
                                <span class="badge bg-danger">Deuda Generada</span>
                            @elseif($pedidoProcesado)
                                <span class="badge bg-info">Procesado</span>
                            @elseif($tienePedidosRegistrados)
                                <span class="badge bg-warning">Registrado</span>
                            @else
                                <span class="badge bg-secondary">Pendiente</span>
                            @endif
                            <br>
                            <small class="text-muted">
                                @if (!$esFechaValida && !$pedidoProcesado)
                                    <i class="ri-time-line"></i>
                                    @if ($pedido->pedido_dia == 0)
                                        Disponible desde:
                                        {{ \Carbon\Carbon::parse($fechaEntrega)->subDay()->format('d/m/Y') }}
                                    @else
                                        Disponible el: {{ \Carbon\Carbon::parse($fechaEntrega)->format('d/m/Y') }}
                                    @endif
                                @endif
                            </small>
                        </td>
                        <td>
                            @if ($pedidoProcesado)
                                <!-- Botón Ver Detalle - SIEMPRE DISPONIBLE -->
                                <button type="button" class="btn btn-sm btn-info mb-1" data-bs-toggle="modal"
                                    data-bs-target="#modalDetalleProcesado{{ $pedido->id }}">
                                    <i class="ri-eye-line"></i> Ver Detalle
                                </button>

                                <a href="{{ route('admin.pedidos.reporte-individual-pdf', $pedido->id) }}"
                                    class="btn btn-sm btn-secondary mb-1" target="_blank"
                                    title="Imprimir reporte individual">
                                    <i class="ri-printer-line"></i> Imprimir
                                </a>
                                <!-- Botón Generar Deuda si no se ha generado -->
                                @if ($pedido->subtotal == 0)
                                    <form action="{{ route('admin.pedidos.generar-deuda', $pedido->id) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="ri-money-dollar-circle-line"></i> Generar Deuda
                                        </button>
                                    </form>
                                @endif
                            @elseif($esFechaValida)
                                <!-- En fechas válidas: Registrar, Modificar y Procesar disponibles -->
                                @if ($tienePedidosRegistrados)
                                    <!-- Si ya está registrado: Modificar y Procesar disponibles -->
                                    <a href="{{ route('admin.pedidos.modificar-form', $pedido->id) }}"
                                        class="btn btn-sm btn-warning mb-1">
                                        <i class="ri-edit-line"></i> Modificar
                                    </a>
                                    <br>

                                    <a href="{{ route('admin.pedidos.procesar-form', $pedido->id) }}"
                                        class="btn btn-sm btn-success mb-1">
                                        <i class="ri-settings-line"></i> Procesar
                                    </a>
                                @else
                                    <!-- Si no está registrado: Solo Registrar disponible -->
                                    <a href="{{ route('admin.pedidos.registrar-form', $pedido->id) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class="ri-edit-line"></i> Registrar
                                    </a>
                                @endif
                            @else
                                <!-- Fuera de las fechas válidas: Solo información -->
                                @if ($pedido->pedido_dia == 0)
                                    @if ($fechaActual < $fechaEntrega)
                                        <span class="text-muted small">Disponible desde:</span>
                                        <br>
                                        <small
                                            class="text-info">{{ \Carbon\Carbon::parse($fechaEntrega)->subDay()->format('d/m/Y') }}</small>
                                    @else
                                        @if (Auth::guard('web')->user()->can('pedidos.modificar'))
                                            <!-- En fechas válidas: Registrar, Modificar y Procesar disponibles -->
                                            @if ($tienePedidosRegistrados)
                                                <!-- Si ya está registrado: Modificar y Procesar disponibles -->
                                                <a href="{{ route('admin.pedidos.modificar-form', $pedido->id) }}"
                                                    class="btn btn-sm btn-warning mb-1">
                                                    <i class="ri-edit-line"></i> Modificar
                                                </a>
                                                <br>

                                                <a href="{{ route('admin.pedidos.procesar-form', $pedido->id) }}"
                                                    class="btn btn-sm btn-success mb-1">
                                                    <i class="ri-settings-line"></i> Procesar
                                                </a>
                                            @else
                                                <!-- Si no está registrado: Solo Registrar disponible -->
                                                <a href="{{ route('admin.pedidos.registrar-form', $pedido->id) }}"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="ri-edit-line"></i> Registrar
                                                </a>
                                            @endif
                                        @else
                                            <span class="text-muted small">Fecha vencida</span>
                                            <br>
                                            <small
                                                class="text-danger">{{ \Carbon\Carbon::parse($fechaEntrega)->format('d/m/Y') }}</small>
                                        @endif
                                    @endif
                                @else
                                    @if ($fechaActual < $fechaEntrega)
                                        <span class="text-muted small">Disponible el:</span>
                                        <br>
                                        <small
                                            class="text-info">{{ \Carbon\Carbon::parse($fechaEntrega)->format('d/m/Y') }}</small>
                                    @else
                                        <span class="text-muted small">Fecha vencida</span>
                                        <br>
                                        <small
                                            class="text-danger">{{ \Carbon\Carbon::parse($fechaEntrega)->format('d/m/Y') }}</small>
                                    @endif
                                @endif

                                @if ($tienePedidosRegistrados && !$pedidoProcesado)
                                    <br>
                                    <span class="text-warning small">(Registrado pendiente)</span>
                                @endif
                            @endif
                        </td>
                        <td>Bs. {{ number_format($pedido->subtotal, 2) }}</td>
                        <td>Bs. {{ number_format($pedido->descuento_bs, 2) }}</td>
                        <td>Bs. {{ number_format($pedido->pendiente, 2) }}</td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($tipoPedido == 0)
    <!-- Paginación solo para Pedidos Regulares -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted">
            Mostrando {{ $pedidos->firstItem() }} a {{ $pedidos->lastItem() }} de {{ $pedidos->total() }} registros
        </div>
        <div>
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0">
                    <!-- Enlace Anterior -->
                    <li class="page-item {{ $pedidos->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link"
                            href="{{ $pedidos->previousPageUrl() }}&tipo_pedido={{ $tipoPedido }}"
                            aria-label="Previous">
                            <i class="ri-arrow-left-s-line"></i>
                        </a>
                    </li>

                    <!-- Números de Página - Máximo 5 páginas visibles -->
                    @php
                        $current = $pedidos->currentPage();
                        $last = $pedidos->lastPage();
                        $start = max($current - 2, 1);
                        $end = min($current + 2, $last);

                        if ($current <= 3) {
                            $end = min(5, $last);
                        }
                        if ($current >= $last - 2) {
                            $start = max($last - 4, 1);
                        }
                    @endphp

                    @if ($start > 1)
                        <li class="page-item">
                            <a class="page-link" href="{{ $pedidos->url(1) }}&tipo_pedido={{ $tipoPedido }}">1</a>
                        </li>
                        @if ($start > 2)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                    @endif

                    @for ($i = $start; $i <= $end; $i++)
                        <li class="page-item {{ $i == $current ? 'active' : '' }}">
                            <a class="page-link"
                                href="{{ $pedidos->url($i) }}&tipo_pedido={{ $tipoPedido }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if ($end < $last)
                        @if ($end < $last - 1)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                        <li class="page-item">
                            <a class="page-link"
                                href="{{ $pedidos->url($last) }}&tipo_pedido={{ $tipoPedido }}">{{ $last }}</a>
                        </li>
                    @endif

                    <!-- Enlace Siguiente -->
                    <li class="page-item {{ !$pedidos->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $pedidos->nextPageUrl() }}&tipo_pedido={{ $tipoPedido }}"
                            aria-label="Next">
                            <i class="ri-arrow-right-s-line"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
    @endif
@endif

<!-- Modal para Generar Pedidos -->
<div class="modal fade" id="modalGenerarPedidos{{ $tipoPedido }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Generar Pedidos {{ $titulo }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formGenerarPedidos{{ $tipoPedido }}"
                action="{{ route('admin.pedidos.crear-para-localidad') }}" method="POST"
                data-tipo-pedido="{{ $tipoPedido }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="localidade_id" value="{{ $localidadId }}">
                    <input type="hidden" name="tipo_pedido" value="{{ $tipoPedido }}">

                    <div class="text-center">
                        <i class="ri-information-line display-4 text-primary mb-3"></i>
                        <h5>¿Generar Pedidos {{ $titulo }}?</h5>
                        <p class="text-muted">
                            Se crearán pedidos {{ $titulo }} para todos los clientes de
                            <strong>{{ $localidadNombre }}</strong>.
                        </p>

                        @if ($tipoPedido == 0)
                            <div class="alert alert-info">
                                <i class="ri-calendar-line me-1"></i>
                                <strong>Pedidos Regulares:</strong> Se generan para el día siguiente
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="ri-calendar-event-line me-1"></i>
                                <strong>Pedidos {{ $titulo }}:</strong> Se generan para el día actual
                            </div>
                        @endif

                        <div class="mt-3 p-3 bg-light rounded">
                            <strong>Resumen:</strong><br>
                            - Localidad: {{ $localidadNombre }}<br>
                            - Tipo: {{ $titulo }}<br>
                            - Clientes: {{ $totalClientes }}
                        </div>

                        <div class="alert alert-warning mt-3" id="alert-inactivos-{{ $tipoPedido }}" style="display: none;">
                            <i class="ri-forbid-line me-2"></i>
                            <strong>Clientes deshabilitados (no se incluirán en el pedido):</strong>
                            <ul id="lista-inactivos-{{ $tipoPedido }}" class="mb-0 mt-2 text-start" style="max-height: 150px; overflow-y: auto;"></ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success" id="btnGenerar{{ $tipoPedido }}">
                        <i class="ri-add-line me-1"></i> Generar Pedidos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Ver Ventas -->
@if (!$pedidos->isEmpty())
    <div class="modal fade" id="modalVentas{{ $tipoPedido }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-bar-chart-line me-2"></i>
                        Detalle de Ventas - {{ $titulo }} - Código {{ $codigo }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @php
                        // Obtener todos los productos de la localidad
                        $productosLocalidad = \App\Models\LocalidadesProductos::with('producto')
                            ->where('localidade_id', $localidadId)
                            ->get();

                        // Preparar estructura de productos únicos
                        $productosUnicos = [];
                        $totalGeneral = 0;

                        foreach ($productosLocalidad as $localidadProducto) {
                            $productoId = $localidadProducto->producto_id;
                            $productoNombre = $localidadProducto->producto->nombre;

                            // Consolidar productos con ID 1,2,3 como "Pollo Primera"
                            if (in_array($productoId, [1, 2, 3])) {
                                $productoNombre = 'Pollo Primera';
                            }

                            if (!isset($productosUnicos[$productoNombre])) {
                                $productosUnicos[$productoNombre] = [
                                    'nombre' => $productoNombre,
                                    'total_peso_real' => 0,
                                    'total_costo' => 0,
                                    'precio_referencia' => $localidadProducto->precio,
                                    'ids' => [],
                                ];
                            }

                            // Agregar ID del producto para referencia
                            if (!in_array($productoId, $productosUnicos[$productoNombre]['ids'])) {
                                $productosUnicos[$productoNombre]['ids'][] = $productoId;
                            }
                        }

                        // Obtener todos los clientes de la localidad (para mostrar incluso sin pedidos)
                        $todosClientes = \App\Models\Cliente::where('localidade_id', $localidadId)
                            ->orderBy('orden', 'asc')
                            ->get();

                        // Calcular totales por producto
                        foreach ($todosClientes as $cliente) {
                            // Buscar pedido del cliente para este código y tipo
                            $pedidoCliente = \App\Models\Pedido::where('cliente_id', $cliente->id)
                                ->where('codigo', $codigo)
                                ->where('pedido_dia', $tipoPedido)
                                ->when(request('fecha'), function ($query, $fecha) {
                                    return $query->whereDate('created_at', $fecha);
                                })
                                ->first();

                            if ($pedidoCliente) {
                                $productosProcesados = $pedidoCliente->pedidos_productos->where('procesado', '1');

                                foreach ($productosProcesados as $producto) {
                                    $productoId = $producto->producto_id;
                                    $productoNombre = $producto->producto->nombre;

                                    if (in_array($productoId, [1, 2, 3])) {
                                        $productoNombre = 'Pollo Primera';
                                    }

                                    if (isset($productosUnicos[$productoNombre])) {
                                        $productosUnicos[$productoNombre]['total_peso_real'] += $producto->peso_real;
                                        $productosUnicos[$productoNombre]['total_costo'] += $producto->costo_total;
                                    }
                                }
                                $totalGeneral += $pedidoCliente->subtotal;
                            }
                        }
                    @endphp

                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-bordered table-hover table-sm" id="tablaVentas{{ $tipoPedido }}">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="sticky-left">Cliente</th>
                                    @foreach ($productosUnicos as $producto)
                                        <th colspan="3" class="text-center">
                                            {{ $producto['nombre'] }}
                                        </th>
                                    @endforeach
                                    <th class="sticky-right">Subtotal Pedido</th>
                                </tr>
                                <tr>
                                    <th class="sticky-left"></th>
                                    @foreach ($productosUnicos as $producto)
                                        <th class="text-center">Peso (kg)</th>
                                        <th class="text-center">Precio (Bs.)</th>
                                        <th class="text-center">Subtotal (Bs.)</th>
                                    @endforeach
                                    <th class="text-center sticky-right">Bs.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($todosClientes as $cliente)
                                    @php
                                        // Buscar pedido del cliente para este código y tipo
                                        $pedidoCliente = \App\Models\Pedido::where('cliente_id', $cliente->id)
                                            ->where('codigo', $codigo)
                                            ->where('pedido_dia', $tipoPedido)
                                            ->when(request('fecha'), function ($query, $fecha) {
                                                return $query->whereDate('created_at', $fecha);
                                            })
                                            ->first();

                                        $productosCliente = [];
                                        $subtotalCliente = 0;

                                        if ($pedidoCliente) {
                                            $productosProcesados = $pedidoCliente->pedidos_productos->where(
                                                'procesado',
                                                '1',
                                            );
                                            $subtotalCliente = $pedidoCliente->subtotal;

                                            foreach ($productosProcesados as $producto) {
                                                $productoId = $producto->producto_id;
                                                $productoNombre = $producto->producto->nombre;

                                                if (in_array($productoId, [1, 2, 3])) {
                                                    $productoNombre = 'Pollo Primera';
                                                }

                                                if (!isset($productosCliente[$productoNombre])) {
                                                    $productosCliente[$productoNombre] = [
                                                        'peso_real' => 0,
                                                        'precio' => $producto->precio,
                                                        'subtotal' => 0,
                                                    ];
                                                }

                                                $productosCliente[$productoNombre]['peso_real'] += $producto->peso_real;
                                                $productosCliente[$productoNombre]['subtotal'] +=
                                                    $producto->costo_total;
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td class="sticky-left bg-white">
                                            <strong>{{ $cliente->nombres }} {{ $cliente->apellidos }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $cliente->ci }}</small>
                                            @if (!$pedidoCliente)
                                                <br>
                                                <span class="badge bg-warning">Sin pedido</span>
                                            @elseif($pedidoCliente->subtotal == 0)
                                                <br>
                                                <span class="badge bg-info">Sin deuda</span>
                                            @endif
                                        </td>
                                        @foreach ($productosUnicos as $productoNombre => $producto)
                                            @php
                                                $clienteProducto = $productosCliente[$productoNombre] ?? [
                                                    'peso_real' => 0,
                                                    'precio' => 0,
                                                    'subtotal' => 0,
                                                ];

                                                // Formatear números con separadores de miles
                                                $pesoFormateado =
                                                    $clienteProducto['peso_real'] > 0
                                                        ? number_format($clienteProducto['peso_real'], 2, ',', '.')
                                                        : '-';

                                                $precioFormateado =
                                                    $clienteProducto['precio'] > 0
                                                        ? number_format($clienteProducto['precio'], 2, ',', '.')
                                                        : '-';

                                                $subtotalFormateado =
                                                    $clienteProducto['subtotal'] > 0
                                                        ? number_format($clienteProducto['subtotal'], 2, ',', '.')
                                                        : '-';
                                            @endphp
                                            <td class="text-end">{{ $pesoFormateado }}</td>
                                            <td class="text-end">{{ $precioFormateado }}</td>
                                            <td class="text-end">{{ $subtotalFormateado }}</td>
                                        @endforeach
                                        @php
                                            // Formatear subtotal del cliente
                                            $subtotalClienteFormateado =
                                                $subtotalCliente > 0
                                                    ? number_format($subtotalCliente, 2, ',', '.')
                                                    : '-';
                                        @endphp
                                        <td class="text-end fw-bold sticky-right bg-white">
                                            {{ $subtotalClienteFormateado }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light sticky-bottom">
                                <tr>
                                    <th class="text-end sticky-left bg-light">TOTALES:</th>
                                    @foreach ($productosUnicos as $producto)
                                        @php
                                            // Formatear pesos con separadores de miles
                                            $pesoFormateado = number_format($producto['total_peso_real'], 2, ',', '.');
                                            // Formatear costos con separadores de miles
                                            $costoFormateado = number_format($producto['total_costo'], 2, ',', '.');
                                        @endphp
                                        <th class="text-end bg-light">{{ $pesoFormateado }} kg</th>
                                        <th class="text-end bg-light">-</th>
                                        <th class="text-end bg-light">Bs. {{ $costoFormateado }}</th>
                                    @endforeach
                                    @php
                                        // Formatear total general con separadores de miles
                                        $totalGeneralFormateado = number_format($totalGeneral, 2, ',', '.');
                                    @endphp
                                    <th class="text-end bg-light sticky-right">Bs. {{ $totalGeneralFormateado }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Resumen de ventas -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">Resumen de Ventas</h6>
                                </div>
                                <div class="card-body">
                                    @foreach ($productosUnicos as $producto)
                                        @php
                                            $pesoFormateado = number_format($producto['total_peso_real'], 2, ',', '.');
                                            $costoFormateado = number_format($producto['total_costo'], 2, ',', '.');
                                        @endphp
                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <strong>{{ $producto['nombre'] }}:</strong>
                                            </div>
                                            <div class="col-6 text-end">
                                                {{ $pesoFormateado }} kg - Bs. {{ $costoFormateado }}
                                            </div>
                                        </div>
                                    @endforeach
                                    <hr>
                                    <div class="row">
                                        <div class="col-6">
                                            <strong>Total General:</strong>
                                        </div>
                                        <div class="col-6 text-end">
                                            @php
                                                $totalGeneralFormateado = number_format($totalGeneral, 2, ',', '.');
                                            @endphp
                                            <strong>Bs. {{ $totalGeneralFormateado }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">Información General</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Localidad:</strong> {{ $localidadNombre }}</p>
                                    <p><strong>Código:</strong> {{ $codigo }}</p>
                                    <p><strong>Tipo:</strong> {{ $titulo }}</p>
                                    <p><strong>Total Clientes:</strong> {{ $todosClientes->count() }}</p>
                                    <p><strong>Clientes con pedido:</strong> {{ $pedidos->count() }}</p>
                                    <p><strong>Fecha:</strong>
                                        {{ $primerPedido ? $primerPedido->created_at->format('d/m/Y') : 'N/A' }}</p>
                                    <p><strong>Productos configurados:</strong> {{ count($productosUnicos) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i> Cerrar
                    </button>
                    <button type="button" class="btn btn-success" id="btnExportExcel{{ $tipoPedido }}">
                        <i class="ri-file-excel-line me-1"></i> Exportar Excel
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Modal para Reporte de Deudas -->
@if (!$pedidos->isEmpty())
    <div class="modal fade" id="modalDeudas{{ $tipoPedido }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-money-dollar-circle-line me-2"></i>
                        Reporte de Deudas - {{ $titulo }} - Código {{ $codigo }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @php
                        // Obtener la localidad para el nombre
                        $localidad = \App\Models\Localidade::find($localidadId);
                        $localidadNombre = $localidad ? $localidad->nombre : 'Localidad no encontrada';

                        // Obtener todos los clientes de la localidad para el reporte de deudas
                        $todosClientesDeuda = \App\Models\Cliente::where('localidade_id', $localidadId)
                            ->orderBy('orden', 'asc')
                            ->get();

                        // Obtener clientes con deuda (subtotal > 0) ORDENADOS por clientes.orden
                        $clientesConDeuda = \App\Models\Pedido::with('cliente')
                            ->where('pedidos.codigo', $codigo) // Especificar tabla pedidos
                            ->where('pedidos.pedido_dia', $tipoPedido) // Especificar tabla pedidos
                            ->when(request('fecha'), function ($query, $fecha) {
                                // Especificar tabla pedidos para created_at
                                return $query->whereDate('pedidos.created_at', $fecha);
                            })
                            ->when($localidadId, function ($query, $localidadId) {
                                // Especificar tabla pedidos para localidade_id
                                return $query->where('pedidos.localidade_id', $localidadId);
                            })
                            ->where('pedidos.subtotal', '>', 0) // Especificar tabla pedidos
                            ->join('clientes', 'pedidos.cliente_id', '=', 'clientes.id')
                            ->orderBy('clientes.orden', 'asc')
                            ->select('pedidos.*') // Solo columnas de pedidos
                            ->get();

                        $totalDeuda = $clientesConDeuda->sum('subtotal');
                    @endphp

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm"
                            id="tablaDeudas{{ $tipoPedido }}">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Cliente</th>
                                    <th width="150" class="text-end">Deuda (Bs.)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($clientesConDeuda as $index => $pedido)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $pedido->cliente->nombres }}
                                                {{ $pedido->cliente->apellidos }}</strong>
                                        </td>
                                        <td class="text-end fw-bold">Bs. {{ number_format($pedido->subtotal, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2" class="text-end">TOTAL DEUDA:</th>
                                    @php
                                        $totalDeudaFormateado = number_format($totalDeuda, 2, ',', '.');
                                    @endphp
                                    <th class="text-end">Bs. {{ $totalDeudaFormateado }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Resumen de deudas -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-danger text-white">
                                    <h6 class="mb-0">Resumen de Deudas</h6>
                                </div>
                                <div class="card-body">
                                    @php
                                        $totalDeudaFormateado = number_format($totalDeuda, 2, ',', '.');
                                        $deudaPromedio =
                                            $clientesConDeuda->count() > 0
                                                ? number_format($totalDeuda / $clientesConDeuda->count(), 2, ',', '.')
                                                : '0,00';
                                    @endphp
                                    <p><strong>Total Clientes con Deuda:</strong> {{ $clientesConDeuda->count() }}</p>
                                    <p><strong>Total Deuda:</strong> Bs. {{ $totalDeudaFormateado }}</p>
                                    <p><strong>Deuda Promedio:</strong> Bs. {{ $deudaPromedio }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">Información del Reporte</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Localidad:</strong> {{ $localidadNombre }}</p>
                                    <p><strong>Código:</strong> {{ $codigo }}</p>
                                    <p><strong>Tipo:</strong> {{ $titulo }}</p>
                                    <p><strong>Fecha:</strong>
                                        {{ $primerPedido ? $primerPedido->created_at->format('d/m/Y') : 'N/A' }}</p>
                                    <p><strong>Total Clientes:</strong> {{ $todosClientesDeuda->count() }}</p>
                                    <p><strong>Clientes con Deuda:</strong> {{ $clientesConDeuda->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i> Cerrar
                    </button>
                    <form id="formDeudasPdf{{ $tipoPedido }}"
                        action="{{ route('admin.pedidos.reporte-deudas-pdf') }}" method="POST" target="_blank">
                        @csrf
                        <input type="hidden" name="codigo" value="{{ $codigo }}">
                        <input type="hidden" name="tipo_pedido" value="{{ $tipoPedido }}">
                        <input type="hidden" name="localidad_id" value="{{ $localidadId }}">
                        @if (request('fecha'))
                            <input type="hidden" name="fecha" value="{{ request('fecha') }}">
                        @endif
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-printer-line me-1"></i> Generar PDF
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif


<!-- Modal para Reporte de Detalle de Pedidos -->
@if (!$pedidos->isEmpty())
    <div class="modal fade" id="modalDetallePedidos{{ $tipoPedido }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-file-list-line me-2"></i>
                        Reporte de Detalle de Pedidos - {{ $titulo }} - Código {{ $codigo }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Generar reporte de detalle de pedidos <strong>PROCESADOS</strong> para {{ $titulo }} -
                        Código {{ $codigo }}?</p>
                    <p>Este reporte generará un PDF con una hoja por cada cliente que tenga pedidos procesados.</p>
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        <strong>Información del Reporte:</strong><br>
                        - Solo incluye pedidos procesados<br>
                        - Una hoja por cliente (media carta)<br>
                        - Detalle completo con pesos reales y costos<br>
                        - Productos con tara consolidados como "Pollo Primera"<br>
                        - Estado de deuda actualizado
                    </div>
                    <div class="alert alert-warning">
                        <i class="ri-alert-line me-2"></i>
                        <strong>Nota:</strong> Los cálculos se basan en los datos procesados reales (peso real, tara,
                        costo total).
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i> Cancelar
                    </button>
                    <form id="formDetallePedidosPdf{{ $tipoPedido }}"
                        action="{{ route('admin.pedidos.reporte-detalle-pedidos-pdf') }}" method="POST"
                        target="_blank">
                        @csrf
                        <input type="hidden" name="codigo" value="{{ $codigo }}">
                        <input type="hidden" name="tipo_pedido" value="{{ $tipoPedido }}">
                        <input type="hidden" name="localidad_id" value="{{ $localidadId }}">
                        @if (request('fecha'))
                            <input type="hidden" name="fecha" value="{{ request('fecha') }}">
                        @endif
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-printer-line me-1"></i> Generar PDF
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif


<!-- Incluir modales para cada pedido -->
@foreach ($pedidos as $pedido)
    @include('admin.pedidos.partials.modal-registrar', ['pedido' => $pedido])
    @include('admin.pedidos.partials.modal-procesar', ['pedido' => $pedido])
    @include('admin.pedidos.partials.modal-detalle', ['pedido' => $pedido])
@endforeach

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Manejar el envío del formulario de generar pedidos
        const form = document.getElementById('formGenerarPedidos{{ $tipoPedido }}');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const tipoPedido = form.dataset.tipoPedido;
                const submitBtn = document.getElementById('btnGenerar' + tipoPedido);
                const originalText = submitBtn.innerHTML;

                // Mostrar estado de carga
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i> Generando...';

                // Realizar petición AJAX
                fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Mostrar mensaje de éxito en la parte superior principal
                            showMainAlert('success', data.msg);

                            // Recargar toda la página después de 1.5 segundos
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            // Mostrar mensaje de error
                            showMainAlert('error', data.msg);
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showMainAlert('error',
                            'Error al generar los pedidos. Por favor, intente nuevamente.');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            });
        }

        // Toast en esquina superior derecha
        function showMainAlert(type, message) {
            let container = document.getElementById('toast-container-global');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container-global';
                container.className = 'position-fixed top-0 end-0 p-3';
                container.style.zIndex = '1200';
                document.body.appendChild(container);
            }

            const bgClass = type === 'success' ? 'text-bg-success' : 'text-bg-danger';
            const icon = type === 'success' ? 'ri-checkbox-circle-line' : 'ri-error-warning-line';

            const toastEl = document.createElement('div');
            toastEl.className = `toast align-items-center ${bgClass} border-0`;
            toastEl.setAttribute('role', 'alert');
            toastEl.setAttribute('aria-atomic', 'true');
            toastEl.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="${icon} me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>`;

            container.appendChild(toastEl);
            const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
            toast.show();
            toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
        }

        // Cargar clientes inactivos al abrir el modal de generar pedidos
        const modalGenerar = document.getElementById('modalGenerarPedidos{{ $tipoPedido }}');
        if (modalGenerar) {
            modalGenerar.addEventListener('shown.bs.modal', function() {
                const alertInactivos = document.getElementById('alert-inactivos-{{ $tipoPedido }}');
                const listaInactivos = document.getElementById('lista-inactivos-{{ $tipoPedido }}');
                if (!alertInactivos || !listaInactivos) return;
                alertInactivos.style.display = 'none';
                listaInactivos.innerHTML = '';

                fetch('{{ url("/admin/clientes/inactivos") }}/{{ $localidadId }}')
                    .then(r => r.json())
                    .then(function(response) {
                        if (response.success && response.clientes && response.clientes.length > 0) {
                            listaInactivos.innerHTML = response.clientes.map(function(c) {
                                return '<li>' + c.ci + ' - ' + c.nombres + ' ' + c.apellidos + '</li>';
                            }).join('');
                            alertInactivos.style.display = 'block';
                        }
                    })
                    .catch(function() { alertInactivos.style.display = 'none'; });
            });
        }
    });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Función para exportar a Excel con formato correcto
        // En la función exportToExcel{{ $tipoPedido }}:

        function exportToExcel{{ $tipoPedido }}() {
            const table = document.getElementById('tablaVentas{{ $tipoPedido }}');

            // Crear una nueva tabla con los datos procesados
            const newTable = document.createElement('table');

            // Clonar la estructura de la tabla original
            const thead = table.querySelector('thead').cloneNode(true);
            const tbody = table.querySelector('tbody').cloneNode(true);
            const tfoot = table.querySelector('tfoot').cloneNode(true);

            newTable.appendChild(thead);
            newTable.appendChild(tbody);
            newTable.appendChild(tfoot);

            // Array para almacenar valores numéricos por columna
            const columnTotals = {};

            // Procesar TODAS las celdas para limpiar y formatear
            newTable.querySelectorAll('td, th').forEach(cell => {
                const originalText = cell.textContent.trim();
                const row = cell.parentElement;
                const cellIndex = cell.cellIndex;

                // 1. ESPECIAL: Para celdas de la columna del cliente (primer columna en el cuerpo)
                // FORZAR a que se trate como TEXTO, no como número
                if (cell.classList.contains('sticky-left') && row.closest('tbody')) {
                    // Extraer el contenido completo del cliente
                    const strongElement = cell.querySelector('strong');
                    let clienteText = '';

                    if (strongElement) {
                        clienteText = strongElement.textContent.trim();
                    } else {
                        // Si no hay strong, extraer solo el nombre (primera línea)
                        const lines = originalText.split('\n');
                        for (let line of lines) {
                            const trimmedLine = line.trim();
                            if (trimmedLine && !/^CI:/.test(trimmedLine) &&
                                !trimmedLine.includes('Entrega:') &&
                                !trimmedLine.includes('Sin pedido') &&
                                !trimmedLine.includes('Sin deuda')) {
                                clienteText = trimmedLine;
                                break;
                            }
                        }
                    }

                    // Establecer como texto puro, agregando un apóstrofe al inicio para que Excel lo trate como texto
                    cell.textContent = "'" + clienteText;
                    // Marcar esta celda como texto
                    cell.setAttribute('data-xlsx-type', 's'); // 's' para string en XLSX
                    return; // Salir del procesamiento para esta celda
                }

                // 2. Para celdas con números (Bs., kg, etc.) - EXCLUIR columnas de nombre
                if (originalText && originalText !== '-' && originalText !== '' &&
                    !cell.classList.contains('sticky-left') &&
                    !cell.classList.contains('sticky-right') &&
                    !row.closest('tfoot')) {

                    // Extraer el número del texto
                    let numberStr = originalText
                        .replace('Bs.', '')
                        .replace('kg', '')
                        .replace(/\s+/g, '')
                        .trim();

                    // Detectar si es un número con formato español
                    const spanishFormat = /^(\d{1,3}(?:\.\d{3})*|\d+)(,\d{1,2})?$/;

                    if (spanishFormat.test(numberStr) || !isNaN(parseFloat(numberStr.replace(',',
                            '.')))) {
                        // Convertir formato español a número
                        const cleanNumber = numberStr
                            .replace(/\./g, '')
                            .replace(',', '.');

                        const num = parseFloat(cleanNumber);

                        if (!isNaN(num)) {
                            // Guardar el valor numérico
                            cell.setAttribute('data-xlsx-value', num);
                            cell.setAttribute('data-xlsx-type', 'n'); // 'n' para number en XLSX

                            // Acumular para totales (excluir primera columna que es nombre)
                            if (cellIndex > 0) {
                                if (!columnTotals[cellIndex]) {
                                    columnTotals[cellIndex] = 0;
                                }
                                columnTotals[cellIndex] += num;
                            }

                            // Mantener el texto formateado para visualización
                            if (originalText.includes('Bs.')) {
                                cell.textContent = `Bs. ${formatNumberES(num, 2)}`;
                            } else if (originalText.includes('kg')) {
                                cell.textContent = `${formatNumberES(num, 2)} kg`;
                            } else {
                                cell.textContent = formatNumberES(num, 2);
                            }
                        }
                    } else {
                        // Si no es un número, tratar como texto
                        cell.setAttribute('data-xlsx-type', 's');
                    }
                }

                // 3. Para celdas en el tfoot (totales)
                if (row.closest('tfoot') && cellIndex > 0) {
                    // Calcular total de esta columna si existe
                    if (columnTotals[cellIndex] !== undefined) {
                        const total = columnTotals[cellIndex];
                        cell.setAttribute('data-xlsx-value', total);
                        cell.setAttribute('data-xlsx-type', 'n');

                        // Formatear con separadores de miles
                        let formattedTotal;
                        if (total >= 1000) {
                            formattedTotal = formatNumberES(total, 2);
                        } else {
                            formattedTotal = total.toFixed(2).replace('.', ',');
                        }

                        // Agregar unidad si corresponde
                        const headerCell = thead.rows[1].cells[cellIndex];
                        if (headerCell) {
                            const headerText = headerCell.textContent.trim();
                            if (headerText.includes('Precio') || headerText.includes('Bs.')) {
                                cell.textContent = `Bs. ${formattedTotal}`;
                            } else if (headerText.includes('Peso')) {
                                cell.textContent = `${formattedTotal} kg`;
                            } else {
                                cell.textContent = formattedTotal;
                            }
                        }
                    }
                }
            });

            // Crear el workbook
            const workbook = XLSX.utils.book_new();

            // Convertir la tabla a worksheet
            const worksheet = XLSX.utils.table_to_sheet(newTable);

            // Post-procesar el worksheet para asignar tipos correctos
            const range = XLSX.utils.decode_range(worksheet['!ref']);

            for (let row = range.s.r; row <= range.e.r; row++) {
                for (let col = range.s.c; col <= range.e.c; col++) {
                    const cellAddress = XLSX.utils.encode_cell({
                        r: row,
                        c: col
                    });
                    const cell = worksheet[cellAddress];

                    if (cell) {
                        // Buscar la celda original en la nueva tabla
                        const tableRow = newTable.rows[row];
                        if (tableRow) {
                            const tableCell = tableRow.cells[col];
                            if (tableCell) {
                                // Obtener el tipo de dato
                                const cellType = tableCell.getAttribute('data-xlsx-type');
                                const cellValue = tableCell.getAttribute('data-xlsx-value');

                                if (cellType === 's') {
                                    // Para texto, tomar el contenido completo
                                    let textValue = tableCell.textContent.trim();
                                    // Remover el apóstrofe agregado anteriormente
                                    if (textValue.startsWith("'")) {
                                        textValue = textValue.substring(1);
                                    }
                                    cell.v = textValue;
                                    cell.t = 's'; // Tipo string
                                    // Forzar formato de texto
                                    cell.z = '@';
                                } else if (cellType === 'n' && cellValue !== null) {
                                    // Para números
                                    const numValue = parseFloat(cellValue);
                                    if (!isNaN(numValue)) {
                                        cell.v = numValue;
                                        cell.t = 'n'; // Tipo numérico

                                        // Asignar formato adecuado
                                        const cellText = tableCell.textContent.trim();
                                        if (cellText.includes('Bs.')) {
                                            // Formato monetario con separador de miles y 2 decimales
                                            cell.z = '#,##0.00" Bs."';
                                        } else if (cellText.includes('kg')) {
                                            // Formato de peso con 2 decimales
                                            cell.z = '#,##0.00" kg"';
                                        } else {
                                            // Formato numérico con separador de miles y 2 decimales
                                            cell.z = '#,##0.00';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Ajustar el ancho de las columnas automáticamente
            worksheet['!cols'] = getColumnWidths(newTable);

            // Agregar worksheet al workbook
            XLSX.utils.book_append_sheet(workbook, worksheet, 'Ventas');

            // Agregar información adicional como metadata
            workbook.Props = {
                Title: `Reporte de Ventas - {{ $titulo }} - Código {{ $codigo }}`,
                Author: 'Sistema de Pedidos',
                CreatedDate: new Date()
            };

            // Generar nombre de archivo
            const fecha = new Date().toISOString().split('T')[0];
            const filename = `Ventas_{{ $titulo }}_Codigo{{ $codigo }}_${fecha}.xlsx`;

            // Descargar archivo
            XLSX.writeFile(workbook, filename);
        }

        // Función para formatear números en formato español con separadores de miles
        function formatNumberES(num, decimals = 2) {
            return num.toLocaleString('es-ES', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
        }

        // Función para formatear números en formato español
        function formatNumberES(num, decimals = 2) {
            return num.toLocaleString('es-ES', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
        }

        // Función para calcular anchos de columnas
        function getColumnWidths(table) {
            const colWidths = [];
            const rows = table.rows;

            if (rows.length === 0) return colWidths;

            // Inicializar con anchos basados en los encabezados
            const headerRow = rows[0];
            for (let col = 0; col < headerRow.cells.length; col++) {
                const headerCell = headerRow.cells[col];
                let maxWidth = headerCell.textContent.length;

                // Revisar todas las filas para encontrar el contenido más largo
                for (let row = 1; row < rows.length; row++) {
                    if (rows[row].cells[col]) {
                        const cellText = rows[row].cells[col].textContent;
                        maxWidth = Math.max(maxWidth, cellText.length);
                    }
                }

                // Ajustar el ancho mínimo y máximo
                // Para la columna del cliente, dar más ancho
                if (col === 0) { // Primera columna (cliente)
                    const adjustedWidth = Math.min(Math.max(maxWidth * 1.3, 25), 50);
                    colWidths.push({
                        wch: adjustedWidth
                    });
                } else {
                    const adjustedWidth = Math.min(Math.max(maxWidth * 1.1, 10), 20);
                    colWidths.push({
                        wch: adjustedWidth
                    });
                }
            }

            return colWidths;
        }

        // Asignar evento al botón de exportar
        const exportBtn = document.getElementById('btnExportExcel{{ $tipoPedido }}');
        if (exportBtn) {
            exportBtn.addEventListener('click', exportToExcel{{ $tipoPedido }});
        }

        // Función para formatear números en las tablas de ventas (para visualización)
        // Mejorar la función formatTableNumbers para los totales:
        function formatTableNumbers() {
            document.querySelectorAll(
                '#modalVentas{{ $tipoPedido }} .table td.text-end, #modalVentas{{ $tipoPedido }} .table th.text-end'
            ).forEach(cell => {
                const text = cell.textContent.trim();

                if (text && text !== '-') {
                    // Extraer número del texto
                    let numberStr = text
                        .replace('Bs.', '')
                        .replace('kg', '')
                        .replace(/\s+/g, '')
                        .trim();

                    // Intentar parsear como número
                    let num;

                    if (numberStr.includes(',') && numberStr.includes('.')) {
                        num = parseFloat(numberStr.replace(/\./g, '').replace(',', '.'));
                    } else if (numberStr.includes(',')) {
                        num = parseFloat(numberStr.replace(',', '.'));
                    } else {
                        num = parseFloat(numberStr);
                    }

                    if (!isNaN(num)) {
                        // Formatear con separadores de miles si es necesario
                        let formattedNum;
                        if (num >= 1000) {
                            formattedNum = formatNumberES(num, 2);
                        } else {
                            formattedNum = num.toFixed(2).replace('.', ',');
                        }

                        // Formatear según el tipo
                        if (text.includes('Bs.')) {
                            cell.textContent = 'Bs. ' + formattedNum;
                        } else if (text.includes('kg')) {
                            cell.textContent = formattedNum + ' kg';
                        } else if (num !== 0) {
                            cell.textContent = formattedNum;
                        }
                    }
                }
            });
        }

        // Aplicar formato cuando se abre el modal de ventas
        const modalVentas = document.getElementById('modalVentas{{ $tipoPedido }}');
        if (modalVentas) {
            modalVentas.addEventListener('shown.bs.modal', function() {
                formatTableNumbers();

                // Ajustar el ancho de las columnas fijas
                const stickyLefts = document.querySelectorAll(
                    '#modalVentas{{ $tipoPedido }} .sticky-left');
                const stickyRights = document.querySelectorAll(
                    '#modalVentas{{ $tipoPedido }} .sticky-right');

                if (stickyLefts.length > 0) {
                    const maxWidth = Math.max(...Array.from(stickyLefts).map(el => {
                        // Solo considerar el contenido del strong (nombre del cliente)
                        const strong = el.querySelector('strong');
                        return strong ? strong.textContent.length : el.textContent.length;
                    }));
                    stickyLefts.forEach(el => {
                        el.style.minWidth = (maxWidth * 8 + 20) + 'px'; // Ajuste proporcional
                    });
                }

                if (stickyRights.length > 0) {
                    const maxWidth = Math.max(...Array.from(stickyRights).map(el => el.scrollWidth));
                    stickyRights.forEach(el => {
                        el.style.minWidth = (maxWidth + 5) + 'px';
                    });
                }
            });
        }

    });
</script>
