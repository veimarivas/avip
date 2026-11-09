<div class="modal fade" id="modalGenerarPedidos{{ $tipoPedido }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-add-line me-2"></i>
                    Generar Pedidos {{ $titulo }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.pedidos.crear-para-localidad') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="localidade_id" value="{{ $localidadId }}">
                    <input type="hidden" name="tipo_pedido" value="{{ $tipoPedido }}">
                    <input type="hidden" name="codigo" value="{{ $codigo }}">

                    <div class="text-center">
                        <i class="ri-information-line display-4 text-primary mb-3"></i>
                        <h5>¿Generar Pedidos {{ $titulo }}?</h5>

                        @if ($tipoPedido == 0)
                            <div class="alert alert-info">
                                <i class="ri-calendar-line me-1"></i>
                                <strong>Pedidos Regulares:</strong> Se generan para el día siguiente
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="ri-calendar-event-line me-1"></i>
                                <strong>Pedidos {{ $titulo }}:</strong> Se generan para el día actual<br>
                                <small>Disponible solo el mismo día de los pedidos regulares</small>
                            </div>
                        @endif

                        <div class="mt-3 p-3 bg-light rounded">
                            <strong>Resumen:</strong><br>
                            - Localidad: {{ $localidadNombre }}<br>
                            - Código: {{ $codigo }}<br>
                            - Tipo: {{ $titulo }}<br>
                            - Clientes: {{ $totalClientes }}<br>
                            @if ($tipoPedido != 0 && isset($fechaPedidosRegulares))
                                - Fecha pedidos regulares:
                                {{ \Carbon\Carbon::parse($fechaPedidosRegulares)->format('d/m/Y') }}
                            @endif
                        </div>
                        
                        <div class="alert alert-secondary mt-3" id="alert-inactivos-{{ $tipoPedido }}" style="display: none;">
                            <i class="ri-forbid-line me-2"></i>
                            <strong>Clientes inactivos (no se incluirán):</strong>
                            <ul id="lista-inactivos-{{ $tipoPedido }}" class="mb-0 mt-2" style="max-height: 150px; overflow-y: auto;"></ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="ri-add-line me-1"></i> Generar Pedidos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('modalGenerarPedidos{{ $tipoPedido }}').addEventListener('shown.bs.modal', function() {
    var alert = document.getElementById('alert-inactivos-{{ $tipoPedido }}');
    alert.style.display = 'none';
    
    fetch("{{ url('/admin/clientes/inactivos') }}/{{ $localidadId }}")
        .then(function(r) { return r.json(); })
        .then(function(response) {
            var list = document.getElementById('lista-inactivos-{{ $tipoPedido }}');
            if (response.success && response.clientes.length > 0) {
                var html = '';
                response.clientes.forEach(function(c) {
                    html += '<li>' + c.ci + ' - ' + c.nombres + ' ' + c.apellidos + '</li>';
                });
                list.innerHTML = html;
                alert.style.display = 'block';
            }
        })
        .catch(function() {
            alert.style.display = 'none';
        });
});
</script>
