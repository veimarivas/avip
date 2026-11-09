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

        .text-center {
            text-align: center;
        }

        .totales {
            font-weight: bold;
            background-color: #e9ecef;
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
    </style>
</head>

<body>
    <div class="header">
        <h1>REPORTE CONSOLIDADO - PESOS DE PRODUCTOS</h1>
        <div class="subtitle">Fecha: {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</div>
        <div class="subtitle">Generado: {{ $fechaGeneracion }}</div>
        @if ($localidadFiltro)
            <div class="subtitle">Localidad: {{ $localidadFiltro->nombre }}</div>
        @endif
    </div>

    @foreach ($datosReporte as $datosLocalidad)
        <div style="margin-bottom: 20px;">
            <h3>Localidad: {{ $datosLocalidad['localidad']->nombre }}</h3>

            <table>
                <thead>
                    <tr>
                        <th width="100">Localidad</th>
                        <th width="200">Cliente</th>
                        <th width="60">Orden</th>
                        @foreach ($productos as $producto)
                            <th width="80" class="text-center">{{ $producto->nombre }} (kg)</th>
                        @endforeach
                        <th width="100" class="text-center">Subtotal (Bs.)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($datosLocalidad['clientes'] as $clienteData)
                        <tr>
                            <td>{{ $datosLocalidad['localidad']->nombre }}</td>
                            <td>{{ $clienteData['cliente']->nombres }} {{ $clienteData['cliente']->apellidos }}</td>
                            <td class="text-center">{{ $clienteData['cliente']->orden }}</td>
                            @foreach ($productos as $producto)
                                <td class="text-end">{{ number_format($clienteData['productos'][$producto->id], 2) }}
                                </td>
                            @endforeach
                            <td class="text-end">{{ number_format($clienteData['subtotal'], 2) }}</td>
                        </tr>
                    @endforeach
                    <!-- Fila de totales -->
                    <tr class="totales">
                        <td colspan="3">TOTALES</td>
                        @foreach ($productos as $index => $producto)
                            <td class="text-end">
                                {{ number_format($datosLocalidad['totales']['productos'][$index], 2) }}</td>
                        @endforeach
                        <td class="text-end">{{ number_format($datosLocalidad['totales']['subtotal'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if (!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach
</body>

</html>
