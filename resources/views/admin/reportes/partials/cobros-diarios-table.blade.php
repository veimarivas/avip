<table class="table table-hover table-centered align-middle report-table mb-0">
    <thead class="table-light">
        <tr>
            <th>Usuario</th>
            <th class="text-end">Efectivo</th>
            <th class="text-end">QR</th>
            <th class="text-end d-none d-md-table-cell">Descuento</th>
            <th class="text-end d-none d-md-table-cell">Total</th>
            <th class="text-end d-none d-lg-table-cell">Recibos</th>
            <th class="text-center cell-actions">
                <span class="d-none d-md-inline">Acciones</span>
                <i class="ri-more-2-line d-md-none"></i>
            </th>
        </tr>
    </thead>
    <tbody>
        @forelse($cobrosPorUsuario as $usuarioId => $cobros)
            @php
                $totalUsuario = $cobros['total'];
            @endphp
            <tr>
                <td class="cell-user">
                    <div class="d-flex align-items-center gap-2">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-primary bg-opacity-10">
                                <i class="ri-user-line fs-18 text-primary"></i>
                            </div>
                        </div>
                        <div class="min-w-0">
                            <div class="fw-semibold">{{ $cobros['nombre_completo'] }}</div>
                            @if ($cobros['usuario'] && $cobros['usuario']->persona)
                                <small class="text-muted d-block">
                                    {{ $cobros['usuario']->persona->nombres ?? '' }}
                                    {{ $cobros['usuario']->persona->apellido_paterno ?? '' }}
                                </small>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="text-end">
                    <span class="fw-bold text-success">Bs. {{ number_format($cobros['efectivo'], 2) }}</span>
                    @if ($cobros['parcial_efectivo'] > 0)
                        <small class="d-block text-muted">
                            Parcial: Bs. {{ number_format($cobros['parcial_efectivo'], 2) }}
                        </small>
                    @endif
                </td>
                <td class="text-end">
                    <span class="fw-bold text-primary">Bs. {{ number_format($cobros['qr'], 2) }}</span>
                    @if ($cobros['parcial_qr'] > 0)
                        <small class="d-block text-muted">
                            Parcial: Bs. {{ number_format($cobros['parcial_qr'], 2) }}
                        </small>
                    @endif
                </td>
                <td class="text-end d-none d-md-table-cell">
                    <span class="fw-medium text-warning">Bs. {{ number_format($cobros['descuento'], 2) }}</span>
                </td>
                <td class="text-end d-none d-md-table-cell">
                    <span class="fw-bold text-dark">Bs. {{ number_format($totalUsuario, 2) }}</span>
                </td>
                <td class="text-end d-none d-lg-table-cell">
                    <span class="badge bg-primary">{{ $cobros['cantidad_recibos'] }}</span>
                </td>
                <td class="text-center cell-actions">
                    <button type="button" class="btn btn-sm btn-soft-info btnVerDetallesUsuario"
                        data-usuario-id="{{ $usuarioId }}" title="Ver detalles">
                        <i class="ri-eye-line"></i>
                        <span class="d-none d-xl-inline ms-1">Ver</span>
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center py-5">
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
            <th class="text-end">TOTALES</th>
            <th class="text-end text-success">
                Bs. {{ number_format($totalesGenerales['efectivo'], 2) }}
                @if ($totalesGenerales['parcial_efectivo'] > 0)
                    <small class="d-block fw-normal text-muted">
                        Parcial: Bs. {{ number_format($totalesGenerales['parcial_efectivo'], 2) }}
                    </small>
                @endif
            </th>
            <th class="text-end text-primary">
                Bs. {{ number_format($totalesGenerales['qr'], 2) }}
                @if ($totalesGenerales['parcial_qr'] > 0)
                    <small class="d-block fw-normal text-muted">
                        Parcial: Bs. {{ number_format($totalesGenerales['parcial_qr'], 2) }}
                    </small>
                @endif
            </th>
            <th class="text-end text-warning d-none d-md-table-cell">
                Bs. {{ number_format($totalesGenerales['descuento'], 2) }}
            </th>
            <th class="text-end text-dark d-none d-md-table-cell">
                Bs. {{ number_format($totalesGenerales['total'], 2) }}
            </th>
            <th class="text-end d-none d-lg-table-cell"></th>
            <th></th>
        </tr>
    </tfoot>
</table>
