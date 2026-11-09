@if ($pedido->pedidos_productos->count() > 0)
    <div class="modal fade" id="modalRegistrarPedido{{ $pedido->id }}" tabindex="-1"
        aria-labelledby="modalRegistrarPedidoLabel{{ $pedido->id }}" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRegistrarPedidoLabel{{ $pedido->id }}">
                        <i class="ri-shopping-cart-line me-2"></i>
                        {{ $pedido->pedidos_productos->where('realizado', '1')->count() > 0 ? 'Modificar' : 'Registrar' }}
                        Pedido - {{ $pedido->cliente->nombres }} {{ $pedido->cliente->apellidos }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.pedidos.registrar-productos', $pedido->id) }}" method="POST">
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

                        <!-- Productos en 3 columnas -->
                        @php
                            $pedidoProductos = $pedido->pedidos_productos;
                            $chunkedProducts = $pedidoProductos->chunk(ceil($pedidoProductos->count() / 3));
                        @endphp

                        <div class="row">
                            @foreach ($chunkedProducts as $columnProducts)
                                <div class="col-md-4">
                                    @foreach ($columnProducts as $index => $pedidoProducto)
                                        <div class="producto-item mb-3 p-3 border rounded">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <label class="form-label fw-bold mb-0">
                                                    {{ $pedidoProducto->producto->nombre ?? 'N/A' }}
                                                </label>
                                                <span class="badge bg-light text-dark">
                                                    Bs. {{ number_format($pedidoProducto->precio, 2) }}
                                                </span>
                                            </div>
                                            <input type="hidden"
                                                name="pedido_productos[{{ $pedidoProducto->id }}][id]"
                                                value="{{ $pedidoProducto->id }}">
                                            <input type="hidden"
                                                name="pedido_productos[{{ $pedidoProducto->id }}][producto_id]"
                                                value="{{ $pedidoProducto->producto_id }}">

                                            <div class="input-group">
                                                <span class="input-group-text">Peso (kg)</span>
                                                <input type="number"
                                                    name="pedido_productos[{{ $pedidoProducto->id }}][peso_pedido]"
                                                    class="form-control" value="{{ $pedidoProducto->peso_pedido }}"
                                                    step="0.01" min="0" placeholder="0.00">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>

                        @if ($pedidoProductos->isEmpty())
                            <div class="text-center py-4">
                                <div class="text-muted">
                                    <i class="ri-inbox-line display-4"></i>
                                    <p class="mt-2">No hay productos configurados para este pedido</p>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i> Guardar Pesos
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
