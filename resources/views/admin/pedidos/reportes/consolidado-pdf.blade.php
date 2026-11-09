<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Reporte Consolidado - {{ $fecha }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 10px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .text-end {
            text-align: right;
        }

        .page-break {
            page-break-after: always;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 16px;
        }

        .header .subtitle {
            font-size: 12px;
            color: #666;
        }

        .localidad-header {
            background-color: #e9ecef;
            padding: 8px;
            margin: 10px 0;
            border-radius: 4px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>REPORTE CONSOLIDADO - PESOS DE PRODUCTOS</h1>
        <div class="subtitle">Fecha: {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</div>
        <div class="subtitle">Generado: {{ $fechaGeneracion }}</div>
    </div>

    @php
        $tipos = [0 => 'Regulares', 1 => 'Frial', 2 => 'Frial Sofia'];
    @endphp

    @foreach ($pedidosAgrupados as $localidadId => $tiposPedidos)
        @foreach ($tiposPedidos as $tipoPedido => $pedidos)
            @php
                $localidad = $pedidos->first()->localidad;
                $tipoNombre = $tipos[$tipoPedido] ?? 'Desconocido';
            @endphp

            <div class="localidad-header">
                Localidad: {{ $localidad->nombre }} - {{ $tipoNombre }}
            </div>

            <table>
                <thead>
                    <tr>
                        <th width="30">#</th>
                        <th>Cliente</th>
                        <th width="80">CI</th>
                        @foreach ($productosConsolidados as $producto)
                            <th width="60" class="text-end">{{ $producto }} (Kg)</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pedidos as $index => $pedido)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $pedido->cliente->nombres }} {{ $pedido->cliente->apellidos }}</td>
                            <td>{{ $pedido->cliente->ci }}</td>
                            @foreach ($productosConsolidados as $producto)
                                @php
                                    $peso = 0;
                                    foreach ($pedido->pedidos_productos as $pedidoProducto) {
                                        $productoNombre = $pedidoProducto->producto->nombre;
                                        if (in_array($pedidoProducto->producto_id, [1, 2, 3])) {
                                            $productoNombre = 'Pollo Primera';
                                        }
                                        if ($productoNombre == $producto) {
                                            $peso += $pedidoProducto->peso_real;
                                        }
                                    }
                                @endphp
                                <td class="text-end">{{ number_format($peso, 2) }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if (!$loop->last)
                <div style="page-break-after: always;"></div>
            @endif
        @endforeach
    @endforeach
</body>

</html>
