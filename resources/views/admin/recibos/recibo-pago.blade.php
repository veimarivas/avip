<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo {{ $pago->recibo }}</title>
    <style>
        /* Estilos más compactos y optimizados */
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 15px;
            font-size: 11px;
            line-height: 1.2;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
        }

        .header h1 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }

        .header h2 {
            margin: 2px 0;
            font-size: 12px;
            color: #666;
        }

        .company-info {
            margin-bottom: 10px;
            text-align: center;
            font-size: 9px;
            color: #666;
        }

        .section {
            margin-bottom: 8px;
        }

        .section-title {
            background-color: #f5f5f5;
            padding: 3px 6px;
            font-weight: bold;
            border-left: 2px solid #333;
            margin-bottom: 6px;
            font-size: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6px;
            margin-bottom: 8px;
        }

        .info-item {
            margin-bottom: 3px;
        }

        .info-label {
            font-weight: bold;
            color: #666;
            font-size: 9px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
            font-size: 9px;
        }

        .table th {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 4px;
            text-align: left;
            font-weight: bold;
        }

        .table td {
            border: 1px solid #dee2e6;
            padding: 4px;
            vertical-align: top;
        }

        .table-compact {
            font-size: 8px;
        }

        .table-compact th,
        .table-compact td {
            padding: 2px 3px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-nowrap {
            white-space: nowrap;
        }

        .totals {
            margin-top: 15px;
            width: 40%;
            margin-left: auto;
            font-size: 10px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .total-row:last-child {
            border-bottom: none;
        }

        .total-label {
            font-weight: bold;
        }

        .total-amount {
            font-weight: bold;
        }

        .grand-total {
            font-size: 12px;
            color: #000;
            margin-top: 5px;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 5px;
        }

        .signature {
            margin-top: 20px;
            text-align: center;
        }

        .signature-line {
            width: 150px;
            border-top: 1px solid #333;
            margin: 0 auto;
            margin-top: 15px;
        }

        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #d1f7c4;
            color: #0c622f;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .badge-primary {
            background-color: #d1e7ff;
            color: #084298;
        }

        .page-break {
            page-break-before: always;
        }

        /* Nuevos estilos para optimización */
        .compact-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 5px;
            margin-bottom: 5px;
        }

        .double-column {
            grid-column: span 2;
        }

        .info-value {
            font-size: 10px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-top: 10px;
        }

        .summary-box {
            border: 1px solid #dee2e6;
            border-radius: 3px;
            padding: 5px;
            text-align: center;
        }

        .summary-label {
            font-size: 8px;
            color: #666;
            margin-bottom: 2px;
        }

        .summary-value {
            font-size: 12px;
            font-weight: bold;
        }

        .summary-success {
            border-color: #28a745;
            background-color: #f8fff9;
        }

        .summary-warning {
            border-color: #ffc107;
            background-color: #fffdf6;
        }

        .summary-primary {
            border-color: #007bff;
            background-color: #f8fbff;
        }

        /* Estilos para columnas específicas de tabla */
        .col-pedido {
            width: 12%;
        }

        .col-tipo {
            width: 10%;
        }

        .col-descripcion {
            width: 23%;
        }

        .col-monto {
            width: 11%;
        }

        .no-wrap {
            white-space: nowrap;
        }

        /* Optimización para texto largo */
        .truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Estilo para badges de tipo de pedido */
        .badge-tipo {
            font-size: 7px;
            padding: 1px 3px;
            border-radius: 2px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>RECIBO DE PAGO</h1>
        <h2>N° {{ $pago->recibo }}</h2>
        <div class="company-info">
            <p>Fecha: {{ $pago->created_at->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>

    <!-- Información compacta del cliente -->
    <div class="section">
        <div class="section-title">INFORMACIÓN DEL CLIENTE</div>
        <div class="compact-grid">
            <div class="info-item">
                <div class="info-label">Cliente:</div>
                <div class="info-value truncate">{{ $cliente->nombres }} {{ $cliente->apellidos }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Localidad:</div>
                <div class="info-value">{{ $cliente->localidad->nombre ?? 'No asignada' }}</div>
            </div>
        </div>
    </div>

    <!-- Resumen rápido de montos -->
    <div class="summary-grid">
        <div class="summary-box summary-success">
            <div class="summary-label">TOTAL PAGADO</div>
            <div class="summary-value">Bs. {{ number_format($totalMontoAsignado ?? $pago->pago_bs, 2) }}</div>
        </div>
        <div class="summary-box summary-warning">
            <div class="summary-label">DESCUENTO</div>
            <div class="summary-value">Bs. {{ number_format($totalDescuentoAsignado ?? $pago->descuento_bs, 2) }}</div>
        </div>
        <div class="summary-box summary-primary">
            <div class="summary-label">TOTAL GENERAL</div>
            <div class="summary-value">
                Bs.
                {{ number_format(($totalMontoAsignado ?? $pago->pago_bs) + ($totalDescuentoAsignado ?? $pago->descuento_bs), 2) }}
            </div>
        </div>
    </div>

    <!-- Detalle compacto de pedidos -->
    <div class="section">
        <div class="section-title">DETALLE DE PEDIDOS ({{ $pago->pedidos->count() }})</div>

        @if ($pago->pedidos->count() > 0)
            <table class="table table-compact">
                <thead>
                    <tr>
                        <th class="col-pedido text-left">Pedido</th>
                        <th class="col-tipo text-left">Tipo</th> <!-- Columna nueva -->
                        <th class="col-descripcion text-left">Descripción</th>
                        <th class="col-monto text-right">Subtotal</th>
                        <th class="col-monto text-right">Pagado</th>
                        <th class="col-monto text-right">Desc.</th>
                        <th class="col-monto text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pago->pedidos as $pedido)
                        @php
                            // Determinar el tipo de pedido basado en pedido_dia
                            $tipoPedido = 'Regular';
                            $badgeClass = 'badge-primary';

                            if ($pedido->pedidos_productos && $pedido->pedidos_productos->count() > 0) {
                                $primerProducto = $pedido->pedidos_productos->first();

                                if ($primerProducto->pedido_dia == 1) {
                                    $tipoPedido = 'Frial';
                                    $badgeClass = 'badge-success';
                                } elseif ($primerProducto->pedido_dia == 2) {
                                    $tipoPedido = 'Frial Sofia';
                                    $badgeClass = 'badge-info';
                                }
                            }
                        @endphp
                        <tr>
                            <td class="text-left no-wrap">{{ $pedido->codigo ?? 'N/A' }}</td>
                            <td class="text-left no-wrap">
                                <span class="badge-tipo {{ $badgeClass }}">{{ $tipoPedido }}</span>
                            </td>
                            <td class="text-left truncate">
                                @if ($pedido->platillo)
                                    {{ Str::limit($pedido->platillo, 25) }}
                                @else
                                    Pedido #{{ $pedido->id }}
                                @endif
                            </td>
                            <td class="text-right no-wrap">Bs. {{ number_format($pedido->subtotal, 2) }}</td>
                            <td class="text-right no-wrap">Bs. {{ number_format($pedido->pivot->monto_asignado, 2) }}
                            </td>
                            <td class="text-right no-wrap">Bs.
                                {{ number_format($pedido->pivot->descuento_asignado, 2) }}</td>
                            <td class="text-right no-wrap fw-bold">
                                Bs.
                                {{ number_format($pedido->pivot->monto_asignado + $pedido->pivot->descuento_asignado, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                @if ($pago->pedidos->count() > 1)
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Totales:</strong></td>
                            <td class="text-right no-wrap"><strong>Bs.
                                    {{ number_format($pago->pedidos->sum('subtotal'), 2) }}</strong></td>
                            <td class="text-right no-wrap"><strong>Bs.
                                    {{ number_format($totalMontoAsignado ?? 0, 2) }}</strong></td>
                            <td class="text-right no-wrap"><strong>Bs.
                                    {{ number_format($totalDescuentoAsignado ?? 0, 2) }}</strong></td>
                            <td class="text-right no-wrap"><strong>Bs.
                                    {{ number_format(($totalMontoAsignado ?? 0) + ($totalDescuentoAsignado ?? 0), 2) }}</strong>
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        @else
            <div class="text-center">
                <p>No hay pedidos asociados a este recibo.</p>
            </div>
        @endif
    </div>

    <!-- Información compacta del pago -->
    <div class="section">
        <div class="section-title">INFORMACIÓN DEL PAGO</div>
        <div class="compact-grid">
            <div class="info-item">
                <div class="info-label">Tipo de Pago:</div>
                <div class="info-value">
                    @if ($pago->tipo == 'efectivo')
                        <span class="badge badge-success">EFECTIVO</span>
                    @elseif($pago->tipo == 'qr')
                        <span class="badge badge-warning">QR</span>
                    @elseif($pago->tipo == 'parcial')
                        <span class="badge badge-warning">PARCIAL</span>
                    @else
                        {{ strtoupper($pago->tipo) }}
                    @endif
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Cobrador:</div>
                <div class="info-value truncate">{{ $pago->cobrador ?? (auth()->user()->name ?? 'Sistema') }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Usuario:</div>
                <div class="info-value truncate">{{ $pago->usuario->name ?? 'Sistema' }}</div>
            </div>
        </div>
    </div>

    <!-- Detalles de formas de pago (solo si existen) -->
    @if ($pago->detalles && $pago->detalles->count() > 0)
        <div class="section">
            <div class="section-title">FORMA DE PAGO</div>
            <table class="table table-compact">
                <thead>
                    <tr>
                        <th class="text-left">Tipo</th>
                        <th class="text-right">Monto (Bs.)</th>
                        <th class="text-right">Descuento (Bs.)</th>
                        <th class="text-right">Subtotal (Bs.)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pago->detalles as $detalle)
                        <tr>
                            <td class="text-left">
                                @if ($detalle->tipo == 'efectivo')
                                    <span class="badge badge-success">EFECTIVO</span>
                                @elseif($detalle->tipo == 'qr')
                                    <span class="badge badge-warning">QR</span>
                                @else
                                    {{ strtoupper($detalle->tipo) }}
                                @endif
                            </td>
                            <td class="text-right no-wrap">Bs. {{ number_format($detalle->pago_bs, 2) }}</td>
                            <td class="text-right no-wrap">Bs. {{ number_format($detalle->descuentos_bs, 2) }}</td>
                            <td class="text-right no-wrap fw-bold">
                                Bs. {{ number_format($detalle->pago_bs + $detalle->descuentos_bs, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                @if ($pago->detalles->count() > 1)
                    <tfoot>
                        <tr>
                            <td class="text-right"><strong>Totales:</strong></td>
                            <td class="text-right no-wrap"><strong>Bs.
                                    {{ number_format($pago->detalles->sum('pago_bs'), 2) }}</strong></td>
                            <td class="text-right no-wrap"><strong>Bs.
                                    {{ number_format($pago->detalles->sum('descuentos_bs'), 2) }}</strong></td>
                            <td class="text-right no-wrap"><strong>Bs.
                                    {{ number_format($pago->detalles->sum('pago_bs') + $pago->detalles->sum('descuentos_bs'), 2) }}</strong>
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    @endif

    <!-- Resumen final compacto -->
    <div class="totals">
        <div class="section-title">RESUMEN</div>
        <div class="total-row">
            <span class="total-label">Total Pagado:</span>
            <span class="total-amount no-wrap">Bs. {{ number_format($totalMontoAsignado ?? $pago->pago_bs, 2) }}</span>
        </div>
        <div class="total-row">
            <span class="total-label">Total Descuento:</span>
            <span class="total-amount no-wrap">Bs.
                {{ number_format($totalDescuentoAsignado ?? $pago->descuento_bs, 2) }}</span>
        </div>
        <div class="total-row grand-total">
            <span class="total-label">TOTAL GENERAL:</span>
            <span class="total-amount no-wrap">
                Bs.
                {{ number_format(($totalMontoAsignado ?? $pago->pago_bs) + ($totalDescuentoAsignado ?? $pago->descuento_bs), 2) }}
            </span>
        </div>
    </div>

    <!-- Firma más compacta -->
    <div class="signature">
        <p style="margin-bottom: 5px; font-size: 9px;">Firma del Cliente</p>
        <div class="signature-line"></div>
        <p style="margin-top: 5px; font-size: 8px; color: #666;">Recibo válido como comprobante de pago</p>
    </div>

    <!-- Footer minimalista -->
    <div class="footer">
        <p>Documento generado automáticamente • Fecha de emisión: {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>

</html>
