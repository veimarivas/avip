<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Reporte de Deudas - {{ $titulo }} - Código {{ $codigo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #dc3545;
        }

        .header .subtitle {
            font-size: 14px;
            color: #666;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 15px;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            font-size: 11px;
        }

        .info-item {
            margin: 3px 0;
        }

        .info-item strong {
            display: inline-block;
            width: 120px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10px;
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

        tfoot th {
            background-color: #e9ecef;
        }

        .resumen {
            margin-top: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .resumen-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            background: #f8f9fa;
            font-size: 11px;
        }

        .resumen-card h3 {
            margin: 0 0 8px 0;
            font-size: 12px;
            color: #dc3545;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }

        @media print {
            body {
                margin: 10px;
            }

            .header {
                margin-top: 0;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>REPORTE DE DEUDAS</h1>
        <div class="subtitle">{{ $titulo }} - Código {{ $codigo }}</div>
    </div>

    <div class="info-grid">
        <div class="info-item"><strong>Localidad:</strong> {{ $localidad->nombre }}</div>
        <div class="info-item"><strong>Código:</strong> {{ $codigo }}</div>
        <div class="info-item"><strong>Tipo:</strong> {{ $titulo }}</div>
        <div class="info-item"><strong>Fecha:</strong> {{ $fecha }}</div>
        <div class="info-item"><strong>Total Clientes:</strong> {{ $todosClientes->count() }}</div>
        <div class="info-item"><strong>Clientes con Deuda:</strong> {{ $clientesConDeuda->count() }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="30">#</th>
                <th>Cliente</th>
                <th width="100" class="text-end">Deuda (Bs.)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($clientesConDeuda as $index => $pedido)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $pedido->cliente->nombres }} {{ $pedido->cliente->apellidos }}</td>
                    <td class="text-end">Bs. {{ number_format($pedido->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-end">TOTAL DEUDA:</th>
                <th class="text-end">Bs. {{ number_format($totalDeuda, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="resumen">
        <div class="resumen-card">
            <h3>RESUMEN DE DEUDAS</h3>
            <p><strong>Total Clientes con Deuda:</strong> {{ $clientesConDeuda->count() }}</p>
            <p><strong>Total Deuda:</strong> Bs. {{ number_format($totalDeuda, 2) }}</p>
            <p><strong>Deuda Promedio:</strong> Bs.
                {{ $clientesConDeuda->count() > 0 ? number_format($totalDeuda / $clientesConDeuda->count(), 2) : '0.00' }}
            </p>
        </div>
        <div class="resumen-card">
            <h3>INFORMACIÓN ADICIONAL</h3>
            <p><strong>Fecha de Generación:</strong> {{ $fechaGeneracion }}</p>
            <p><strong>Localidad:</strong> {{ $localidad->nombre }}</p>
            <p><strong>Tipo de Pedido:</strong> {{ $titulo }}</p>
        </div>
    </div>

    <div class="footer">
        Generado el {{ $fechaGeneracion }} | Sistema de Pedidos
    </div>
</body>

</html>
