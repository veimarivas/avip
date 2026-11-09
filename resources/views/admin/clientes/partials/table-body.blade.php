<tbody id="clientesTableBody">
    @if (isset($clientes) && count($clientes) > 0)
        @foreach ($clientes as $n => $cliente)
            @php
                // Calcular deuda
                if (isset($cliente->deuda_calculada_exacta)) {
                    $deuda = $cliente->deuda_calculada_exacta;
                } else {
                    $deuda = $cliente->deuda_calculada ?? 0;
                }

                // Si aún no está calculada, calcular manualmente
                if ($deuda == 0) {
                    $deuda = $cliente
                        ->pedidos()
                        ->where('pagado', 0)
                        ->selectRaw(
                            'COALESCE(SUM(
                            CASE 
                                WHEN (subtotal - IFNULL(pagado_bs, 0) - IFNULL(descuento_bs, 0)) > 0 
                                THEN (subtotal - IFNULL(pagado_bs, 0) - IFNULL(descuento_bs, 0))
                                ELSE 0 
                            END
                        ), 0) as deuda',
                        )
                        ->value('deuda');
                }

                $deuda = floatval($deuda);

                // Índice simple para todas las listas
                $index = $n + 1;
            @endphp

            <tr>
                <td class="text-center">{{ $index }}</td>
                <td>{{ $cliente->apellidos }}</td>
                <td>{{ $cliente->nombres }}</td>
                <td class="text-center">
                    <span class="badge {{ $deuda > 0 ? 'bg-danger' : 'bg-success' }}"
                        title="Deuda total: Bs. {{ number_format($deuda, 2) }}&#013;Incluye pagos parciales y descuentos">
                        Bs. {{ number_format($deuda, 2) }}
                    </span>
                </td>
                <td class="text-center">
                    @if ($cliente->localidad)
                        <span class="localidad-badge" style="background-color: {{ $cliente->localidad->color }};">
                            <i class="ri-map-pin-fill me-1"></i>{{ $cliente->localidad->nombre }}
                        </span>
                    @else
                        <span class="text-muted">Sin localidad</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($cliente->habilitado)
                        <span class="badge bg-success">Activo</span>
                    @else
                        <span class="badge bg-secondary">Inactivo</span>
                    @endif
                </td>
                <td class="text-center">
                    <div class="btn-group" role="group">
                        @if (Auth::guard('web')->user()->can('clientes.ver'))
                            <a href="{{ route('admin.clientes.ver', $cliente->id) }}" title="Ver Cliente"
                                class="btn btn-primary btn-sm">
                                <i class="ri-eye-line"></i>
                            </a>
                        @endif
                        @if (Auth::guard('web')->user()->can('clientes.editar'))
                            @if($cliente->habilitado)
                                @if($deuda == 0)
                                    <button type="button" title="Deshabilitar Cliente"
                                        class="btn btn-secondary btn-sm toggleHabilitadoBtn"
                                        data-id="{{ $cliente->id }}"
                                        data-habilitado="{{ $cliente->habilitado }}">
                                        <i class="ri-forbid-line"></i>
                                    </button>
                                @else
                                    <button type="button" title="No se puede deshabilitar: tiene deuda"
                                        class="btn btn-secondary btn-sm" disabled style="opacity: 0.5;">
                                        <i class="ri-forbid-line"></i>
                                    </button>
                                @endif
                            @else
                                <button type="button" title="Habilitar Cliente"
                                    class="btn btn-success btn-sm toggleHabilitadoBtn"
                                    data-id="{{ $cliente->id }}"
                                    data-habilitado="{{ $cliente->habilitado }}">
                                    <i class="ri-checkbox-circle-line"></i>
                                </button>
                            @endif
                            <button type="button" title="Editar Datos Cliente" class="btn btn-warning btn-sm editBtn"
                                data-bs-obj='@json($cliente)' data-bs-toggle="modal"
                                data-bs-target="#modalModificar">
                                <i class="ri-edit-line"></i>
                            </button>
                        @endif
                        @if (Auth::guard('web')->user()->can('clientes.eliminar'))
                            <button type="button" title="Eliminar Cliente" class="btn btn-danger btn-sm deleteBtn"
                                data-bs-obj='@json($cliente)' data-bs-toggle="modal"
                                data-bs-target="#modalEliminar">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    @else
        <tr>
            <td colspan="7" class="text-center py-4">
                <div class="text-muted">
                    <i class="ri-user-search-line display-4"></i>
                    <p class="mt-2">No se encontraron clientes</p>
                </div>
            </td>
        </tr>
    @endif
</tbody>
