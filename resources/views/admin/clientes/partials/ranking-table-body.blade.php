@if (isset($clientes) && $clientes->count() > 0)
    @foreach ($clientes as $n => $cliente)
        @php
            $deuda = $cliente->deuda_calculada ?? 0;
            $deuda = floatval($deuda);

            $posicion = ($clientes->currentPage() - 1) * $clientes->perPage() + $n + 1;

            $posicionClass = 'ranking-other';
            if ($posicion === 1) {
                $posicionClass = 'ranking-1';
            } elseif ($posicion === 2) {
                $posicionClass = 'ranking-2';
            } elseif ($posicion === 3) {
                $posicionClass = 'ranking-3';
            } elseif ($posicion >= 4 && $posicion <= 10) {
                $posicionClass = 'ranking-4-10';
            }

            $barClass = 'deuda-low';
            if ($deuda > 500) {
                $barClass = 'deuda-high';
            } elseif ($deuda > 200) {
                $barClass = 'deuda-medium';
            }
        @endphp

        <tr data-deuda="{{ $deuda }}">
            <td class="text-center">
                <span class="ranking-badge {{ $posicionClass }}">
                    {{ $posicion }}
                </span>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-xs">
                            <div class="avatar-title bg-soft-primary text-primary rounded-circle">
                                {{ substr($cliente->nombres, 0, 1) }}{{ substr($cliente->apellidos, 0, 1) }}
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-2">
                        <strong>{{ $cliente->nombres }} {{ $cliente->apellidos }}</strong>
                        <div class="text-muted small">ID: {{ $cliente->id }}</div>
                    </div>
                </div>
            </td>
            <td class="text-center">
                @if ($cliente->localidad)
                    <span class="badge" style="background-color: {{ $cliente->localidad->color }}; color: white;">
                        <i class="ri-map-pin-fill me-1"></i>{{ $cliente->localidad->nombre }}
                    </span>
                @else
                    <span class="text-muted">Sin localidad</span>
                @endif
            </td>
            <td class="text-center">
                <div class="d-flex flex-column">
                    <div class="mb-1">
                        <span class="h5 text-danger fw-bold">
                            Bs. {{ number_format($deuda, 2) }}
                        </span>
                    </div>
                    <div class="progress progress-deuda">
                        <div class="deuda-bar {{ $barClass }}"
                            style="width: {{ min(($deuda / 1000) * 100, 100) }}%"
                            title="Deuda: Bs. {{ number_format($deuda, 2) }}">
                        </div>
                    </div>
                    <div class="small text-muted mt-1">
                        @if ($deuda > 1000)
                            <i class="ri-alarm-warning-line text-danger me-1"></i>Deuda Alta
                        @elseif($deuda > 500)
                            <i class="ri-information-line text-warning me-1"></i>Deuda Media
                        @else
                            <i class="ri-check-line text-success me-1"></i>Deuda Moderada
                        @endif
                    </div>
                </div>
            </td>
            <td class="text-center">
                <div class="btn-group" role="group">
                    @if (Auth::guard('web')->user()->can('clientes.ver'))
                        <a href="{{ route('admin.clientes.ver', $cliente->id) }}" title="Ver Detalles del Cliente"
                            class="btn btn-primary btn-sm">
                            <i class="ri-eye-line"></i>
                        </a>
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
                <p class="mt-2">No se encontraron clientes con deuda</p>
                <p class="small">Todos los clientes están al día con sus pagos</p>
            </div>
        </td>
    </tr>
@endif
