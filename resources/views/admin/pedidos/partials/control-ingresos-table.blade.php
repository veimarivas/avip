<!-- Reporte por Usuario -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless table-centered align-middle table-nowrap mb-0">
                        <thead class="table-light text-muted fw-semibold">
                            <tr>
                                <th class="text-center">#</th>
                                <th>Usuario</th>
                                <th class="text-end">Total Efectivo (Bs.)</th>
                                <th class="text-end">Total QR (Bs.)</th>
                                <th class="text-end">Total General (Bs.)</th>
                                <th class="text-center">Pagos Realizados</th>
                                <th class="text-center">Detalles</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reporte as $index => $item)
                                <tr>
                                    <td class="text-center fw-bold">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="avatar-sm">
                                                    <div class="avatar-title bg-light text-primary rounded-circle">
                                                        {{ substr($item['usuario']['name'], 0, 1) }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-0">{{ $item['usuario']['name'] }}</h6>
                                                <small class="text-muted">{{ $item['usuario']['email'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-success">Bs.
                                            {{ number_format($item['total_efectivo'], 2) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-info">Bs.
                                            {{ number_format($item['total_qr'], 2) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-warning">Bs.
                                            {{ number_format($item['total_general'], 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $item['cantidad_pagos'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-info ver-detalles-usuario" data-bs-toggle="modal"
                                            data-bs-target="#modalDetallesUsuario"
                                            data-usuario='@json($item)'>
                                            <i class="ri-eye-line"></i> Ver
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="ri-inbox-line display-4"></i>
                                            <p class="mt-2">No hay datos para el período seleccionado</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if (count($reporte) > 0)
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2" class="text-end">TOTALES GENERALES:</th>
                                    <th class="text-end fw-bold text-success">Bs.
                                        {{ number_format($totalesGenerales['total_efectivo'], 2) }}</th>
                                    <th class="text-end fw-bold text-info">Bs.
                                        {{ number_format($totalesGenerales['total_qr'], 2) }}</th>
                                    <th class="text-end fw-bold text-warning">Bs.
                                        {{ number_format($totalesGenerales['total_general'], 2) }}</th>
                                    <th class="text-center fw-bold">{{ $totalesGenerales['total_pagos'] }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

                <!-- Periodo del Reporte -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="alert alert-primary">
                            <i class="ri-calendar-line me-2"></i>
                            <strong>Periodo del Reporte:</strong>
                            {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }}
                            al
                            {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
