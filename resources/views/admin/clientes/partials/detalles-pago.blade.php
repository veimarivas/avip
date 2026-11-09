@if (isset($pago) && $pago)
    <div class="container-fluid">
        <!-- Encabezado -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-primary">
                    <h5 class="mb-1"><i class="ri-receipt-line me-2"></i> Detalles del Pago - {{ $pago->recibo }}</h5>
                    <p class="mb-0">Fecha: {{ $pago->created_at->format('d/m/Y H:i:s') }}</p>
                </div>
            </div>
        </div>

        <!-- Información del Pago -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="card border-primary">
                    <div class="card-body">
                        <h6 class="card-title text-primary">Información del Cliente</h6>
                        <div class="mb-2">
                            <strong>Nombre:</strong> {{ $pago->cliente->nombres ?? 'N/A' }}
                            {{ $pago->cliente->apellidos ?? '' }}
                        </div>
                        <div class="mb-2">
                            <strong>CI:</strong> {{ $pago->cliente->ci ?? 'N/A' }}
                        </div>
                        <div class="mb-2">
                            <strong>Localidad:</strong> {{ $pago->cliente->localidad->nombre ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-success">
                    <div class="card-body">
                        <h6 class="card-title text-success">Información del Pago</h6>
                        <div class="mb-2">
                            <strong>Pedidos Asociados:</strong> {{ $pago->pedidos->count() }}
                        </div>
                        <div class="mb-2">
                            <strong>Tipo de Pago:</strong>
                            <span
                                class="badge bg-{{ $pago->tipo == 'efectivo' ? 'success' : ($pago->tipo == 'qr' ? 'primary' : 'warning') }}">
                                {{ strtoupper($pago->tipo) }}
                            </span>
                        </div>
                        <div class="mb-2">
                            <strong>Cobrador:</strong> {{ $pago->cobrador }}
                        </div>
                        <div class="mb-2">
                            <strong>Usuario:</strong> {{ $pago->usuario->name ?? 'Sistema' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalle de Pedidos Asociados -->
        @if ($pago->pedidos->count() > 0)
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="ri-list-check-2 me-2"></i> Pedidos Asociados
                                ({{ $pago->pedidos->count() }})</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Código</th>
                                            <th>Subtotal</th>
                                            <th class="text-end">Monto Asignado</th>
                                            <th class="text-end">Descuento Asignado</th>
                                            <th class="text-end">Total Aplicado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pago->pedidos as $pedido)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $pedido->codigo ?? 'N/A' }}</td>
                                                <td>Bs. {{ number_format($pedido->subtotal, 2) }}</td>
                                                <td class="text-end">Bs.
                                                    {{ number_format($pedido->pivot->monto_asignado, 2) }}</td>
                                                <td class="text-end">Bs.
                                                    {{ number_format($pedido->pivot->descuento_asignado, 2) }}</td>
                                                <td class="text-end fw-bold">
                                                    Bs.
                                                    {{ number_format($pedido->pivot->monto_asignado + $pedido->pivot->descuento_asignado, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th colspan="3" class="text-end">TOTALES:</th>
                                            <th class="text-end">Bs. {{ number_format($totalMontoAsignado, 2) }}</th>
                                            <th class="text-end">Bs. {{ number_format($totalDescuentoAsignado, 2) }}
                                            </th>
                                            <th class="text-end">Bs.
                                                {{ number_format($totalMontoAsignado + $totalDescuentoAsignado, 2) }}
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Montos del Pago -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-success">
                    <div class="card-body">
                        <h6 class="card-title text-success">Resumen de Montos</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <p class="mb-1 text-muted">Total Pagado</p>
                                    <h4 class="text-success">Bs.
                                        {{ number_format($totalMontoAsignado ?? $pago->pago_bs, 2) }}</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <p class="mb-1 text-muted">Total Descuento</p>
                                    <h4 class="text-warning">Bs.
                                        {{ number_format($totalDescuentoAsignado ?? $pago->descuento_bs, 2) }}</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <p class="mb-1 text-muted">Total General</p>
                                    <h4 class="text-primary">Bs.
                                        {{ number_format(($totalMontoAsignado ?? $pago->pago_bs) + ($totalDescuentoAsignado ?? $pago->descuento_bs), 2) }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalles del Pago (si hay múltiples) -->
        @if ($pago->detalles && $pago->detalles->count() > 0)
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0"><i class="ri-list-check-2 me-2"></i> Detalles de Formas de Pago</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Tipo</th>
                                            <th class="text-end">Monto (Bs.)</th>
                                            <th class="text-end">Descuento (Bs.)</th>
                                            <th class="text-end">Subtotal (Bs.)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pago->detalles as $detalle)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <span
                                                        class="badge bg-{{ $detalle->tipo == 'efectivo' ? 'success' : 'primary' }}">
                                                        {{ strtoupper($detalle->tipo) }}
                                                    </span>
                                                </td>
                                                <td class="text-end">{{ number_format($detalle->pago_bs, 2) }}</td>
                                                <td class="text-end">{{ number_format($detalle->descuentos_bs, 2) }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format($detalle->pago_bs + $detalle->descuentos_bs, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th colspan="2" class="text-end">TOTALES:</th>
                                            <th class="text-end">Bs.
                                                {{ number_format($pago->detalles->sum('pago_bs'), 2) }}</th>
                                            <th class="text-end">Bs.
                                                {{ number_format($pago->detalles->sum('descuentos_bs'), 2) }}</th>
                                            <th class="text-end">Bs.
                                                {{ number_format($pago->detalles->sum(function ($d) {return $d->pago_bs + $d->descuentos_bs;}),2) }}
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@else
    <div class="alert alert-warning">
        <i class="ri-alert-line me-2"></i>
        No se pudo cargar la información del pago
    </div>
@endif
