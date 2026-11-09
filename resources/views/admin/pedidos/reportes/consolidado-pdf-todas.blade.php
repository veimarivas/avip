<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Consolidado - {{ $titulo }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #405189;
        }

        .header h1 {
            font-size: 16px;
            margin: 5px 0;
            color: #405189;
        }

        .header p {
            font-size: 10px;
            margin: 2px 0;
            color: #666;
        }

        .table-container {
            width: 100%;
            border-collapse: collapse;
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }

        .table-container th {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            color: black;
            padding: 4px 6px;
            text-align: center;
            font-weight: bold;
            vertical-align: middle;
        }

        .table-container td {
            border: 1px solid #dee2e6;
            padding: 4px 6px;
            text-align: center;
            vertical-align: middle;
        }

        .sticky-left {
            background-color: white;
            position: sticky;
            left: 0;
            z-index: 1;
        }

        .sticky-left.bg-light {
            background-color: #f8f9fa !important;
        }

        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }

        .cajas-row {
            background-color: #343a40;
            color: white;
            font-weight: bold;
        }

        .producto-header {
            background-color: #405189;
            color: white;
        }

        .numero-col {
            width: 20px;
        }

        .cliente-col {
            width: 90px;
            text-align: left;
        }

        .localidad-col {
            width: 50px;
            text-align: left;
        }

        .peso-col {
            width: 50px;
        }

        .info-footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            font-size: 9px;
            color: #666;
            text-align: center;
        }

        @page {
            margin: 10mm;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: 600;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
        }

        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .badge-dark {
            background-color: #343a40;
            color: white;
        }

        .badge-success {
            background-color: #198754;
            color: white;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>REPORTE CONSOLIDADO - {{ $titulo }}</h1>
        <p>Fecha: {{ $fecha }}</p>
        <p>Generado: {{ $fechaGeneracion }}</p>
        <p>Total Pedidos: {{ $totalPedidos }}</p>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th class="sticky-left bg-light numero-col">#</th>
                    <th class="sticky-left bg-light cliente-col">Cliente</th>
                    <th class="sticky-left bg-light localidad-col">Localidad</th>
                    @foreach ($productos as $producto)
                        <th class="peso-col producto-header">{{ $producto->nombre }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($datosReporte as $fila)
                    <tr>
                        <td class="sticky-left">{{ $fila['numero'] }}</td>
                        <td class="sticky-left">
                            <strong>{{ $fila['cliente_nombre'] }}</strong>
                            <br>
                            <small>{{ $fila['cliente_ci'] }}</small>
                        </td>
                        <td class="sticky-left">{{ $fila['localidad'] }}</td>
                        @foreach ($productos as $index => $producto)
                            <td>
                                @if ($fila['productos_peso'][$index] > 0)
                                    <span
                                        class="badge badge-info">{{ number_format($fila['productos_peso'][$index], 2) }}</span>
                                @else
                                    -
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach

                <!-- Fila de TOTALES (suma de pesos) -->
                <tr class="total-row">
                    <td class="sticky-left" colspan="3" style="text-align: center;">
                        <strong>TOTALES</strong>
                    </td>
                    @foreach ($productos as $index => $producto)
                        <td>
                            @if ($totalesPesos[$index] > 0)
                                <span class="badge badge-dark">{{ number_format($totalesPesos[$index], 2) }}</span>
                            @else
                                -
                            @endif
                        </td>
                    @endforeach
                </tr>

                <!-- Fila de CANTIDAD DE CAJAS (total dividido por n_caja y redondeado) -->
                <tr class="cajas-row">
                    <td class="sticky-left" colspan="3" style="text-align: center;">
                        <strong>CANTIDAD DE CAJAS</strong>
                    </td>
                    @foreach ($productos as $index => $producto)
                        <td>
                            @if ($cantidadCajas[$index] > 0)
                                <span class="badge badge-success">{{ $cantidadCajas[$index] }}</span>
                            @else
                                -
                            @endif
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>

    <div class="info-footer">
        <p>Sistema de Gestión de Pedidos | Reporte generado automáticamente</p>
        <p>Nota: La cantidad de cajas se calcula dividiendo el total de cada producto entre
            {{ $productos->first()->n_caja ?? 1 }} y redondeando</p>
    </div>
</body>

</html>
