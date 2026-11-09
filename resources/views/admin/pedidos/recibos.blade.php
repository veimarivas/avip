@extends('admin.dashboard')
@section('admin')
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-light rounded-3 p-3">
                    <div>
                        <h4 class="mb-1 fw-bold text-dark">Lista de Recibos</h4>
                        <p class="text-muted mb-0">Todos los recibos ordenados del más reciente al más antiguo</p>
                    </div>
                    <div class="page-title-right">
                        <button class="btn btn-primary btn-lg waves-effect waves-light" data-bs-toggle="modal"
                            data-bs-target="#modalFiltros">
                            <i class="ri-filter-line align-bottom me-1"></i> Filtros
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Filtros -->
        <div class="modal fade" id="modalFiltros" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Filtrar Recibos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="formFiltros" method="GET">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Búsqueda</label>
                                <input type="text" class="form-control" name="search"
                                    placeholder="Recibo, Cliente, CI..." value="{{ request('search') }}">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Fecha Desde</label>
                                        <input type="date" class="form-control" name="fecha_inicio"
                                            value="{{ request('fecha_inicio') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Fecha Hasta</label>
                                        <input type="date" class="form-control" name="fecha_fin"
                                            value="{{ request('fecha_fin') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tipo de Pago</label>
                                <select class="form-select" name="tipo_pago">
                                    <option value="">Todos</option>
                                    <option value="efectivo" {{ request('tipo_pago') == 'efectivo' ? 'selected' : '' }}>
                                        Efectivo</option>
                                    <option value="qr" {{ request('tipo_pago') == 'qr' ? 'selected' : '' }}>QR</option>
                                    <option value="parcial" {{ request('tipo_pago') == 'parcial' ? 'selected' : '' }}>
                                        Parcial</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate bg-primary bg-opacity-10 border-primary border-opacity-25">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Total Recibos</p>
                                <h4 class="fs-20 fw-semibold mb-0">{{ number_format($recibos->total()) }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-receipt-line display-4 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate bg-success bg-opacity-10 border-success border-opacity-25">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Efectivo</p>
                                <h4 class="fs-20 fw-semibold mb-0">Bs. {{ number_format($recibos->sum('pago_bs'), 2) }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-money-dollar-circle-line display-4 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate bg-warning bg-opacity-10 border-warning border-opacity-25">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Descuentos</p>
                                <h4 class="fs-20 fw-semibold mb-0">Bs. {{ number_format($recibos->sum('descuento_bs'), 2) }}
                                </h4>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-percent-line display-4 text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate bg-info bg-opacity-10 border-info border-opacity-25">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Total Pagado</p>
                                <h4 class="fs-20 fw-semibold mb-0">Bs. {{ number_format($recibos->sum('pago_total'), 2) }}
                                </h4>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-bank-card-line display-4 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Recibos -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table
                                class="table table-hover table-borderless table-centered align-middle table-nowrap mb-0">
                                <thead class="table-light text-muted fw-semibold">
                                    <tr>
                                        <th>Recibo</th>
                                        <th>Fecha y Hora</th>
                                        <th>Cliente</th>
                                        <th>CI</th>
                                        <th>Pedido</th>
                                        <th class="text-end">Monto Pagado</th>
                                        <th class="text-end">Descuento</th>
                                        <th>Tipo de Pago</th>
                                        <th>Cobrador</th>
                                        <th class="text-center">Detalles</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaRecibos">
                                    @forelse($recibos as $pago)
                                        @php
                                            $tipoClass = match ($pago->tipo) {
                                                'efectivo' => 'success',
                                                'qr' => 'primary',
                                                'parcial' => 'warning',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="avatar-sm rounded-circle bg-info bg-opacity-10">
                                                            <i class="ri-receipt-line fs-18 text-info"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-0">{{ $pago->recibo }}</h6>
                                                        <small class="text-muted">ID: {{ $pago->id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span
                                                        class="fw-medium">{{ $pago->created_at->format('d/m/Y') }}</span>
                                                    <small
                                                        class="text-muted">{{ $pago->created_at->format('h:i A') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($pago->cliente)
                                                    {{ $pago->cliente->nombres }} {{ $pago->cliente->apellidos }}
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($pago->cliente)
                                                    {{ $pago->cliente->ci }}
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($pago->pedido)
                                                    <span
                                                        class="badge bg-secondary">{{ $pago->pedido->codigo ?? 'N/A' }}</span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-bold text-success">Bs.
                                                    {{ number_format($pago->pago_bs, 2) }}</span>
                                            </td>
                                            <td class="text-end">
                                                @if ($pago->descuento_bs > 0)
                                                    <span class="fw-medium text-warning">Bs.
                                                        {{ number_format($pago->descuento_bs, 2) }}</span>
                                                @else
                                                    <span class="text-muted">Bs. 0.00</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $tipoClass }}">
                                                    {{ strtoupper($pago->tipo) }}
                                                </span>
                                                @if ($pago->detalles && $pago->detalles->count() > 1)
                                                    <span class="badge bg-secondary ms-1">
                                                        {{ $pago->detalles->count() }} detalles
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="avatar-xs">
                                                            <div class="avatar-title bg-light text-primary rounded-circle">
                                                                {{ substr($pago->cobrador, 0, 1) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2">
                                                        <span class="fw-medium">{{ $pago->cobrador }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if ($pago->detalles && $pago->detalles->count() > 0)
                                                    <button class="btn btn-sm btn-info ver-detalles"
                                                        data-bs-toggle="modal" data-bs-target="#modalDetalles"
                                                        data-pago-id="{{ $pago->id }}">
                                                        <i class="ri-eye-line"></i> Ver
                                                    </button>
                                                @else
                                                    <span class="text-muted">Sin detalles</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.recibos.ver', $pago->id) }}"
                                                        class="btn btn-info btn-sm" target="_blank" title="Ver Recibo">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <a href="{{ route('admin.recibos.pdf', $pago->id) }}"
                                                        class="btn btn-primary btn-sm" title="Descargar PDF">
                                                        <i class="ri-download-line"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="ri-inbox-line display-4"></i>
                                                    <p class="mt-2">No se encontraron recibos</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="row mt-3">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" role="status">
                                    Mostrando {{ $recibos->firstItem() ?? 0 }} a {{ $recibos->lastItem() ?? 0 }} de
                                    {{ $recibos->total() }} registros
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate paging_simple_numbers float-end">
                                    {{ $recibos->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para Detalles de Pago -->
        <div class="modal fade" id="modalDetalles" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary-subtle">
                        <h5 class="modal-title text-primary">Detalles del Pago</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="detallesPagoContent">
                        <!-- Los detalles se cargarán aquí via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('script')
        <script>
            $(document).ready(function() {
                // Ver detalles de pago
                $(document).on('click', '.ver-detalles', function() {
                    const pagoId = $(this).data('pago-id');

                    $.ajax({
                        url: "{{ route('admin.recibos.detalles') }}",
                        type: "GET",
                        data: {
                            pago_id: pagoId
                        },
                        success: function(response) {
                            $('#detallesPagoContent').html(response);
                        },
                        error: function() {
                            $('#detallesPagoContent').html(
                                '<div class="alert alert-danger">Error al cargar los detalles</div>'
                            );
                        }
                    });
                });

                // Filtro por fechas
                $('#formFiltros').submit(function(e) {
                    e.preventDefault();
                    const formData = $(this).serialize();
                    window.location.href = "{{ route('admin.recibos.listar') }}?" + formData;
                });
            });
        </script>
    @endpush
@endsection
