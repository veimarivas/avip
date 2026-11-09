<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Control de Ingresos - {{ $fechaInicio }} al {{ $fechaFin }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #3498db;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
        }

        .header p {
            color: #7f8c8d;
            margin: 5px 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }

        .table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .table tfoot th {
            background-color: #f1c40f;
            color: #333;
        }

        .totals {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .periodo {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #e3f2fd;
            border-radius: 5px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-bold {
            font-weight: bold;
        }

        .text-success {
            color: #28a745;
        }

        .text-info {
            color: #17a2b8;
        }

        .text-warning {
            color: #ffc107;
        }

        .mb-3 {
            margin-bottom: 15px;
        }

        .mt-3 {
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>CONTROL DE INGRESOS POR USUARIO</h1>
        <p>Sistema de Gestión de Pedidos</p>
        <p>Generado el: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <div class="periodo">
        <h3>Periodo del Reporte:</h3>
        <p class="text-bold">Desde: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }}</p>
        <p class="text-bold">Hasta: {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</p>
    </div>

    <!-- Totales Generales -->
    <div class="totals">
        <h3>Totales Generales:</h3>
        <div class="row">
            <div class="col">
                <p><strong>Total Efectivo:</strong> <span class="text-success text-bold">Bs.
                        {{ number_format($totalesGenerales['total_efectivo'], 2) }}</span></p>
                <p><strong>Total QR:</strong> <span class="text-info text-bold">Bs.
                        {{ number_format($totalesGenerales['total_qr'], 2) }}</span></p>
                <p><strong>Total General:</strong> <span class="text-warning text-bold">Bs.
                        {{ number_format($totalesGenerales['total_general'], 2) }}</span></p>
            </div>
            <div class="col">
                <p><strong>Usuarios con Cobros:</strong> {{ $totalesGenerales['cantidad_usuarios'] }}</p>
                <p><strong>Total de Pagos:</strong> {{ $totalesGenerales['total_pagos'] }}</p>
            </div>
        </div>
    </div>

    <!-- Tabla de Reporte -->
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Usuario</th>
                <th class="text-right">Total Efectivo (Bs.)</th>
                <th class="text-right">Total QR (Bs.)</th>
                <th class="text-right">Total General (Bs.)</th>
                <th class="text-center">Pagos Realizados</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reporte as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item['usuario']['name'] }}</td>
                    <td class="text-right text-success">{{ number_format($item['total_efectivo'], 2) }}</td>
                    <td class="text-right text-info">{{ number_format($item['total_qr'], 2) }}</td>
                    <td class="text-right text-warning">{{ number_format($item['total_general'], 2) }}</td>
                    <td class="text-center">{{ $item['cantidad_pagos'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-right">TOTALES GENERALES:</th>
                <th class="text-right text-success">{{ number_format($totalesGenerales['total_efectivo'], 2) }}</th>
                <th class="text-right text-info">{{ number_format($totalesGenerales['total_qr'], 2) }}</th>
                <th class="text-right text-warning">{{ number_format($totalesGenerales['total_general'], 2) }}</th>
                <th class="text-center">{{ $totalesGenerales['total_pagos'] }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="mt-3">
        <p><strong>Notas:</strong></p>
        <ul>
            <li>Este reporte muestra los cobros realizados por cada usuario en el período especificado.</li>
            <li>Los pagos parciales se desglosan en efectivo y QR según los detalles registrados.</li>
            <li>Los descuentos aplicados están incluidos en los montos totales.</li>
            <li>Reporte generado automáticamente por el sistema.</li>
        </ul>
    </div>

    <div style="margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; text-align: center; color: #7f8c8d;">
        <p>Sistema de Gestión de Pedidos - Todos los derechos reservados</p>
    </div>
</body>

</html>
