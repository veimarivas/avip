<div class="table-responsive">
    <table class="table table-sm table-hover">
        <thead class="table-light">
            <tr>
                <th>Pedido</th>
                <th>Fecha</th>
                <th class="text-end">Subtotal</th>
                <th class="text-end">Pagado</th>
                <th class="text-end">Descuento</th>
                <th class="text-end">Pendiente</th>
                <th class="text-end">Días</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($detalles as $detalle)
                <tr>
                    <td>#{{ $detalle['codigo'] }}</td>
                    <td>{{ $detalle['fecha'] }}</td>
                    <td class="text-end">Bs. {{ number_format($detalle['subtotal'], 2) }}</td>
                    <td class="text-end text-success">Bs. {{ number_format($detalle['pagado'], 2) }}</td>
                    <td class="text-end text-warning">Bs. {{ number_format($detalle['descuento'], 2) }}</td>
                    <td class="text-end text-danger fw-bold">Bs. {{ number_format($detalle['pendiente'], 2) }}</td>
                    <td class="text-end">
                        <span
                            class="badge {{ $detalle['dias_atraso'] > 30 ? 'bg-danger' : ($detalle['dias_atraso'] > 15 ? 'bg-warning' : 'bg-info') }}">
                            {{ $detalle['dias_atraso'] }} días
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="table-light">
            <tr>
                <th colspan="5" class="text-end">Deuda Total:</th>
                <th class="text-end text-danger fw-bold">Bs. {{ number_format($deudaTotal, 2) }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>

<div class="alert alert-info mt-3">
    <i class="ri-information-line me-2"></i>
    <strong>Resumen:</strong>
    {{ count($detalles) }} pedidos pendientes •
    Promedio por pedido: Bs. {{ count($detalles) > 0 ? number_format($deudaTotal / count($detalles), 2) : '0.00' }}
</div>
