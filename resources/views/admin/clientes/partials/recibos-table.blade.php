<div class="table-responsive">
    <table class="table table-hover table-centered align-middle report-table mb-0">
        <thead class="table-light">
            <tr>
                <th>Recibo / Fecha</th>
                <th class="d-none d-md-table-cell">Pedido / Tipo</th>
                <th class="text-end">Monto</th>
                <th class="d-none d-lg-table-cell">Cobrador</th>
                <th class="text-center cell-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pagos as $pago)
                @php
                    $tipoClass = match ($pago->tipo) {
                        'efectivo' => 'success',
                        'qr' => 'primary',
                        'parcial' => 'warning',
                        default => 'secondary',
                    };
                    $pedidoCodigo = optional($pago->pedidos->first())->codigo ?? 'N/A';
                @endphp
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $pago->recibo }}</div>
                        <small class="text-muted d-block">{{ $pago->created_at->format('d/m/Y H:i') }}</small>
                    </td>
                    <td class="d-none d-md-table-cell">
                        <div class="fw-medium">{{ $pedidoCodigo }}</div>
                        <span class="badge bg-{{ $tipoClass }} mt-1">{{ strtoupper($pago->tipo) }}</span>
                    </td>
                    <td class="text-end">
                        <span class="fw-bold text-success">Bs. {{ number_format($pago->pago_bs, 2) }}</span>
                        @if ($pago->descuento_bs > 0)
                            <small class="d-block text-warning">
                                Desc. Bs. {{ number_format($pago->descuento_bs, 2) }}
                            </small>
                        @endif
                    </td>
                    <td class="d-none d-lg-table-cell">
                        {{ $pago->cobrador ?? 'Sistema' }}
                    </td>
                    <td class="text-center cell-actions">
                        <div class="btn-group" role="group">
                            <a href="{{ route('admin.recibos.ver', $pago->id) }}" class="btn btn-sm btn-info"
                                target="_blank" title="Ver recibo">
                                <i class="ri-eye-line"></i>
                            </a>
                            <a href="{{ route('admin.recibos.pdf', $pago->id) }}" class="btn btn-sm btn-primary"
                                title="Descargar PDF">
                                <i class="ri-download-line"></i>
                            </a>
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
                        <h5 class="text-muted">No hay recibos registrados</h5>
                        <p class="text-muted">No se encontraron recibos para este cliente.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($pagos->hasPages())
    <div class="tab-pagination d-flex justify-content-end mt-3" data-tab="recibos">
        {{ $pagos->links('pagination::bootstrap-5') }}
    </div>
@endif
