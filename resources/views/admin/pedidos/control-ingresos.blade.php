@extends('admin.dashboard')
@section('admin')
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-light rounded-3 p-3">
                    <div>
                        <h4 class="mb-1 fw-bold text-dark">Control de Ingresos por Usuario</h4>
                        <p class="text-muted mb-0">Reporte detallado de cobros por usuario en efectivo y QR</p>
                    </div>

                </div>
            </div>
        </div>

        <!-- Filtros Fijos -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border border-primary">
                    <div class="card-header bg-primary-subtle">
                        <h5 class="mb-0 text-primary">
                            <i class="ri-calendar-line me-2"></i>Filtro de Fechas
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="formFiltros">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio"
                                        value="{{ $fechaInicio }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin"
                                        value="{{ $fechaFin }}">
                                </div>
                                <div class="col-md-4 align-self-end">
                                    <div class="d-grid gap-2 d-md-flex">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-search-line me-1"></i> Generar Reporte
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
        <div class="row mb-4" id="totalesGeneralesContainer">
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate bg-primary bg-opacity-10 border-primary border-opacity-25">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Usuarios</p>
                                <h4 class="fs-22 fw-semibold mb-0" id="totalUsuarios">
                                    {{ $totalesGenerales['cantidad_usuarios'] }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-user-line display-4 text-primary"></i>
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
                                <p class="text-uppercase fw-medium text-muted mb-0">Total Efectivo</p>
                                <h4 class="fs-22 fw-semibold mb-0" id="totalEfectivo">Bs.
                                    {{ number_format($totalesGenerales['total_efectivo'], 2) }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-money-dollar-circle-line display-4 text-success"></i>
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
                                <p class="text-uppercase fw-medium text-muted mb-0">Total QR</p>
                                <h4 class="fs-22 fw-semibold mb-0" id="totalQr">Bs.
                                    {{ number_format($totalesGenerales['total_qr'], 2) }}
                                </h4>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-qr-code-line display-4 text-info"></i>
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
                                <p class="text-uppercase fw-medium text-muted mb-0">Total General</p>
                                <h4 class="fs-22 fw-semibold mb-0" id="totalGeneral">Bs.
                                    {{ number_format($totalesGenerales['total_general'], 2) }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="ri-bank-card-line display-4 text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reporte por Usuario -->
        <div class="row">
            <div class="col-12">
                <div id="tablaReporteContainer">
                    @include(
                        'admin.pedidos.partials.control-ingresos-table',
                        compact('reporte', 'totalesGenerales', 'fechaInicio', 'fechaFin'))
                </div>
            </div>
        </div>

        <!-- Modal para Detalles por Usuario -->
        <div class="modal fade" id="modalDetallesUsuario" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary-subtle">
                        <h5 class="modal-title text-primary">Detalles de Pagos por Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="detallesUsuarioContent">
                        <!-- Los detalles se cargarán aquí via JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('script')
        <script>
            $(document).ready(function() {
                // Función para cargar el reporte con AJAX
                function cargarReporteControlIngresos() {
                    const fechaInicio = $('#fecha_inicio').val();
                    const fechaFin = $('#fecha_fin').val();

                    $.ajax({
                        url: "{{ route('admin.control-ingresos') }}",
                        type: 'GET',
                        data: {
                            fecha_inicio: fechaInicio,
                            fecha_fin: fechaFin
                        },
                        dataType: 'json',
                        beforeSend: function() {
                            $('#tablaReporteContainer').html(`
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
                                $('#tablaReporteContainer').html(response.html);

                                // Actualizar totales si están en la respuesta
                                if (response.totalesGenerales) {
                                    const totales = response.totalesGenerales;

                                    // Actualizar los totales en las tarjetas
                                    $('#totalUsuarios').text(totales.cantidad_usuarios);
                                    $('#totalEfectivo').text('Bs. ' + parseFloat(totales.total_efectivo)
                                        .toFixed(2));
                                    $('#totalQr').text('Bs. ' + parseFloat(totales.total_qr).toFixed(2));
                                    $('#totalGeneral').text('Bs. ' + parseFloat(totales.total_general)
                                        .toFixed(2));
                                }
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                            $('#tablaReporteContainer').html(`
                                <div class="alert alert-danger">
                                    Error al cargar el reporte. Por favor, intente nuevamente.
                                </div>
                            `);
                        }
                    });
                }

                // Enviar formulario de filtros
                $('#formFiltros').on('submit', function(e) {
                    e.preventDefault();
                    cargarReporteControlIngresos();
                });

                // Cambio de fechas
                $('#fecha_inicio, #fecha_fin').on('change', function() {
                    cargarReporteControlIngresos();
                });

                // Limpiar filtros (establecer fecha de hoy)
                $('#btnLimpiarFiltros').click(function() {
                    const hoy = new Date().toISOString().split('T')[0];
                    $('#fecha_inicio').val(hoy);
                    $('#fecha_fin').val(hoy);
                    cargarReporteControlIngresos();
                });

                // Exportar a Excel
                $('#exportarExcel').click(function() {
                    const fechaInicio = $('#fecha_inicio').val();
                    const fechaFin = $('#fecha_fin').val();

                    const form = $('<form>', {
                        method: 'POST',
                        action: "{{ route('admin.control-ingresos.exportar-excel') }}"
                    });

                    form.append($('<input>', {
                        type: 'hidden',
                        name: '_token',
                        value: "{{ csrf_token() }}"
                    }));

                    form.append($('<input>', {
                        type: 'hidden',
                        name: 'fecha_inicio',
                        value: fechaInicio
                    }));

                    form.append($('<input>', {
                        type: 'hidden',
                        name: 'fecha_fin',
                        value: fechaFin
                    }));

                    $('body').append(form);
                    form.submit();
                    form.remove();
                });

                // Exportar a PDF
                $('#exportarPdf').click(function() {
                    const fechaInicio = $('#fecha_inicio').val();
                    const fechaFin = $('#fecha_fin').val();

                    const form = $('<form>', {
                        method: 'POST',
                        action: "{{ route('admin.control-ingresos.exportar-pdf') }}",
                        target: '_blank'
                    });

                    form.append($('<input>', {
                        type: 'hidden',
                        name: '_token',
                        value: "{{ csrf_token() }}"
                    }));

                    form.append($('<input>', {
                        type: 'hidden',
                        name: 'fecha_inicio',
                        value: fechaInicio
                    }));

                    form.append($('<input>', {
                        type: 'hidden',
                        name: 'fecha_fin',
                        value: fechaFin
                    }));

                    $('body').append(form);
                    form.submit();
                    form.remove();
                });

                // Ver detalles por usuario (usando event delegation para elementos cargados dinámicamente)
                $(document).on('click', '.ver-detalles-usuario', function() {
                    const usuarioData = $(this).data('usuario');
                    let html = `
                    <div class="mb-3">
                        <h6 class="text-primary">Usuario: ${usuarioData.usuario.name}</h6>
                        <p class="text-muted">${usuarioData.usuario.email}</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Recibo</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th class="text-end">Efectivo (Bs.)</th>
                                    <th class="text-end">QR (Bs.)</th>
                                    <th class="text-end">Total (Bs.)</th>
                                    <th>Tipo</th>
                                </tr>
                            </thead>
                            <tbody>`;

                    usuarioData.pagos_detalle.forEach(pago => {
                        html += `
                        <tr>
                            <td>${pago.recibo}</td>
                            <td>${pago.fecha}</td>
                            <td>${pago.cliente}</td>
                            <td class="text-end">${parseFloat(pago.efectivo).toFixed(2)}</td>
                            <td class="text-end">${parseFloat(pago.qr).toFixed(2)}</td>
                            <td class="text-end fw-bold">${parseFloat(pago.total).toFixed(2)}</td>
                            <td><span class="badge bg-${pago.tipo === 'efectivo' ? 'success' : pago.tipo === 'qr' ? 'info' : 'warning'}">${pago.tipo.toUpperCase()}</span></td>
                        </tr>`;
                    });

                    html += `
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">TOTALES:</th>
                                    <th class="text-end fw-bold text-success">${parseFloat(usuarioData.total_efectivo).toFixed(2)}</th>
                                    <th class="text-end fw-bold text-info">${parseFloat(usuarioData.total_qr).toFixed(2)}</th>
                                    <th class="text-end fw-bold text-warning">${parseFloat(usuarioData.total_general).toFixed(2)}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        Total de pagos realizados: <strong>${usuarioData.cantidad_pagos}</strong>
                    </div>`;

                    $('#detallesUsuarioContent').html(html);
                });

                // Cargar reporte al iniciar la página
                cargarReporteControlIngresos();
            });
        </script>
    @endpush
    @push('styles')
        <style>
            .card-animate {
                transition: all 0.3s ease;
            }

            .card-animate:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            }

            .avatar-title {
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
            }

            .table-hover tbody tr:hover {
                background-color: rgba(64, 81, 137, 0.05);
            }

            .badge-success {
                background-color: #2ca87f;
            }

            .badge-info {
                background-color: #405189;
            }

            .badge-warning {
                background-color: #f7b84b;
                color: #000;
            }
        </style>
    @endpush
@endsection
