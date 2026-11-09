<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Reporte de Detalle de Pedidos</title>
    <style>
        /* Márgenes ajustados para impresión */
        @page {
            margin: 2mm 7mm 2mm 7mm;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            font-weight: bold;
            font-size: 14px;
        }

        .page {
            page-break-after: always;
            padding: 2mm 2mm;
        }

        /* Aplicar salto de página solo si NO es el último elemento */
        .page:not(:last-child) {
            page-break-after: always;
        }

        .header {
            text-align: center;
            margin-bottom: 2mm;
            border-bottom: 1px solid #333;
            padding-bottom: 1mm;
        }

        .header h1 {
            margin: 0;
            font-size: 14px;
            color: #333;
            line-height: 1;
        }

        .client-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1mm;
            margin-bottom: 1mm;
            font-size: 14px;
        }

        .info-item {
            margin: 0;
            line-height: 1;
        }

        .info-item h1 {
            margin: 0;
            font-size: 14px;
            line-height: 1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1mm 0;
            font-size: 14px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 0.5mm 1mm;
            text-align: left;
            line-height: 1;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
            padding: 0.5mm 1mm;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total {
            font-weight: bold;
            background-color: #f8f9fa;
        }

        .footer {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-top: 2mm;
            border-top: 1px solid #ddd;
            padding-top: 1mm;
            line-height: 1;
        }
    </style>
</head>

<body>
    @php
        $totalPedidos = count($pedidos);
        $contador = 0;
    @endphp

    @foreach ($pedidos as $pedido)
        @php
            $contador++;
            $productosProcesados = $pedido->pedidos_productos->where('procesado', '1');

            // Separar productos normales de pollo primera (id 1, 2, 3)
            $productosNormales = [];
            $polloPrimeraItems = [];

            foreach ($productosProcesados as $producto) {
                if ($producto->costo_total > 0) {
                    if (in_array($producto->producto_id ?? ($producto->producto->id ?? null), [1, 2, 3])) {
                        $polloPrimeraItems[] = $producto;
                    } else {
                        $productosNormales[] = $producto;
                    }
                }
            }

            // Calcular totales para pollo primera
            $polloPrimeraPesoTotal = 0;
            $polloPrimeraCostoTotal = 0;
            $polloPrimeraPrecio = null;
            $polloPrimeraPesoPedidoTotal = 0;

            foreach ($polloPrimeraItems as $producto) {
                $polloPrimeraPesoTotal += $producto->peso_real;
                $polloPrimeraCostoTotal += $producto->costo_total;
                $polloPrimeraPesoPedidoTotal += $producto->peso_pedido;
                if ($polloPrimeraPrecio === null) {
                    $polloPrimeraPrecio = $producto->precio;
                }
            }

            // Calcular total general
            $totalGeneral = $productosProcesados->sum('costo_total');
            $totalCajas = $productosProcesados->sum('n_cajas');
        @endphp

        <div class="page">
            <div class="header">
                <h1>NOTA DE ENTREGA N.{{ $pedido->id }}</h1>
            </div>

            <!-- Información del Cliente -->
            <div class="client-info">
                <div class="info-item"><strong>Fecha:</strong> {{ $fecha }}</div>
                <div class="info-item">
                    <h1><strong>Cliente:</strong> {{ $pedido->cliente->nombres }}
                        {{ $pedido->cliente->apellidos }}</h1>
                </div>
            </div>

            <!-- Tabla de productos -->
            <table>
                <thead>
                    <tr>
                        <th width="8%" class="text-center">Cantidad</th>
                        <th class="text-center">Concepto</th>
                        <th width="15%" class="text-end">Peso Neto</th>
                        <th width="15%" class="text-end">P. Unitario</th>
                        <th width="15%" class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Mostrar pollo primera como un solo registro -->
                    @if (count($polloPrimeraItems) > 0)
                        <tr>
                            <td>{{ number_format($polloPrimeraPesoPedidoTotal, 2) }}</td>
                            <td>pollo primera</td>
                            <td class="text-end">{{ number_format($polloPrimeraPesoTotal, 2) }}</td>
                            <td class="text-end">
                                @if ($polloPrimeraPrecio !== null)
                                    {{ number_format($polloPrimeraPrecio, 2) }}
                                @else
                                    0.00
                                @endif
                            </td>
                            <td class="text-end">{{ number_format($polloPrimeraCostoTotal, 2) }}</td>
                        </tr>
                    @endif

                    <!-- Mostrar productos normales -->
                    @foreach ($productosNormales as $producto)
                        <tr>
                            <td>{{ number_format($producto->peso_pedido, 2) }}</td>
                            <td>{{ $producto->producto->nombre ?? 'N/A' }}</td>
                            <td class="text-end">{{ number_format($producto->peso_real, 2) }}</td>
                            <td class="text-end">{{ number_format($producto->precio, 2) }}</td>
                            <td class="text-end">{{ number_format($producto->costo_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total">
                        <td colspan="4" class="text-end"><strong>TOTAL:</strong></td>
                        <td class="text-end"><strong>{{ number_format($totalGeneral, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>

            <div class="footer">
                {{ $titulo }} - {{ $localidad->nombre }} | {{ $fechaGeneracion }}
            </div>
        </div>
    @endforeach
</body>

</html>
