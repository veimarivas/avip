<table class="table table-hover table-centered align-middle report-table mb-0">
    <thead class="table-light">
        <tr>
            <th>Recibo / Fecha</th>
            <th>Cliente / Localidad</th>
            <th class="text-end d-none d-md-table-cell">Monto</th>
            <th class="text-center d-none d-md-table-cell">Tipo</th>
            <th class="d-none d-lg-table-cell">Usuario</th>
            <th class="text-center cell-actions">
                <span class="d-none d-md-inline">Acciones</span>
                <i class="ri-more-2-line d-md-none"></i>
            </th>
        </tr>
    </thead>
    <tbody>
        @forelse($pagos as $pago)
            @php
                $tipoClass = match ($pago->tipo) {
                    'efectivo' => 'badge-efectivo',
                    'qr' => 'badge-qr',
                    'parcial' => 'badge-parcial',
                    default => 'badge-secondary',
                };
            @endphp
            <tr>
                <td>
                    <div class="fw-semibold">{{ $pago->recibo }}</div>
                    <small class="text-muted d-block">
                        {{ $pago->created_at->format('d/m/Y') }} · {{ $pago->created_at->format('H:i') }}
                    </small>
                </td>
                <td>
                    <div class="fw-semibold">
                        {{ $pago->cliente->nombres ?? 'N/A' }} {{ $pago->cliente->apellidos ?? '' }}
                    </div>
                    <small class="text-muted d-block">CI: {{ $pago->cliente->ci ?? 'N/A' }}</small>
                    @if ($pago->cliente && $pago->cliente->localidad)
                        <span class="badge mt-1"
                            style="background-color: {{ $pago->cliente->localidad->color ?? '#405189' }}; color: white;">
                            {{ $pago->cliente->localidad->nombre }}
                        </span>
                    @endif
                </td>
                <td class="text-end d-none d-md-table-cell">
                    <span class="fw-bold text-success">Bs. {{ number_format($pago->pago_bs, 2) }}</span>
                    @if ($pago->descuento_bs > 0)
                        <small class="d-block text-warning">
                            Desc. Bs. {{ number_format($pago->descuento_bs, 2) }}
                        </small>
                    @endif
                </td>
                <td class="text-center d-none d-md-table-cell">
                    <span class="badge {{ $tipoClass }}">
                        {{ strtoupper($pago->tipo) }}
                    </span>
                </td>
                <td class="d-none d-lg-table-cell">
                    {{ $pago->usuario->name ?? 'Sistema' }}
                </td>
                <td class="text-center cell-actions">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-info btnVerDetalles"
                            data-pago-id="{{ $pago->id }}" title="Ver detalles">
                            <i class="ri-eye-line"></i>
                        </button>
                        <a href="{{ route('admin.recibos.ver', $pago->id) }}" class="btn btn-sm btn-primary"
                            target="_blank" title="Ver recibo">
                            <i class="ri-external-link-line"></i>
                        </a>
                        <a href="{{ route('admin.recibos.pdf', $pago->id) }}" class="btn btn-sm btn-success"
                            title="Descargar PDF">
                            <i class="ri-download-line"></i>
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center py-5">
                    <div class="avatar-lg mx-auto mb-4">
                        <div class="avatar-title bg-light text-muted rounded-circle">
                            <i class="ri-inbox-line fs-24"></i>
                        </div>
                    </div>
                    <h5 class="text-muted">No hay recibos registrados</h5>
                    <p class="text-muted">No se encontraron recibos con los filtros aplicados.</p>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

@if ($pagos->hasPages())
    <div class="mt-3">
        {{ $pagos->links('pagination::bootstrap-5') }}
    </div>
@endif
