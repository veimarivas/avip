@extends('admin.dashboard')
@section('admin')
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-light rounded-3 p-3">
                    <div>
                        <h4 class="mb-1 fw-bold text-dark">Reporte de Cobros Diarios</h4>
                        <p class="text-muted mb-0">Cobros realizados por día, desglosados por usuario y tipo de pago</p>
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
                        <form id="formFiltros" method="GET" action="{{ route('admin.reportes.cobros-diarios') }}">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="fecha" class="form-label">Fecha</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha"
                                        value="{{ $fecha }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="usuario_id" class="form-label">Usuario</label>
                                    <select class="form-select" id="usuario_id" name="usuario_id">
                                        <option value="">Todos los usuarios</option>
                                        @foreach ($usuarios as $usuario)
                                            <option value="{{ $usuario->id }}"
                                                {{ $usuarioId == $usuario->id ? 'selected' : '' }}>
                                                {{ $usuario->name }}
                                                @if ($usuario->persona)
                                                    ({{ $usuario->persona->nombres ?? '' }}
                                                    {{ $usuario->persona->apellido_paterno ?? '' }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="tipo_pago" class="form-label">Tipo de Pago</label>
                                    <select class="form-select" id="tipo_pago" name="tipo_pago">
                                        <option value="">Todos los tipos</option>
                                        <option value="efectivo" {{ $tipoPago == 'efectivo' ? 'selected' : '' }}>Efectivo
                                        </option>
                                        <option value="qr" {{ $tipoPago == 'qr' ? 'selected' : '' }}>QR</option>
                                        <option value="parcial" {{ $tipoPago == 'parcial' ? 'selected' : '' }}>Parcial
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-3 align-self-end">
                                    <div class="d-grid gap-2 d-md-flex">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-search-line me-1"></i> Buscar
                                        </button>
                                        <button type="button" class="btn btn-secondary" id="btnLimpiarFiltros">
                                            <i class="ri-refresh-line me-1"></i> Limpiar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Totales Generales -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body text-center p-3">
                        <p class="mb-1 text-muted small">Total Efectivo</p>
                        <p class="mb-0 fw-bold text-success fs-4">Bs.
                            {{ number_format($totalesGenerales['efectivo'] + $totalesGenerales['parcial_efectivo'], 2) }}
                        </p>
                        <small class="text-muted">
                            (Directo: {{ number_format($totalesGenerales['efectivo'], 2) }},
                            Parcial: {{ number_format($totalesGenerales['parcial_efectivo'], 2) }})
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body text-center p-3">
                        <p class="mb-1 text-muted small">Total QR</p>
                        <p class="mb-0 fw-bold text-primary fs-4">Bs.
                            {{ number_format($totalesGenerales['qr'] + $totalesGenerales['parcial_qr'], 2) }}</p>
                        <small class="text-muted">
                            (Directo: {{ number_format($totalesGenerales['qr'], 2) }},
                            Parcial: {{ number_format($totalesGenerales['parcial_qr'], 2) }})
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body text-center p-3">
                        <p class="mb-1 text-muted small">Total Descuentos</p>
                        <p class="mb-0 fw-bold text-warning fs-4">Bs.
                            {{ number_format($totalesGenerales['descuento'], 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-dark">
                    <div class="card-body text-center p-3">
                        <p class="mb-1 text-muted small">Total General</p>
                        <p class="mb-0 fw-bold text-dark fs-4">Bs. {{ number_format($totalesGenerales['total'], 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Cobros por Usuario -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive" id="tablaCobrosContainer">
                            @include('admin.reportes.partials.cobros-diarios-table', [
                                'cobrosPorUsuario' => $cobrosPorUsuario,
                                'totalesGenerales' => $totalesGenerales,
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Detalles por Usuario -->
    <div class="modal fade" id="modalDetallesUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary-subtle">
                    <h5 class="modal-title text-primary">Detalles de Cobros por Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detallesUsuarioContent">
                    <!-- Los detalles se cargarán aquí via AJAX -->
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando detalles...</p>
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
                cargarCobrosDiarios();
            });

            // Cambio de fecha o usuario
            $('#fecha, #usuario_id, #tipo_pago').on('change', function() {
                cargarCobrosDiarios();
            });

            // Limpiar filtros
            $('#btnLimpiarFiltros').click(function() {
                $('#formFiltros')[0].reset();
                $('#fecha').val('{{ date('Y-m-d') }}');
                cargarCobrosDiarios();
            });

            // Función para cargar cobros diarios con AJAX
            function cargarCobrosDiarios() {
                const formData = $('#formFiltros').serialize();

                $.ajax({
                    url: "{{ route('admin.reportes.cobros-diarios') }}",
                    type: 'GET',
                    data: formData,
                    beforeSend: function() {
                        $('#tablaCobrosContainer').html(`
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-2 text-muted">Cargando reporte...</p>
                            </div>
                        `);
                    },
                    success: function(response) {
                        if (response.html) {
                            $('#tablaCobrosContainer').html(response.html);

                            // Actualizar totales si están en la respuesta
                            if (response.totales) {
                                const totales = response.totales;
                                const totalEfectivo = parseFloat(totales.efectivo || 0) + parseFloat(
                                    totales.parcial_efectivo || 0);
                                const totalQr = parseFloat(totales.qr || 0) + parseFloat(totales
                                    .parcial_qr || 0);

                                $('.card-border-success .fs-4').text('Bs. ' + totalEfectivo.toFixed(2));
                                $('.card-border-success small').text('(Directo: ' + parseFloat(totales
                                    .efectivo || 0).toFixed(2) + ', Parcial: ' + parseFloat(
                                    totales.parcial_efectivo || 0).toFixed(2) + ')');

                                $('.card-border-primary .fs-4').text('Bs. ' + totalQr.toFixed(2));
                                $('.card-border-primary small').text('(Directo: ' + parseFloat(totales
                                    .qr || 0).toFixed(2) + ', Parcial: ' + parseFloat(totales
                                    .parcial_qr || 0).toFixed(2) + ')');

                                $('.card-border-warning .fs-4').text('Bs. ' + parseFloat(totales
                                    .descuento || 0).toFixed(2));
                                $('.card-border-dark .fs-4').text('Bs. ' + parseFloat(totales.total ||
                                    0).toFixed(2));
                            }
                        }
                    },
                    error: function() {
                        $('#tablaCobrosContainer').html(`
                            <div class="alert alert-danger">
                                Error al cargar el reporte. Por favor, intente nuevamente.
                            </div>
                        `);
                    }
                });
            }

            // Inicializar carga
            cargarCobrosDiarios();

            // Ver detalles por usuario
            $(document).on('click', '.btnVerDetallesUsuario', function() {
                const usuarioId = $(this).data('usuario-id');
                const fecha = $('#fecha').val();

                $.ajax({
                    url: "{{ route('admin.reportes.cobros-diarios.detalles-usuario') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        usuario_id: usuarioId,
                        fecha: fecha
                    },
                    beforeSend: function() {
                        $('#detallesUsuarioContent').html(`
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-2 text-muted">Cargando detalles...</p>
                            </div>
                        `);
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#detallesUsuarioContent').html(response.html);
                        } else {
                            $('#detallesUsuarioContent').html(`
                                <div class="alert alert-danger">
                                    Error al cargar los detalles.
                                </div>
                            `);
                        }
                    },
                    error: function() {
                        $('#detallesUsuarioContent').html(`
                            <div class="alert alert-danger">
                                Error al cargar los detalles.
                            </div>
                        `);
                    }
                });

                $('#modalDetallesUsuario').modal('show');
            });

            // Exportar a Excel
            $('#btnExportarExcel').click(function() {
                const formData = $('#formFiltros').serialize();
                const url = "{{ route('admin.reportes.cobros-diarios') }}?" + formData + "&export=excel";

                window.location.href = url;
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

        .bg-efectivo {
            background-color: rgba(44, 168, 127, 0.1);
        }

        .bg-qr {
            background-color: rgba(64, 81, 137, 0.1);
        }

        .bg-parcial-efectivo {
            background-color: rgba(247, 184, 75, 0.1);
        }

        .bg-parcial-qr {
            background-color: rgba(13, 202, 240, 0.1);
        }
    </style>
@endpush
