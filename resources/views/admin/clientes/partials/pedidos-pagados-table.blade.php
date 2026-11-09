<div class="table-responsive">
    <table class="table table-hover table-centered align-middle report-table mb-0">
        <thead class="table-light">
            <tr>
                <th>Pedido / Fecha</th>
                <th class="d-none d-md-table-cell">Tipo</th>
                <th class="text-end d-none d-md-table-cell">Subtotal</th>
                <th class="text-end d-none d-md-table-cell">Pagado</th>
                <th class="text-center cell-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pedidosPagados as $pedido)
                @php
                    $pagadoBs = $pedido->pagado_bs ?? 0;
                    $descuentoBs = $pedido->descuento_bs ?? 0;
                    $pagosPedido = $pedido->pagos ?? collect();

                    $tipoPedido = 'Regular';
                    $badgeClass = 'bg-primary';

                    if ($pedido->pedidos_productos && $pedido->pedidos_productos->count() > 0) {
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
                <tr class="table-success">
                    <td>
                        <div class="fw-semibold">{{ $pedido->codigo ?? 'N/A' }}</div>
                        <small class="text-muted d-block">{{ $pedido->created_at->format('d/m/Y H:i') }}</small>
                        <small class="d-block text-muted d-md-none">
                            Pagado: Bs. {{ number_format($pagadoBs, 2) }}
                        </small>
                    </td>
                    <td class="d-none d-md-table-cell">
                        <span class="badge {{ $badgeClass }}">{{ $tipoPedido }}</span>
                    </td>
                    <td class="text-end d-none d-md-table-cell">
                        <span class="fw-medium">Bs. {{ number_format($pedido->subtotal, 2) }}</span>
                    </td>
                    <td class="text-end d-none d-md-table-cell">
                        <span class="text-success">Bs. {{ number_format($pagadoBs, 2) }}</span>
                        @if ($descuentoBs > 0)
                            <small class="d-block text-warning">
                                Desc. Bs. {{ number_format($descuentoBs, 2) }}
                            </small>
                        @endif
                    </td>
                    <td class="text-center cell-actions">
                        <div class="btn-group" role="group">
                            @if ($pagosPedido->count() > 0)
                                <div class="dropdown">
                                    <button class="btn btn-info btn-sm dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown" title="Recibos del pedido">
                                        <i class="ri-receipt-line"></i>
                                        <span class="d-none d-xl-inline ms-1">
                                            {{ $pagosPedido->count() }}
                                        </span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @foreach ($pagosPedido as $pago)
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.recibos.ver', $pago->id) }}"
                                                    target="_blank">
                                                    {{ $pago->recibo }} - Bs. {{ number_format($pago->pago_bs, 2) }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <button type="button" class="btn btn-warning btn-sm verProductosBtn"
                                data-pedido-id="{{ $pedido->id }}" title="Ver productos del pedido">
                                <i class="ri-eye-line"></i>
                                <span class="d-none d-xl-inline ms-1">Productos</span>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <div class="avatar-lg mx-auto mb-4">
                            <div class="avatar-title bg-light text-muted rounded-circle">
                                <i class="ri-inbox-line fs-24"></i>
                            </div>
                        </div>
                        <h5 class="text-muted">No hay pedidos pagados</h5>
                        <p class="text-muted">No se encontraron pedidos pagados para este cliente.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($pedidosPagados->hasPages())
    <div class="tab-pagination d-flex justify-content-end mt-3" data-tab="pagados">
        {{ $pedidosPagados->links('pagination::bootstrap-5') }}
    </div>
@endif
