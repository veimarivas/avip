@extends('admin.dashboard')
@section('admin')
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-light rounded-3 p-3">
                    <div>
                        <h4 class="mb-1 fw-bold text-dark">Listado de Recibos</h4>
                        <p class="text-muted mb-0">Todos los recibos generados en el sistema</p>
                    </div>
                    <div class="page-title-right">
                        <button type="button" class="btn btn-primary btn-lg waves-effect waves-light" id="btnExportarExcel">
                            <i class="ri-file-excel-line align-bottom me-1"></i> Exportar Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border border-primary">
                    <div class="card-header bg-primary-subtle">
                        <h5 class="mb-0 text-primary">
                            <i class="ri-filter-line me-2"></i>Filtros de Búsqueda
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="formFiltros" method="GET" action="{{ route('admin.recibos.listar') }}">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="search" class="form-label">Buscar</label>
                                    <input type="text" class="form-control" id="search" name="search"
                                        placeholder="Recibo, CI, Nombre..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio"
                                        value="{{ request('fecha_inicio') }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin"
                                        value="{{ request('fecha_fin') }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="tipo_pago" class="form-label">Tipo de Pago</label>
                                    <select class="form-select" id="tipo_pago" name="tipo_pago">
                                        <option value="">Todos</option>
                                        <option value="efectivo" {{ request('tipo_pago') == 'efectivo' ? 'selected' : '' }}>
                                            Efectivo</option>
                                        <option value="qr" {{ request('tipo_pago') == 'qr' ? 'selected' : '' }}>QR
                                        </option>
                                        <option value="parcial" {{ request('tipo_pago') == 'parcial' ? 'selected' : '' }}>
                                            Parcial</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="localidad_id" class="form-label">Localidad</label>
                                    <select class="form-select" id="localidad_id" name="localidad_id">
                                        <option value="">Todas</option>
                                        @foreach ($localidades as $localidad)
                                            <option value="{{ $localidad->id }}"
                                                {{ request('localidad_id') == $localidad->id ? 'selected' : '' }}>
                                                {{ $localidad->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-search-line"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Totales -->
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-body text-center p-3">
                        <p class="mb-1 text-muted small">Total Pagado</p>
                        <p class="mb-0 fw-bold text-success fs-4">Bs. {{ number_format($totalPagado, 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-warning">
                    <div class="card-body text-center p-3">
                        <p class="mb-1 text-muted small">Total Descuento</p>
                        <p class="mb-0 fw-bold text-warning fs-4">Bs. {{ number_format($totalDescuento, 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-primary">
                    <div class="card-body text-center p-3">
                        <p class="mb-1 text-muted small">Total General</p>
                        <p class="mb-0 fw-bold text-primary fs-4">Bs. {{ number_format($totalGeneral, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Recibos -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive" id="tablaRecibosContainer">
                            @include('admin.recibos.partials.table-body', ['pagos' => $pagos])
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
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando detalles del pago...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            // Búsqueda con AJAX
            $('#formFiltros').on('submit', function(e) {
                e.preventDefault();
                cargarRecibos();
            });

            // Paginación con AJAX
            $(document).on('click', '.pagination a', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                cargarRecibos(url);
            });

            // Función para cargar recibos con AJAX
            function cargarRecibos(url = null) {
                const formData = $('#formFiltros').serialize();
                const targetUrl = url || "{{ route('admin.recibos.listar') }}";

                $.ajax({
                    url: targetUrl,
                    type: 'GET',
                    data: formData,
                    beforeSend: function() {
                        $('#tablaRecibosContainer').html(`
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-2 text-muted">Cargando recibos...</p>
                            </div>
                        `);
                    },
                    success: function(response) {
                        if (response.html) {
                            $('#tablaRecibosContainer').html(response.html);

                            // Actualizar totales si están en la respuesta
                            if (response.totales) {
                                $('.card-border-success .fs-4').text('Bs. ' + response.totales.pagado);
                                $('.card-border-warning .fs-4').text('Bs. ' + response.totales
                                    .descuento);
                                $('.card-border-primary .fs-4').text('Bs. ' + response.totales.general);
                            }
                        }
                    },
                    error: function() {
                        $('#tablaRecibosContainer').html(`
                            <div class="alert alert-danger">
                                Error al cargar los recibos. Por favor, intente nuevamente.
                            </div>
                        `);
                    }
                });
            }

            // Exportar a Excel
            $('#btnExportarExcel').click(function() {
                const formData = $('#formFiltros').serialize();
                const url = "{{ route('admin.recibos.listar') }}?" + formData + "&export=excel";

                window.location.href = url;
            });

            // Ver detalles del pago
            $(document).on('click', '.btnVerDetalles', function() {
                const pagoId = $(this).data('pago-id');

                $.ajax({
                    url: "{{ route('admin.recibos.obtener-detalles') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        pago_id: pagoId
                    },
                    beforeSend: function() {
                        $('#detallesPagoContent').html(`
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-2 text-muted">Cargando detalles del pago...</p>
                            </div>
                        `);
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#detallesPagoContent').html(response.html);
                        } else {
                            $('#detallesPagoContent').html(`
                                <div class="alert alert-danger">
                                    Error al cargar los detalles del pago.
                                </div>
                            `);
                        }
                    },
                    error: function() {
                        $('#detallesPagoContent').html(`
                            <div class="alert alert-danger">
                                Error al cargar los detalles del pago.
                            </div>
                        `);
                    }
                });

                $('#modalDetalles').modal('show');
            });

            // Limpiar filtros
            $('#btnLimpiarFiltros').click(function() {
                $('#formFiltros')[0].reset();
                cargarRecibos();
            });
        });
    </script>

    <style>
        .table-hover tbody tr:hover {
            background-color: rgba(64, 81, 137, 0.05);
        }

        .badge-efectivo {
            background-color: #2ca87f;
            color: white;
        }

        .badge-qr {
            background-color: #405189;
            color: white;
        }

        .badge-parcial {
            background-color: #f7b84b;
            color: #000;
        }
    </style>
@endpush
