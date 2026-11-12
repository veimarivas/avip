<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">Lista de Pedidos {{ $titulo }} ({{ $pedidos->count() }} registros)</h6>

    <!-- Buscador para esta tabla específica -->
    <form method="GET" class="d-flex gap-2">
        <!-- Mantener todos los parámetros de filtro existentes -->
        <input type="hidden" name="fecha" value="{{ request('fecha') }}">
        <input type="hidden" name="tipo_pedido" value="{{ $tipoPedido }}">

        <div class="input-group input-group-sm" style="width: 300px;">
            <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, apellido o CI..."
                value="{{ request('search') }}">
            <button class="btn btn-outline-primary" type="submit">
                <i class="ri-search-line"></i>
            </button>
            @if (request('search'))
                <a href="{{ request()->url() }}?fecha={{ request('fecha') }}&tipo_pedido={{ $tipoPedido }}"
                    class="btn btn-outline-secondary">
                    <i class="ri-close-line"></i>
                </a>
            @endif
        </div>
    </form>
</div>

@if ($pedidos->isEmpty())
    <div class="text-center py-4">
        <div class="text-muted">
            <i class="ri-shopping-cart-line display-4"></i>
            <p class="mt-2">No se encontraron pedidos {{ $titulo }} con productos registrados</p>
            <p class="small text-info">
                Fecha: {{ $fecha }} | Tipo: {{ $tipoPedido }} ({{ $titulo }})
            </p>
        </div>
    </div>
@else
    <div class="table-responsive" style="max-height: 450px; overflow-x: auto;">
        <table class="table table-bordered table-hover table-sm">
            <thead class="table-light sticky-top">
                <tr>
                    <th class="bg-light" width="50">#</th>
                    <th class="sticky-left cliente-col" width="200">Cliente</th>
                    <th class="sticky-right bg-light" width="150">Acciones</th>
                    @foreach ($productos as $producto)
                        <th class="text-center" width="120">
                            {{ $producto->nombre }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($pedidos as $index => $pedido)
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
                        <td class="bg-white">{{ $index + 1 }}</td>
                        <td class="sticky-left cliente-col">
                            <strong>{{ $pedido->cliente->nombres }} {{ $pedido->cliente->apellidos }}</strong>
                            <br>
                            <small class="text-muted">{{ $pedido->cliente->ci }}</small>
                            <br>
                            <small class="text-info">
                                <i class="ri-map-pin-line"></i>
                                {{ $pedido->localidad->nombre }}
                            </small>
                        </td>

                        <td class="sticky-right bg-white">
                            @if ($pedidoProcesado)
                                <!-- Botón Ver Detalle - SIEMPRE DISPONIBLE -->
                                <button type="button" class="btn btn-sm btn-info mb-1" data-bs-toggle="modal"
                                    data-bs-target="#modalDetalleProcesado{{ $pedido->id }}">
                                    <i class="ri-eye-line"></i> Ver Detalle
                                </button>
                                <!-- BOTÓN NUEVO: Imprimir reporte individual -->
                                <a href="{{ route('admin.pedidos.reporte-individual-pdf', $pedido->id) }}"
                                    class="btn btn-sm btn-secondary mb-1" target="_blank"
                                    title="Imprimir reporte individual">
                                    <i class="ri-printer-line"></i> Imprimir
                                </a>
                                
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
                                @if (Auth::guard('web')->user()->can('pedidos.generar.deuda'))
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
                                            <span class="text-muted small">Fecha vencida</span>
                                            <br>
                                            <small
                                                class="text-danger">{{ \Carbon\Carbon::parse($fechaEntrega)->format('d/m/Y') }}</small>
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
                            @endif
                        </td>
                        @foreach ($productos as $producto)
                            @php
                                $pedidoProducto = $pedido->pedidos_productos->firstWhere('producto_id', $producto->id);
                                $pesoPedido = $pedidoProducto ? $pedidoProducto->peso_pedido : 0;
                            @endphp
                            <td class="text-center">
                                @if ($pesoPedido > 0)
                                    <span class="badge bg-info text-dark">
                                        {{ number_format($pesoPedido, 2) }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach

                <!-- Fila de totales -->
                <tr style="background-color: #f8f9fa; font-weight: bold;">
                    <td class="sticky-left bg-light" style="text-align: center; white-space: nowrap;" title="Total">Total</td>
                    <td class="bg-light" colspan="2"></td>
                    @foreach ($productos as $producto)
                        @php
                            $totalProducto = 0;
                            foreach ($pedidos as $pedido) {
                                $pedidoProducto = $pedido->pedidos_productos->firstWhere('producto_id', $producto->id);
                                if ($pedidoProducto) {
                                    $totalProducto += $pedidoProducto->peso_pedido;
                                }
                            }
                        @endphp
                        <td class="text-center">
                            @if ($totalProducto > 0)
                                <span class="badge bg-dark">
                                    {{ number_format($totalProducto, 2) }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                    @endforeach
                </tr>

                <!-- ✅ NUEVA FILA: Cantidad de Cajas -->
                <tr style="background-color: #e9ecef; font-weight: bold; border-top: 2px solid #dee2e6;">
                    <td class="sticky-left bg-light" style="text-align: center; white-space: nowrap;"
                        title="Cantidad de Cajas">Cajas</td>
                    <td class="bg-light" colspan="2"></td>
                    @foreach ($productos as $producto)
                        @php
                            $totalProducto = 0;
                            foreach ($pedidos as $pedido) {
                                $pedidoProducto = $pedido->pedidos_productos->firstWhere('producto_id', $producto->id);
                                if ($pedidoProducto) {
                                    $totalProducto += $pedidoProducto->peso_pedido;
                                }
                            }

                            // Calcular cantidad de cajas: totalProducto dividido por n_cajas del producto
                            // Usar n_cajas del producto (debe estar en la relación)
                            $cantidadCajas = 0;
                            if ($totalProducto > 0 && isset($producto->n_caja) && $producto->n_caja > 0) {
                                $cantidadCajas = round($totalProducto / $producto->n_caja);
                            }
                        @endphp
                        <td class="text-center">
                            @if ($cantidadCajas > 0)
                                <span class="badge bg-info" style="color: #000;">
                                    {{ number_format($cantidadCajas, 2) }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Mostrar contador de registros -->
    <div class="mt-3 text-muted">
        <small>Total de registros: {{ $pedidos->count() }}</small>
    </div>
@endif

<!-- Incluir modales para cada pedido -->
@foreach ($pedidos as $pedido)
    @include('admin.pedidos.partials.modal-detalle', ['pedido' => $pedido])
@endforeach

<style>
    .table-responsive {
        overflow-x: auto;
    }

    .sticky-left {
        position: sticky;
        left: 0;
        z-index: 2;
        background-color: inherit;
    }

    .cliente-col {
        background-color: #eef6f0 !important;
        box-shadow: 2px 0 6px -4px rgba(15, 36, 28, 0.35);
    }

    thead th.cliente-col {
        background-color: #e6f1e9 !important;
        z-index: 4 !important;
    }

    thead th.sticky-right {
        z-index: 4 !important;
    }

    .sticky-right {
        position: sticky;
        right: 0;
        z-index: 2;
        background-color: inherit;
    }

    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 3;
    }

    thead th {
        vertical-align: middle;
    }

    .badge.bg-info.text-dark {
        background-color: #d1ecf1 !important;
        color: #0c5460 !important;
        font-weight: 500;
        padding: 0.25em 0.4em;
    }

    .badge.bg-dark {
        background-color: #343a40 !important;
        color: white !important;
        font-weight: 500;
        padding: 0.25em 0.4em;
    }

    /* En la sección de estilos de la vista */
    .badge.bg-info {
        background-color: #0dcaf0 !important;
        color: #000 !important;
        font-weight: 500;
        padding: 0.25em 0.4em;
    }

    .badge.bg-dark {
        background-color: #343a40 !important;
        color: white !important;
        font-weight: 500;
        padding: 0.25em 0.4em;
    }

    /* Para diferenciar las filas de total y cajas */
    tr:last-child {
        border-bottom: 3px solid #495057;
    }
</style>
