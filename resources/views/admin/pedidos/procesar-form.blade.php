@extends('admin.dashboard')
@section('admin')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">
                        <i class="ri-settings-line me-2"></i>
                        Procesar Pedido - {{ $pedido->cliente->nombres }} {{ $pedido->cliente->apellidos }}
                    </h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.pedidos.index') }}">Pedidos</a></li>
                            <li class="breadcrumb-item active">Procesar Pedido</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-checkbox-circle-line me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Información del Cliente -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="info-box p-3 border rounded">
                                    <strong>Cliente:</strong><br>
                                    {{ $pedido->cliente->nombres }} {{ $pedido->cliente->apellidos }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box p-3 border rounded">
                                    <strong>CI:</strong><br>
                                    {{ $pedido->cliente->ci }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box p-3 border rounded">
                                    <strong>Localidad:</strong><br>
                                    <span class="badge"
                                        style="background-color: {{ $pedido->localidad->color }}; color: white;">
                                        {{ $pedido->localidad->nombre }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        @php
                            $productosProcesar = $pedido->pedidos_productos->where('realizado', '1');
                            $productosConTara = $productosProcesar->filter(function ($pedidoProducto) {
                                return in_array($pedidoProducto->producto_id, [1, 2, 3]);
                            });
                            $productosSinTara = $productosProcesar->filter(function ($pedidoProducto) {
                                return !in_array($pedidoProducto->producto_id, [1, 2, 3]);
                            });

                            // Verificar si el pedido ya ha sido procesado
                            $pedidoProcesado = $pedido->pedidos_productos->where('procesado', '1')->count() > 0;

                            // Calcular totales para productos con tara si ya está procesado
                            $cajasConTara = 0;
                            $pesoConTara = 0;
                            $taraConTara = 0;
                            $precioConTara = $productosConTara->first()->precio ?? 0;

                            if ($pedidoProcesado && $productosConTara->count() > 0) {
                                $primerProducto = $productosConTara->first();
                                $cajasConTara = $primerProducto->n_cajas;
                                $precioConTara = $primerProducto->precio;
                                $pesoConTara = $productosConTara->sum('peso');
                                $taraConTara = $productosConTara->sum('tara');
                            }
                        @endphp

                        <!-- FORMULARIO DE PROCESAMIENTO -->
                        <form action="{{ route('admin.pedidos.procesar-productos', $pedido->id) }}" method="POST">
                            @csrf

                            @if ($pedidoProcesado)
                                <div class="alert alert-info mb-3">
                                    <i class="ri-information-line me-2"></i>
                                    El pedido ya ha sido procesado. Puede modificar los datos y volver a procesar.
                                </div>
                            @endif

                            <!-- Productos con Tara (ID 1,2,3) - Una sola fila de entrada -->
                            @if ($productosConTara->count() > 0)
                                <div class="mb-4">
                                    <h6 class="border-bottom pb-2">Productos con Tara</h6>

                                    <!-- Lista de productos con sus pesos pedidos -->
                                    <div class="mb-3">
                                        @foreach ($productosConTara as $pedidoProducto)
                                            <div
                                                class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                <span>
                                                    <strong>{{ $pedidoProducto->producto->nombre ?? 'N/A' }}</strong>
                                                    <input type="hidden"
                                                        name="pedido_productos[{{ $pedidoProducto->id }}][id]"
                                                        value="{{ $pedidoProducto->id }}">
                                                    <input type="hidden"
                                                        name="pedido_productos[{{ $pedidoProducto->id }}][peso_pedido]"
                                                        value="{{ $pedidoProducto->peso_pedido }}">
                                                </span>
                                                <div class="alert alert-info peso-pedido-con-tara">
                                                    Peso Pedido:
                                                    {{ number_format($pedidoProducto->peso_pedido, 2) }} kg
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Una sola fila para ingresar los valores -->
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">N° Cajas</label>
                                            <input type="number" class="form-control" name="cajas_con_tara"
                                                value="{{ $cajasConTara }}" min="0" step="1" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Peso Total (kg)</label>
                                            <input type="number" class="form-control" id="peso_con_tara_input"
                                                name="peso_con_tara" value="{{ $pesoConTara }}" step="0.01"
                                                min="0" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Tara Total (kg)</label>
                                            <input type="number" class="form-control" id="tara_con_tara_input"
                                                name="tara_con_tara" value="{{ $taraConTara }}" step="0.01"
                                                min="0" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Precio (Bs./kg)</label>
                                            <input type="number" class="form-control" id="precio_con_tara_input"
                                                name="precio_con_tara" value="{{ $precioConTara }}" step="0.01"
                                                min="0" required>
                                        </div>
                                    </div>

                                    <!-- Campo oculto para el costo total redondeado de productos con tara -->
                                    <input type="hidden" name="costo_total_con_tara" id="costo_total_con_tara_hidden"
                                        value="{{ round(max(0, $pesoConTara - $taraConTara) * $precioConTara) }}">

                                    <!-- Mostrar peso real calculado y costo total -->
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <div id="peso-real-con-tara" class="alert alert-warning">
                                                @php
                                                    // Por cada 1 kg de tara, se descuentan 2 kg del peso total
                                                    $descuentoTara = 2 * $taraConTara;
                                                    $pesoRealConTara = max(0, $pesoConTara - $descuentoTara);
                                                    $costoTotalConTara = round(
                                                        max(0, $pesoConTara - $descuentoTara) * $precioConTara,
                                                    );
                                                @endphp
                                                <strong>Peso Real Calculado:</strong>
                                                {{ number_format($pesoRealConTara, 2) }} kg
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div id="costo-total-con-tara" class="alert alert-success">
                                                <strong>Costo Total:</strong> Bs.
                                                {{ number_format($costoTotalConTara, 0) }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Campos ocultos para cada producto con tara -->
                                    @foreach ($productosConTara as $pedidoProducto)
                                        <input type="hidden"
                                            name="pedido_productos[{{ $pedidoProducto->id }}][es_con_tara]"
                                            value="1">
                                        <!-- Enviar el costo total redondeado para cada producto con tara -->
                                        <input type="hidden"
                                            name="pedido_productos[{{ $pedidoProducto->id }}][costo_total]"
                                            id="costo_total_con_tara_{{ $pedidoProducto->id }}"
                                            value="{{ round($costoTotalConTara / ($productosConTara->count() ?: 1)) }}">
                                    @endforeach
                                </div>
                            @endif

                            <!-- Productos sin Tara - Filas individuales -->
                            @if ($productosSinTara->count() > 0)
                                <div class="mb-4">
                                    <h6 class="border-bottom pb-2">Otros Productos</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-sm procesar-otros-table">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Producto</th>
                                                    <th width="120">Peso Pedido (kg)</th>
                                                    <th width="120">Peso (kg)</th>
                                                    <th width="120">Precio (Bs.)</th>
                                                    <th width="120">Costo Total (Bs.)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($productosSinTara as $pedidoProducto)
                                                    @php
                                                        // Calcular el costo total redondeado a entero para mostrar
                                                        $peso = $pedidoProcesado ? $pedidoProducto->peso : 0;
                                                        $precio = $pedidoProcesado
                                                            ? $pedidoProducto->precio
                                                            : $pedidoProducto->precio;
                                                        $costoTotalMostrar = round($peso * $precio);
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $pedidoProducto->producto->nombre ?? 'N/A' }}</strong>
                                                            <input type="hidden"
                                                                name="pedido_productos[{{ $pedidoProducto->id }}][id]"
                                                                value="{{ $pedidoProducto->id }}">
                                                            <input type="hidden"
                                                                name="pedido_productos[{{ $pedidoProducto->id }}][peso_pedido]"
                                                                value="{{ $pedidoProducto->peso_pedido }}">
                                                            <input type="hidden"
                                                                name="pedido_productos[{{ $pedidoProducto->id }}][n_cajas]"
                                                                value="0">
                                                            <input type="hidden"
                                                                name="pedido_productos[{{ $pedidoProducto->id }}][tara]"
                                                                value="0">
                                                            <input type="hidden"
                                                                name="pedido_productos[{{ $pedidoProducto->id }}][es_con_tara]"
                                                                value="0">
                                                            <!-- Campo oculto para enviar el costo total redondeado a entero -->
                                                            <input type="hidden"
                                                                name="pedido_productos[{{ $pedidoProducto->id }}][costo_total]"
                                                                id="costo_total_{{ $pedidoProducto->id }}"
                                                                value="{{ $costoTotalMostrar }}">
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-secondary">
                                                                {{ number_format($pedidoProducto->peso_pedido, 2) }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <input type="number"
                                                                name="pedido_productos[{{ $pedidoProducto->id }}][peso]"
                                                                id="peso_{{ $pedidoProducto->id }}"
                                                                class="form-control form-control-sm peso-input"
                                                                data-producto-id="{{ $pedidoProducto->id }}"
                                                                value="{{ $peso }}" step="0.01"
                                                                min="0" required>
                                                        </td>
                                                        <td>
                                                            <input type="number"
                                                                name="pedido_productos[{{ $pedidoProducto->id }}][precio]"
                                                                id="precio_{{ $pedidoProducto->id }}"
                                                                class="form-control form-control-sm precio-input"
                                                                data-producto-id="{{ $pedidoProducto->id }}"
                                                                value="{{ $precio }}" step="0.01"
                                                                min="0" required>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-success"
                                                                id="costo_total_display_{{ $pedidoProducto->id }}">
                                                                {{ number_format($costoTotalMostrar, 0) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            <!-- Información adicional -->
                            <div class="alert alert-info mt-3">
                                <i class="ri-information-line me-2"></i>
                                <strong>Información:</strong><br>
                                - <strong>Productos con Tara (ID 1,2,3):</strong> Se ingresan valores globales en una sola
                                fila.<br>
                                - <strong>Otros productos:</strong> Cada producto se procesa individualmente.<br>
                                - El sistema calculará automáticamente el Peso Real y Costo Total.<br>
                                - <strong>Nota:</strong> Los Costos Totales se redondean a números enteros (sin decimales).
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                                            <i class="ri-arrow-left-line me-1"></i> Volver
                                        </a>
                                        <button type="submit" class="btn btn-success">
                                            <i class="ri-save-line me-1"></i>
                                            {{ $pedidoProcesado ? 'Actualizar Procesamiento' : 'Procesar Pedido' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- SECCIÓN DE DETALLES DEL PEDIDO PROCESADO (solo si está procesado) -->
                        @if ($pedidoProcesado)
                            <div class="row mt-5">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-success text-white">
                                            <h5 class="card-title mb-0">
                                                <i class="ri-checkbox-circle-line me-2"></i>
                                                Detalle del Pedido Procesado
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $productosProcesados = $pedido->pedidos_productos->where(
                                                    'procesado',
                                                    '1',
                                                );

                                                // Separar productos con tara (ID 1,2,3) y sin tara
                                                $productosConTaraProcesados = $productosProcesados->filter(function (
                                                    $item,
                                                ) {
                                                    return in_array($item->producto_id, [1, 2, 3]);
                                                });
                                                $productosSinTaraProcesados = $productosProcesados->filter(function (
                                                    $item,
                                                ) {
                                                    return !in_array($item->producto_id, [1, 2, 3]);
                                                });

                                                // Calcular totales para productos con tara (agrupados)
                                                $totalConTara = 0;
                                                $pesoConTaraProcesado = 0;
                                                $taraConTaraProcesado = 0;
                                                $pesoRealConTaraProcesado = 0;
                                                $costoTotalConTara = 0;

                                                if ($productosConTaraProcesados->count() > 0) {
                                                    $primerProductoConTara = $productosConTaraProcesados->first();
                                                    $pesoConTaraProcesado = $productosConTaraProcesados->sum('peso');
                                                    $taraConTaraProcesado = $productosConTaraProcesados->sum('tara');
                                                    $pesoRealConTaraProcesado = $productosConTaraProcesados->sum(
                                                        'peso_real',
                                                    );
                                                    $costoTotalConTara = $productosConTaraProcesados->sum(
                                                        'costo_total',
                                                    );
                                                }

                                                // Calcular total general sumando los valores ya redondeados
                                                $total =
                                                    $costoTotalConTara +
                                                    $productosSinTaraProcesados->sum('costo_total');
                                            @endphp

                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Producto</th>
                                                            <th width="100" class="text-center">N° Cajas</th>
                                                            <th width="120" class="text-center">Peso (kg)</th>
                                                            <th width="120" class="text-center">Tara (kg)</th>
                                                            <th width="120" class="text-center">Peso Real (kg)</th>
                                                            <th width="120" class="text-center">Precio (Bs./kg)</th>
                                                            <th width="120" class="text-center">Costo Total (Bs.)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- Mostrar productos con tara (ID 1,2,3) agrupados en una sola fila -->
                                                        @if ($productosConTaraProcesados->count() > 0)
                                                            @php
                                                                $costoTotalConTaraRedondeado = round(
                                                                    $costoTotalConTara,
                                                                );
                                                            @endphp
                                                            <tr>
                                                                <td>
                                                                    <strong>Pollo Primera</strong>
                                                                </td>
                                                                <td class="text-center">
                                                                    {{ $primerProductoConTara->n_cajas ?? 0 }}
                                                                </td>
                                                                <td class="text-end">
                                                                    {{ number_format($pesoConTaraProcesado, 2) }}
                                                                </td>
                                                                <td class="text-end">
                                                                    {{ number_format($taraConTaraProcesado, 2) }}
                                                                </td>
                                                                <td class="text-end">
                                                                    {{ number_format($pesoRealConTaraProcesado, 2) }}
                                                                </td>
                                                                <td class="text-end">
                                                                    {{ number_format($primerProductoConTara->precio ?? 0, 2) }}
                                                                </td>
                                                                <td class="text-end">
                                                                    {{ number_format($costoTotalConTaraRedondeado, 0) }}
                                                                </td>
                                                            </tr>
                                                        @endif

                                                        <!-- Mostrar productos sin tara individualmente -->
                                                        @foreach ($productosSinTaraProcesados as $pedidoProducto)
                                                            @php
                                                                $costoTotalIndividualRedondeado = round(
                                                                    $pedidoProducto->costo_total,
                                                                );
                                                            @endphp
                                                            <tr>
                                                                <td>
                                                                    <strong>{{ $pedidoProducto->producto->nombre ?? 'N/A' }}</strong>
                                                                </td>
                                                                <td class="text-center">{{ $pedidoProducto->n_cajas }}
                                                                </td>
                                                                <td class="text-end">
                                                                    {{ number_format($pedidoProducto->peso, 2) }}</td>
                                                                <td class="text-end">
                                                                    {{ number_format($pedidoProducto->tara, 2) }}</td>
                                                                <td class="text-end">
                                                                    {{ number_format($pedidoProducto->peso_real, 2) }}</td>
                                                                <td class="text-end">
                                                                    {{ number_format($pedidoProducto->precio, 2) }}</td>
                                                                <td class="text-end">
                                                                    {{ number_format($costoTotalIndividualRedondeado, 0) }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr style="background-color: #f8f9fa; font-weight: bold;">
                                                            <td colspan="6" class="text-end">Total:</td>
                                                            <td class="text-end">{{ number_format(round($total), 0) }}
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>

                                            <!-- Información adicional del pedido -->
                                            <div class="row mt-3">
                                                <div class="col-md-4">
                                                    <div class="info-box p-3 border rounded">
                                                        <strong>Subtotal del Pedido:</strong><br>
                                                        Bs. {{ number_format($pedido->subtotal, 0) }}
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="info-box p-3 border rounded">
                                                        <strong>Estado:</strong><br>
                                                        @if ($pedido->pendiente > 0)
                                                            <span class="badge bg-warning">Pendiente de Pago</span>
                                                        @else
                                                            <span class="badge bg-success">Pagado</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="info-box p-3 border rounded">
                                                        <strong>Fecha de Procesamiento:</strong><br>
                                                        {{ $pedido->updated_at->format('d/m/Y H:i') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- BOTÓN PARA GENERAR DEUDA SI ES NECESARIO -->
                            @if ($pedido->subtotal == 0)
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body text-center">

                                                <form action="{{ route('admin.pedidos.generar-deuda', $pedido->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    <p class="text-muted mb-3">
                                                        El pedido ha sido procesado pero no se ha generado la deuda.
                                                        Haga clic en el botón para generar el subtotal.
                                                    </p>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="ri-calculator-line me-1"></i> Generar Deuda
                                                    </button>
                                                </form>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @elseif (Auth::guard('web')->user()->can('pedidos.generar.deuda'))
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body text-center">

                                                <form action="{{ route('admin.pedidos.generar-deuda', $pedido->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    <p class="text-muted mb-3">
                                                        El pedido ha sido procesado pero no se ha generado la deuda.
                                                        Haga clic en el botón para generar el subtotal.
                                                    </p>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="ri-calculator-line me-1"></i> Generar Deuda
                                                    </button>
                                                </form>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Mantener visible la columna Producto al desplazar la tabla horizontalmente */
        .procesar-otros-table thead th:first-child,
        .procesar-otros-table tbody td:first-child {
            position: sticky;
            left: 0;
            z-index: 3;
            min-width: 190px;
            max-width: 240px;
            background: #ffffff;
            box-shadow: 4px 0 8px -6px rgba(15, 36, 28, 0.45);
        }

        .procesar-otros-table thead th:first-child {
            z-index: 4;
            background: #f6f9f4;
        }

        .procesar-otros-table tbody tr:hover td:first-child {
            background: #f4f8f2;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Función para redondear a número entero (sin decimales)
            function redondearEntero(valor) {
                return Math.round(valor);
            }

            // Calcular automáticamente el peso real y costo total para productos con tara
            const pesoConTaraInput = document.getElementById('peso_con_tara_input');
            const taraConTaraInput = document.getElementById('tara_con_tara_input');
            const precioConTaraInput = document.getElementById('precio_con_tara_input');

            function actualizarProductosConTara() {
                const peso = parseFloat(pesoConTaraInput.value) || 0;
                const tara = parseFloat(taraConTaraInput.value) || 0;
                const precio = parseFloat(precioConTaraInput.value) || 0;
                // Por cada 1 kg de tara, se descuentan 2 kg del peso total
                const descuentoTara = 2 * tara;
                const pesoReal = Math.max(0, peso - descuentoTara);
                const costoTotal = redondearEntero(pesoReal * precio);

                // Mostrar el peso real calculado
                const pesoRealElement = document.getElementById('peso-real-con-tara');
                if (pesoRealElement) {
                    pesoRealElement.innerHTML = `<strong>Peso Real Calculado:</strong> ${pesoReal.toFixed(2)} kg`;
                }

                // Mostrar el costo total calculado (entero)
                const costoTotalElement = document.getElementById('costo-total-con-tara');
                if (costoTotalElement) {
                    costoTotalElement.innerHTML =
                        `<strong>Costo Total:</strong> Bs. ${costoTotal.toLocaleString()}`;
                }

                // Actualizar campo oculto
                const costoTotalHidden = document.getElementById('costo_total_con_tara_hidden');
                if (costoTotalHidden) {
                    costoTotalHidden.value = costoTotal;
                }

                // Actualizar costo total para cada producto con tara (distribuir proporcionalmente)
                @if ($productosConTara->count() > 0)
                    const cantidadProductos = {{ $productosConTara->count() }};
                    const costoPorProducto = redondearEntero(costoTotal / cantidadProductos);

                    @foreach ($productosConTara as $pedidoProducto)
                        const costoInput{{ $pedidoProducto->id }} = document.getElementById(
                            'costo_total_con_tara_{{ $pedidoProducto->id }}');
                        if (costoInput{{ $pedidoProducto->id }}) {
                            costoInput{{ $pedidoProducto->id }}.value = costoPorProducto;
                        }
                    @endforeach
                @endif
            }

            // Calcular automáticamente el costo total para productos sin tara
            function actualizarCostoTotal(productoId) {
                const pesoInput = document.getElementById('peso_' + productoId);
                const precioInput = document.getElementById('precio_' + productoId);
                const costoTotalInput = document.getElementById('costo_total_' + productoId);
                const costoDisplay = document.getElementById('costo_total_display_' + productoId);

                if (pesoInput && precioInput && costoTotalInput && costoDisplay) {
                    const peso = parseFloat(pesoInput.value) || 0;
                    const precio = parseFloat(precioInput.value) || 0;
                    const costoTotal = redondearEntero(peso * precio);

                    // Actualizar campo oculto
                    costoTotalInput.value = costoTotal;

                    // Actualizar display (mostrar sin decimales)
                    costoDisplay.textContent = costoTotal.toLocaleString();
                    costoDisplay.className = 'badge bg-success';
                }
            }

            // Event listeners para productos con tara
            if (pesoConTaraInput && taraConTaraInput && precioConTaraInput) {
                pesoConTaraInput.addEventListener('input', actualizarProductosConTara);
                taraConTaraInput.addEventListener('input', actualizarProductosConTara);
                precioConTaraInput.addEventListener('input', actualizarProductosConTara);
                // Ejecutar al cargar
                actualizarProductosConTara();
            }

            // Event listeners para productos sin tara
            document.querySelectorAll('.peso-input').forEach(input => {
                input.addEventListener('input', function() {
                    const productoId = this.getAttribute('data-producto-id');
                    actualizarCostoTotal(productoId);
                });
            });

            document.querySelectorAll('.precio-input').forEach(input => {
                input.addEventListener('input', function() {
                    const productoId = this.getAttribute('data-producto-id');
                    actualizarCostoTotal(productoId);
                });
            });

            // Ejecutar al cargar para productos sin tara
            @foreach ($productosSinTara as $pedidoProducto)
                actualizarCostoTotal({{ $pedidoProducto->id }});
            @endforeach
        });
    </script>
@endpush
