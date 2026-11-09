@extends('admin.dashboard')
@section('admin')
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0">Ranking de Clientes con Mayor Deuda</h4>
                        <p class="text-muted mb-0">Clientes ordenados de mayor a menor deuda</p>
                    </div>
                    <div class="page-title-right">
                        <button type="button" class="btn btn-info" onclick="window.print()">
                            <i class="ri-printer-line align-middle me-1"></i> Imprimir Ranking
                        </button>
                        <a href="{{ route('admin.clientes.listar') }}" class="btn btn-outline-primary">
                            <i class="ri-arrow-left-line align-middle me-1"></i> Volver a Clientes
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="card card-height-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-danger bg-soft text-danger rounded-2">
                                    <i class="ri-money-dollar-circle-line font-size-24"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1">Deuda Total</p>
                                <h4 class="mb-0">Bs. {{ number_format($totalDeuda, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-height-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-warning bg-soft text-warning rounded-2">
                                    <i class="ri-user-line font-size-24"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1">Total Clientes</p>
                                <h4 class="mb-0">{{ $clientes->total() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-height-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-info bg-soft text-info rounded-2">
                                    <i class="ri-calculator-line font-size-24"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1">Deuda Promedio</p>
                                <h4 class="mb-0">Bs. {{ number_format($promedioDeuda, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-height-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-success bg-soft text-success rounded-2">
                                    <i class="ri-trophy-line font-size-24"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1">Mayor Deuda</p>
                                <h4 class="mb-0">
                                    @if ($clienteMayorDeuda)
                                        Bs. {{ number_format($clienteMayorDeuda->deuda_calculada ?? 0, 2) }}
                                    @else
                                        Bs. 0.00
                                    @endif
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="search-box">
                                    <input type="text" id="searchInput" class="form-control"
                                        placeholder="Buscar cliente por CI, nombres o apellidos...">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select id="localidadFilter" class="form-select">
                                    <option value="">Todas las Localidades</option>
                                    @foreach ($localidades as $localidad)
                                        <option value="{{ $localidad->id }}">
                                            {{ $localidad->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select id="limitFilter" class="form-select">
                                    <option value="10" {{ $limit == 10 ? 'selected' : '' }}>Top 10</option>
                                    <option value="25" {{ $limit == 25 ? 'selected' : '' }}>Top 25</option>
                                    <option value="50" {{ $limit == 50 ? 'selected' : '' }}>Top 50</option>
                                    <option value="100" {{ $limit == 100 ? 'selected' : '' }}>Top 100</option>
                                    <option value="0">Todos</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="clearFilters" class="btn btn-outline-secondary w-100">
                                    <i class="ri-refresh-line me-1"></i> Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Ranking de Deudas</h5>
                    </div>
                    <!-- En la sección de Results Section, línea aproximadamente 54 -->
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-centered mb-0">
                                <thead class="table-light">
                                    <tr class="text-center">
                                        <th width="5%">#</th>
                                        <th>Cliente</th>
                                        <th>Localidad</th>
                                        <th width="15%">Deuda Total</th>
                                        <th width="10%">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="rankingTableBody">
                                    @include('admin.clientes.partials.ranking-table-body', [
                                        'clientes' => $clientes,
                                    ])
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Mostrando <span id="showing-count">{{ $clientes->firstItem() ?? 0 }}</span>
                                a <span id="to-count">{{ $clientes->lastItem() ?? 0 }}</span>
                                de <span id="total-count">{{ $clientes->total() ?? 0 }}</span> registros
                            </div>
                            <div id="pagination-container">
                                {{ $clientes->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Distribución de Deudas</h5>
                    </div>
                    <div class="card-body">
                        <div class="position-relative">
                            <canvas id="deudaChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Detalles de Deuda -->
    <div class="modal fade" id="modalDetallesDeuda" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info bg-soft">
                    <h5 class="modal-title text-white">Detalles de Deuda</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Los detalles se cargarán aquí via AJAX -->
                    <div class="text-center py-4">
                        <div class="spinner-border text-info" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando detalles de deuda...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <a href="#" class="btn btn-primary btn-ir-pagos" target="_blank">
                        <i class="ri-money-dollar-circle-line me-1"></i> Ir a Pagos
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        .ranking-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            font-weight: bold;
            color: white;
        }

        .ranking-1 {
            background: linear-gradient(135deg, #FFD700, #FFA500);
        }

        .ranking-2 {
            background: linear-gradient(135deg, #C0C0C0, #A9A9A9);
        }

        .ranking-3 {
            background: linear-gradient(135deg, #CD7F32, #8B4513);
        }

        .ranking-4-10 {
            background: linear-gradient(135deg, #14503c, #0d3428);
        }

        .ranking-other {
            background: linear-gradient(135deg, #6c757d, #495057);
        }

        .progress-deuda {
            height: 8px;
            border-radius: 4px;
        }

        .deuda-bar {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .deuda-high {
            background: linear-gradient(90deg, #dc3545, #ff6b7f);
        }

        .deuda-medium {
            background: linear-gradient(90deg, #fd7e14, #ffa94d);
        }

        .deuda-low {
            background: linear-gradient(90deg, #198754, #20c997);
        }

        .stats-card {
            transition: transform 0.2s;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .print-only {
            display: none;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .print-only {
                display: block !important;
            }

            .card {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
            }

            .table {
                font-size: 12px;
            }

            h4,
            h5 {
                color: #000 !important;
            }
        }
    </style>
@endsection

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function() {
            let chartInstance = null;
            let debounceTimer;

            // Inicializar gráfico Chart.js
            function initChart() {
                const ctx = document.getElementById('deudaChart').getContext('2d');

                if (chartInstance) {
                    chartInstance.destroy();
                }

                chartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: [],
                        datasets: [{
                            label: 'Deuda (Bs.)',
                            data: [],
                            backgroundColor: [
                                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                                '#9966FF', '#FF9F40', '#8AC926', '#1982C4',
                                '#6A4C93', '#FF595E'
                            ],
                            borderColor: '#ffffff',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            title: {
                                display: true,
                                text: 'Top 10 Clientes con Mayor Deuda',
                                font: {
                                    size: 16,
                                    weight: 'bold'
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `Deuda: Bs. ${context.parsed.y.toLocaleString('es-ES', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                })}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Monto (Bs.)'
                                },
                                ticks: {
                                    callback: function(value) {
                                        return 'Bs. ' + value.toLocaleString('es-ES');
                                    }
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Clientes'
                                },
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 45
                                }
                            }
                        }
                    }
                });
            }

            // Actualizar gráfico con datos
            function updateChart() {
                if (!chartInstance) {
                    initChart();
                }

                const nombres = [];
                const deudas = [];

                // Obtener datos de la tabla actual (top 10)
                $('#rankingTableBody tr').each(function(index) {
                    if (index < 10) {
                        const nombreElement = $(this).find('td:nth-child(3)');
                        let nombre = '';

                        if (nombreElement.find('strong').length) {
                            nombre = nombreElement.find('strong').text().trim();
                        } else {
                            nombre = nombreElement.text().trim();
                        }

                        // Obtener deuda del atributo data-deuda o del texto
                        const deudaElement = $(this);
                        let deudaText = deudaElement.data('deuda');

                        if (!deudaText) {
                            deudaText = deudaElement.find('td:nth-child(6) .h5.text-danger.fw-bold').text()
                                .trim();
                            deudaText = deudaText.replace('Bs.', '').replace(',', '').trim();
                        }

                        const deuda = parseFloat(deudaText) || 0;

                        if (nombre && !isNaN(deuda) && deuda > 0) {
                            // Acortar nombre para el gráfico
                            const nombreCorto = nombre.length > 20 ? nombre.substring(0, 20) + '...' :
                                nombre;
                            nombres.push(nombreCorto);
                            deudas.push(deuda);
                        }
                    }
                });

                // Si no hay datos, mostrar mensaje
                if (nombres.length === 0) {
                    nombres.push('Sin datos');
                    deudas.push(0);
                }

                // Actualizar gráfico
                chartInstance.data.labels = nombres;
                chartInstance.data.datasets[0].data = deudas;
                chartInstance.update();
            }

            // Función para cargar ranking
            function loadRanking(search = '', localidadId = '', limit = 50) {
                const url = '{{ route('admin.clientes.ranking-deudas') }}';

                $('#rankingTableBody').html(`
            <tr>
                <td colspan="7" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando ranking de deudas...</p>
                </td>
            </tr>
        `);

                $.ajax({
                    url: url,
                    method: 'GET',
                    data: {
                        search: search,
                        localidad_id: localidadId,
                        limit: limit,
                        ajax: true
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log('Respuesta recibida:', response);

                        if (response.success) {
                            $('#rankingTableBody').html(response.html);
                            $('#pagination-container').html(response.pagination);
                            updateCounters(response);

                            // Actualizar stats
                            if (response.stats) {
                                updateStats(response.stats);
                            }

                            // Actualizar gráfico después de un pequeño delay
                            setTimeout(() => {
                                updateChart();
                            }, 300);

                            // Inicializar tooltips
                            initTooltips();
                        } else {
                            showNotification('error', response.error || 'Error al cargar el ranking');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error al cargar ranking:', xhr.responseText);
                        showNotification('error', 'Error al cargar el ranking');
                        $('#rankingTableBody').html(`
                    <tr>
                        <td colspan="7" class="text-center text-danger py-4">
                            Error al cargar los datos. Intente nuevamente.
                        </td>
                    </tr>
                `);
                    }
                });
            }

            // Actualizar estadísticas
            function updateStats(stats) {
                $('.card:nth-child(1) h4').text('Bs. ' + parseFloat(stats.total_deuda || 0).toLocaleString(
                    'es-ES', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));

                $('.card:nth-child(2) h4').text(stats.total_clientes || 0);

                $('.card:nth-child(3) h4').text('Bs. ' + parseFloat(stats.promedio_deuda || 0).toLocaleString(
                    'es-ES', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));

                $('.card:nth-child(4) h4').text('Bs. ' + parseFloat(stats.mayor_deuda || 0).toLocaleString(
                    'es-ES', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
            }

            // Actualizar contadores
            function updateCounters(response) {
                const total = response.total || 0;
                const from = response.from || 0;
                const to = response.to || 0;
                $('#showing-count').text(from);
                $('#to-count').text(to);
                $('#total-count').text(total);
            }

            // Inicializar tooltips
            function initTooltips() {
                $('[data-bs-toggle="tooltip"]').tooltip();
            }

            // Mostrar notificación
            function showNotification(type, message) {
                const toast = $(`
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);

                $('.toast-container').append(toast);
                const bsToast = new bootstrap.Toast(toast[0]);
                bsToast.show();

                toast.on('hidden.bs.toast', function() {
                    $(this).remove();
                });
            }

            // Inicializar gráfico al cargar
            initChart();

            // Eventos
            $('#searchInput').on('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    loadRanking(
                        $(this).val(),
                        $('#localidadFilter').val(),
                        $('#limitFilter').val()
                    );
                }, 500);
            });

            $('#localidadFilter, #limitFilter').on('change', function() {
                loadRanking(
                    $('#searchInput').val(),
                    $('#localidadFilter').val(),
                    $('#limitFilter').val()
                );
            });

            $('#clearFilters').on('click', function() {
                $('#searchInput').val('');
                $('#localidadFilter').val('');
                $('#limitFilter').val('50');
                loadRanking('', '', 50);
            });

            // Manejar paginación
            $(document).on('click', '#pagination-container .pagination a', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                if (url) {
                    $.ajax({
                        url: url,
                        method: 'GET',
                        data: {
                            search: $('#searchInput').val(),
                            localidad_id: $('#localidadFilter').val(),
                            limit: $('#limitFilter').val(),
                            ajax: true
                        },
                        dataType: 'json',
                        success: function(response) {
                            $('#rankingTableBody').html(response.html);
                            $('#pagination-container').html(response.pagination);
                            updateCounters(response);
                            updateChart();
                        }
                    });
                }
            });

            // Botón para ver detalles de deuda
            $(document).on('click', '.btn-detalles-deuda', function() {
                const clienteId = $(this).data('cliente-id');
                const clienteNombre = $(this).data('cliente-nombre');

                $('#modalDetallesDeuda .modal-title').text('Detalles de Deuda - ' + clienteNombre);

                $.ajax({
                    url: "{{ url('admin/clientes/detalles-deuda') }}/" + clienteId,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $('#modalDetallesDeuda .modal-body').html(response.html);

                            // Actualizar enlace de pagos
                            $('.btn-ir-pagos').attr('href',
                                "{{ url('admin/clientes/ver') }}/" + clienteId);

                            $('#modalDetallesDeuda').modal('show');
                        }
                    }
                });
            });

            // Carga inicial
            setTimeout(() => {
                updateChart();
            }, 1000);
        });
    </script>
@endpush
