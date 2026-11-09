<table class="table table-hover table-centered align-middle table-nowrap mb-0">
    <thead class="table-light">
        <tr>
            <th>Usuario</th>
            <th class="text-end">Efectivo (Bs.)</th>
            <th class="text-end">QR (Bs.)</th>
            <th class="text-end">Efectivo (Parcial)</th>
            <th class="text-end">QR (Parcial)</th>
            <th class="text-end">Descuento (Bs.)</th>
            <th class="text-end">Total (Bs.)</th>
            <th class="text-end">Recibos</th>
            <th class="text-center">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($cobrosPorUsuario as $usuarioId => $cobros)
            @php
                $totalEfectivo = $cobros['efectivo'] + $cobros['parcial_efectivo'];
                $totalQr = $cobros['qr'] + $cobros['parcial_qr'];
                $totalUsuario = $cobros['total'];
            @endphp
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-primary bg-opacity-10">
                                <i class="ri-user-line fs-18 text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">{{ $cobros['nombre_completo'] }}</h6>
                            @if ($cobros['usuario'] && $cobros['usuario']->persona)
                                <small class="text-muted">
                                    {{ $cobros['usuario']->persona->nombres ?? '' }}
                                    {{ $cobros['usuario']->persona->apellido_paterno ?? '' }}
                                    {{ $cobros['usuario']->persona->apellido_materno ?? '' }}
                                </small>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="text-end">
                    <span class="fw-bold text-success">Bs. {{ number_format($cobros['efectivo'], 2) }}</span>
                </td>
                <td class="text-end">
                    <span class="fw-bold text-primary">Bs. {{ number_format($cobros['qr'], 2) }}</span>
                </td>
                <td class="text-end">
                    <span class="fw-bold text-warning">Bs. {{ number_format($cobros['parcial_efectivo'], 2) }}</span>
                </td>
                <td class="text-end">
                    <span class="fw-bold text-info">Bs. {{ number_format($cobros['parcial_qr'], 2) }}</span>
                </td>
                <td class="text-end">
                    <span class="fw-bold text-warning">Bs. {{ number_format($cobros['descuento'], 2) }}</span>
                </td>
                <td class="text-end">
                    <span class="fw-bold text-dark">Bs. {{ number_format($totalUsuario, 2) }}</span>
                </td>
                <td class="text-end">
                    <span class="badge bg-primary">{{ $cobros['cantidad_recibos'] }}</span>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-info btnVerDetallesUsuario"
                        data-usuario-id="{{ $usuarioId }}">
                        <i class="ri-eye-line me-1"></i> Ver Detalles
                    </button>
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
                    <h5 class="text-muted">No hay cobros registrados</h5>
                    <p class="text-muted">No se encontraron cobros para la fecha seleccionada.</p>
                </td>
            </tr>
        @endforelse
    </tbody>
    <tfoot class="table-light">
        <tr>
            <th class="text-end">TOTALES:</th>
            <th class="text-end fw-bold text-success">Bs. {{ number_format($totalesGenerales['efectivo'], 2) }}</th>
            <th class="text-end fw-bold text-primary">Bs. {{ number_format($totalesGenerales['qr'], 2) }}</th>
            <th class="text-end fw-bold text-warning">Bs. {{ number_format($totalesGenerales['parcial_efectivo'], 2) }}
            </th>
            <th class="text-end fw-bold text-info">Bs. {{ number_format($totalesGenerales['parcial_qr'], 2) }}</th>
            <th class="text-end fw-bold text-warning">Bs. {{ number_format($totalesGenerales['descuento'], 2) }}</th>
            <th class="text-end fw-bold text-dark">Bs. {{ number_format($totalesGenerales['total'], 2) }}</th>
            <th class="text-end"></th>
            <th></th>
        </tr>
    </tfoot>
</table>
