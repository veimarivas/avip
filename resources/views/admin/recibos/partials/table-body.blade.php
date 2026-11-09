<table class="table table-hover table-centered align-middle table-nowrap mb-0">
    <thead class="table-light">
        <tr>
            <th>Recibo</th>
            <th>Fecha</th>
            <th>Cliente</th>
            <th>Localidad</th>
            <th class="text-end">Monto Pagado</th>
            <th class="text-end">Descuento</th>
            <th>Tipo de Pago</th>
            <th>Usuario</th>
            <th class="text-center">Acciones</th>
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
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-info bg-opacity-10">
                                <i class="ri-receipt-line fs-18 text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">{{ $pago->recibo }}</h6>
                            <small class="text-muted">ID: {{ $pago->id }}</small>
                        </div>
                    </div>
                </td>
                <td>
                    {{ $pago->created_at->format('d/m/Y H:i') }}
                    <br>
                    <small class="text-muted">{{ $pago->created_at->format('H:i:s') }}</small>
                </td>
                <td>
                    <strong>{{ $pago->cliente->nombres ?? 'N/A' }} {{ $pago->cliente->apellidos ?? '' }}</strong>
                    <br>
                    <small class="text-muted">CI: {{ $pago->cliente->ci ?? 'N/A' }}</small>
                </td>
                <td>
                    @if ($pago->cliente->localidad)
                        <span class="badge"
                            style="background-color: {{ $pago->cliente->localidad->color ?? '#405189' }}; color: white;">
                            {{ $pago->cliente->localidad->nombre }}
                        </span>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </td>
                <td class="text-end">
                    <span class="fw-bold text-success">Bs. {{ number_format($pago->pago_bs, 2) }}</span>
                </td>
                <td class="text-end">
                    @if ($pago->descuento_bs > 0)
                        <span class="fw-medium text-warning">Bs. {{ number_format($pago->descuento_bs, 2) }}</span>
                    @else
                        <span class="text-muted">Bs. 0.00</span>
                    @endif
                </td>
                <td>
                    <span class="badge {{ $tipoClass }}">
                        {{ strtoupper($pago->tipo) }}
                    </span>
                </td>
                <td>
                    {{ $pago->usuario->name ?? 'Sistema' }}
                </td>
                <td class="text-center">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-info btnVerDetalles"
                            data-pago-id="{{ $pago->id }}" title="Ver Detalles">
                            <i class="ri-eye-line"></i>
                        </button>
                        <a href="{{ route('admin.recibos.ver', $pago->id) }}" class="btn btn-sm btn-primary"
                            target="_blank" title="Ver Recibo PDF">
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
                <td colspan="9" class="text-center py-5">
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
    <div class="row mt-3">
        <div class="col-12">
            {{ $pagos->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endif
