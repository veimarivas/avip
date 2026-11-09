@if (
    $pedido->pedidos_productos->where('realizado', '1')->count() > 0 &&
        $pedido->pedidos_productos->where('procesado', '1')->count() == 0)
    <div class="modal fade" id="modalProcesarPedido{{ $pedido->id }}" tabindex="-1"
        aria-labelledby="modalProcesarPedidoLabel{{ $pedido->id }}" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProcesarPedidoLabel{{ $pedido->id }}">
                        <i class="ri-settings-line me-2"></i>
                        Procesar Pedido - {{ $pedido->cliente->nombres }} {{ $pedido->cliente->apellidos }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.pedidos.procesar-productos', $pedido->id) }}" method="POST">
                    @csrf
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

                        @php
                            $productosProcesar = $pedido->pedidos_productos->where('realizado', '1');
                            $productosConTara = $productosProcesar->filter(function ($pedidoProducto) {
                                return in_array($pedidoProducto->producto_id, [1, 2, 3]);
                            });
                            $productosSinTara = $productosProcesar->filter(function ($pedidoProducto) {
                                return !in_array($pedidoProducto->producto_id, [1, 2, 3]);
                            });
                        @endphp

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
                                            <span class="badge bg-info">
                                                Peso Pedido: {{ number_format($pedidoProducto->peso_pedido, 2) }} kg
                                            </span>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Una sola fila para ingresar los valores -->
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">N° Cajas</label>
                                        <input type="number" class="form-control" name="cajas_con_tara" value="0"
                                            min="0" step="1" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Peso Total (kg)</label>
                                        <input type="number" class="form-control" name="peso_con_tara" value="0"
                                            step="0.01" min="0" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Tara Total (kg)</label>
                                        <input type="number" class="form-control" name="tara_con_tara" value="0"
                                            step="0.01" min="0" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Precio (Bs./kg)</label>
                                        <input type="number" class="form-control" name="precio_con_tara"
                                            value="{{ $productosConTara->first()->precio ?? 0 }}" step="0.01"
                                            min="0" required>
                                    </div>
                                </div>

                                <!-- Campos ocultos para cada producto con tara -->
                                @foreach ($productosConTara as $pedidoProducto)
                                    <input type="hidden"
                                        name="pedido_productos[{{ $pedidoProducto->id }}][es_con_tara]" value="1">
                                @endforeach
                            </div>
                        @endif

                        <!-- Productos sin Tara - Filas individuales -->
                        @if ($productosSinTara->count() > 0)
                            <div class="mb-4">
                                <h6 class="border-bottom pb-2">Otros Productos</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Producto</th>
                                                <th width="120">Peso Pedido (kg)</th>
                                                <th width="120">Peso (kg)</th>
                                                <th width="120">Precio (Bs.)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($productosSinTara as $pedidoProducto)
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
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-info text-dark">
                                                            {{ number_format($pedidoProducto->peso_pedido, 2) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                            name="pedido_productos[{{ $pedidoProducto->id }}][peso]"
                                                            class="form-control form-control-sm"
                                                            value="{{ $pedidoProducto->peso }}" step="0.01"
                                                            min="0" required>
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                            name="pedido_productos[{{ $pedidoProducto->id }}][precio]"
                                                            class="form-control form-control-sm"
                                                            value="{{ $pedidoProducto->precio }}" step="0.01"
                                                            min="0" required>
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
                            - El sistema calculará automáticamente el Peso Real y Costo Total.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="ri-save-line me-1"></i> Procesar Pedido
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Calcular automáticamente el peso real para productos con tara
        const pesoConTaraInput = document.querySelector('input[name="peso_con_tara"]');
        const taraConTaraInput = document.querySelector('input[name="tara_con_tara"]');

        function actualizarPesoReal() {
            const peso = parseFloat(pesoConTaraInput.value) || 0;
            const tara = parseFloat(taraConTaraInput.value) || 0;
            const pesoReal = Math.max(0, peso - tara);

            // Mostrar el peso real calculado
            const pesoRealElement = document.getElementById('peso-real-con-tara');
            if (!pesoRealElement) {
                const div = document.createElement('div');
                div.id = 'peso-real-con-tara';
                div.className = 'mt-2 alert alert-warning';
                taraConTaraInput.parentNode.appendChild(div);
            }

            document.getElementById('peso-real-con-tara').innerHTML =
                `<strong>Peso Real Calculado:</strong> ${pesoReal.toFixed(2)} kg`;
        }

        if (pesoConTaraInput && taraConTaraInput) {
            pesoConTaraInput.addEventListener('input', actualizarPesoReal);
            taraConTaraInput.addEventListener('input', actualizarPesoReal);
            actualizarPesoReal(); // Ejecutar al cargar
        }
    });
</script>
