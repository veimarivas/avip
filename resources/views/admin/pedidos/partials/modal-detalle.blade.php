@if ($pedido->pedidos_productos->where('procesado', '1')->count() > 0)
    <div class="modal fade" id="modalDetalleProcesado{{ $pedido->id }}" tabindex="-1"
        aria-labelledby="modalDetalleProcesadoLabel{{ $pedido->id }}" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="modalDetalleProcesadoLabel{{ $pedido->id }}">
                            <i class="ri-eye-line me-2"></i>Detalle del pedido procesado
                        </h5>
                        <small class="text-muted">
                            {{ $pedido->cliente->nombres }} {{ $pedido->cliente->apellidos }}
                            · Código {{ $pedido->codigo ?? $pedido->id }}
                        </small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Información del Cliente -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="info-box">
                                <strong>Cliente:</strong><br>
                                {{ $pedido->cliente->nombres }} {{ $pedido->cliente->apellidos }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <strong>CI:</strong><br>
                                {{ $pedido->cliente->ci }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <strong>Localidad:</strong><br>
                                <span class="badge"
                                    style="background-color: {{ $pedido->localidad->color }}; color: white;">
                                    {{ $pedido->localidad->nombre }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Detalle de Productos Procesados -->
                    @php
                        $productosProcesados = $pedido->pedidos_productos->where('procesado', '1');

                        // Separar productos con ID 1,2,3 y otros productos
                        $productosConTara = $productosProcesados->filter(function ($producto) {
                            return in_array($producto->producto_id, [1, 2, 3]);
                        });

                        $otrosProductos = $productosProcesados->filter(function ($producto) {
                            return !in_array($producto->producto_id, [1, 2, 3]);
                        });

                        // Calcular totales para productos con tara
                        $totalPesoConTara = $productosConTara->sum('peso');
                        $totalTaraConTara = $productosConTara->sum('tara');
                        $totalPesoRealConTara = $productosConTara->sum('peso_real');
                        $totalCostoConTara = $productosConTara->sum('costo_total');

                        // Obtener un producto de referencia para n_cajas y precio
                        $productoReferencia = $productosConTara->first();
                        $nCajasReferencia = $productoReferencia ? $productoReferencia->n_cajas : 0;
                        $precioReferencia = $productoReferencia ? $productoReferencia->precio : 0;

                        // Totales generales
                        $totalGeneral = $productosProcesados->sum('costo_total');
                    @endphp

                    <div class="row g-2 mb-3">
                        <div class="col-6 col-lg-3">
                            <div class="pedido-modal-kpi">
                                <span>Total general</span>
                                <strong>Bs. {{ number_format($totalGeneral, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="pedido-modal-kpi">
                                <span>Peso real</span>
                                <strong>{{ number_format($productosProcesados->sum('peso_real'), 2) }} kg</strong>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="pedido-modal-kpi">
                                <span>Cajas</span>
                                <strong>{{ $nCajasReferencia }}</strong>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="pedido-modal-kpi">
                                <span>Productos</span>
                                <strong>{{ $productosProcesados->count() }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-centered align-middle report-table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>N° Cajas</th>
                                    <th>Peso (kg)</th>
                                    <th>Tara (kg)</th>
                                    <th>Peso Real (kg)</th>
                                    <th>Precio (Bs.)</th>
                                    <th>Costo Total (Bs.)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Fila consolidada para productos con tara (ID 1,2,3) -->
                                @if ($productosConTara->count() > 0)
                                    <tr class="table-info">
                                        <td>
                                            <strong>Pollo Primera</strong>
                                            <br>
                                            <small class="text-muted">
                                                Incluye:
                                                @foreach ($productosConTara as $producto)
                                                    {{ $producto->producto->nombre ?? 'N/A' }}{{ !$loop->last ? ', ' : '' }}
                                                @endforeach
                                            </small>
                                        </td>
                                        <td>{{ $nCajasReferencia }}</td>
                                        <td>{{ number_format($totalPesoConTara, 2) }}</td>
                                        <td>{{ number_format($totalTaraConTara, 2) }}</td>
                                        <td>{{ number_format($totalPesoRealConTara, 2) }}</td>
                                        <td>Bs. {{ number_format($precioReferencia, 2) }}</td>
                                        <td>Bs. {{ number_format($totalCostoConTara, 2) }}</td>
                                    </tr>
                                @endif

                                <!-- Otros productos (individuales) -->
                                @foreach ($otrosProductos as $producto)
                                    <tr>
                                        <td>{{ $producto->producto->nombre ?? 'N/A' }}</td>
                                        <td>{{ $producto->n_cajas }}</td>
                                        <td>{{ number_format($producto->peso, 2) }}</td>
                                        <td>{{ in_array($producto->producto_id, [1, 2, 3]) ? number_format($producto->tara, 2) : 'N/A' }}
                                        </td>
                                        <td>{{ number_format($producto->peso_real, 2) }}</td>
                                        <td>Bs. {{ number_format($producto->precio, 2) }}</td>
                                        <td>Bs. {{ number_format($producto->costo_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="6" class="text-end"><strong>Total General:</strong></td>
                                    <td><strong>Bs. {{ number_format($totalGeneral, 2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Resumen de productos con tara -->
                    @if ($productosConTara->count() > 0)
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">Detalle Pollo Primera</h6>
                                    </div>
                                    <div class="card-body">
                                        @foreach ($productosConTara as $producto)
                                            <div class="row mb-2">
                                                <div class="col-6">
                                                    <strong>{{ $producto->producto->nombre ?? 'N/A' }}:</strong>
                                                </div>
                                                <div class="col-6">
                                                    Peso Real: {{ number_format($producto->peso_real, 2) }} kg
                                                </div>
                                            </div>
                                        @endforeach
                                        <hr>
                                        <div class="row">
                                            <div class="col-6">
                                                <strong>Total Consolidado:</strong>
                                            </div>
                                            <div class="col-6">
                                                {{ number_format($totalPesoRealConTara, 2) }} kg
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i> Cerrar
                    </button>
                    @if ($pedido->subtotal == 0)
                        <form action="{{ route('admin.pedidos.generar-deuda', $pedido->id) }}" method="POST"
                            class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                <i class="ri-money-dollar-circle-line me-1"></i> Generar Deuda
                            </button>
                        </form>
                    @else
                        <button type="button" class="btn btn-success" disabled>
                            Deuda Generada
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
