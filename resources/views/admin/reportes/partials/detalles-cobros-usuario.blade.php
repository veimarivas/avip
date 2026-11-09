<div class="container-fluid">
    <!-- Encabezado -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-primary">
                <h5 class="mb-1">
                    <i class="ri-user-line me-2"></i>
                    Detalles de Cobros - {{ $usuario->name }}
                </h5>
                <p class="mb-0">
                    Fecha: {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }} |
                    Total Recibos: {{ $pagos->count() }}
                </p>
            </div>
        </div>
    </div>

    <!-- Totales del Usuario -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center p-3">
                    <p class="mb-1 text-muted small">Total Efectivo</p>
                    <p class="mb-0 fw-bold text-success fs-4">
                        Bs. {{ number_format($totales['efectivo'] + $totales['parcial_efectivo'], 2) }}
                    </p>
                    <small class="text-muted">
                        Directo: {{ number_format($totales['efectivo'], 2) }}<br>
                        Parcial: {{ number_format($totales['parcial_efectivo'], 2) }}
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center p-3">
                    <p class="mb-1 text-muted small">Total QR</p>
                    <p class="mb-0 fw-bold text-primary fs-4">
                        Bs. {{ number_format($totales['qr'] + $totales['parcial_qr'], 2) }}
                    </p>
                    <small class="text-muted">
                        Directo: {{ number_format($totales['qr'], 2) }}<br>
                        Parcial: {{ number_format($totales['parcial_qr'], 2) }}
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center p-3">
                    <p class="mb-1 text-muted small">Total Descuentos</p>
                    <p class="mb-0 fw-bold text-warning fs-4">Bs. {{ number_format($totales['descuento'], 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-dark">
                <div class="card-body text-center p-3">
                    <p class="mb-1 text-muted small">Total General</p>
                    <p class="mb-0 fw-bold text-dark fs-4">Bs. {{ number_format($totales['total'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Recibos del Usuario -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary-subtle">
                    <h5 class="mb-0 text-primary">
                        <i class="ri-receipt-line me-2"></i> Recibos Generados
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Recibo</th>
                                    <th>Cliente</th>
                                    <th>Tipo de Pago</th>
                                    <th class="text-end">Monto Pagado</th>
                                    <th class="text-end">Descuento</th>
                                    <th class="text-end">Efectivo</th>
                                    <th class="text-end">QR</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Hora</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pagos as $pago)
                                    @php
                                        $efectivo = 0;
                                        $qr = 0;

                                        if ($pago->tipo === 'efectivo') {
                                            $efectivo = $pago->pago_bs;
                                        } elseif ($pago->tipo === 'qr') {
                                            $qr = $pago->pago_bs;
                                        } elseif ($pago->tipo === 'parcial') {
                                            foreach ($pago->detalles as $detalle) {
                                                if ($detalle->tipo === 'efectivo') {
                                                    $efectivo = $detalle->pago_bs;
                                                } elseif ($detalle->tipo === 'qr') {
                                                    $qr = $detalle->pago_bs;
                                                }
                                            }
                                        }

                                        $totalPago = $efectivo + $qr;
                                    @endphp
                                    <tr>
                                        <td>{{ $pago->recibo }}</td>
                                        <td>
                                            {{ $pago->cliente->nombres ?? '' }} {{ $pago->cliente->apellidos ?? '' }}
                                            <br>
                                            <small class="text-muted">CI: {{ $pago->cliente->ci ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $pago->tipo == 'efectivo' ? 'success' : ($pago->tipo == 'qr' ? 'primary' : 'warning') }}">
                                                {{ strtoupper($pago->tipo) }}
                                            </span>
                                        </td>
                                        <td class="text-end">Bs. {{ number_format($pago->pago_bs, 2) }}</td>
                                        <td class="text-end">Bs. {{ number_format($pago->descuento_bs, 2) }}</td>
                                        <td class="text-end text-success">Bs. {{ number_format($efectivo, 2) }}</td>
                                        <td class="text-end text-primary">Bs. {{ number_format($qr, 2) }}</td>
                                        <td class="text-end fw-bold">Bs. {{ number_format($totalPago, 2) }}</td>
                                        <td class="text-center">{{ $pago->created_at->format('H:i:s') }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.recibos.ver', $pago->id) }}"
                                                class="btn btn-sm btn-info" target="_blank" title="Ver Recibo">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">TOTALES:</th>
                                    <th class="text-end">Bs. {{ number_format($pagos->sum('pago_bs'), 2) }}</th>
                                    <th class="text-end">Bs. {{ number_format($pagos->sum('descuento_bs'), 2) }}</th>
                                    <th class="text-end text-success">Bs.
                                        {{ number_format($totales['efectivo'] + $totales['parcial_efectivo'], 2) }}
                                    </th>
                                    <th class="text-end text-primary">Bs.
                                        {{ number_format($totales['qr'] + $totales['parcial_qr'], 2) }}</th>
                                    <th class="text-end fw-bold">Bs. {{ number_format($totales['total'], 2) }}</th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
