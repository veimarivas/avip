@extends('admin.dashboard')
@section('admin')
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-light rounded-3 p-3">
                    <div>
                        <h4 class="mb-1 fw-bold text-dark">Gráficos de Ingresos</h4>
                        <p class="text-muted mb-0">Análisis visual de ingresos diarios y por usuario</p>
                    </div>
                    <div class="page-title-right">
                        <button type="button" class="btn btn-primary btn-lg waves-effect waves-light"
                            id="btnActualizarGraficos">
                            <i class="ri-refresh-line align-bottom me-1"></i> Actualizar Gráficos
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 text-primary">
                            <i class="ri-filter-line me-2"></i>Filtros del Reporte
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="formFiltrosGraficos">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="mes" class="form-label">Mes</label>
                                    <select class="form-select" id="mes" name="mes">
                                        @php
                                            $mesesEs = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                                        @endphp
                                        @for ($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}" {{ $m == $mes ? 'selected' : '' }}>
                                                {{ $mesesEs[$m - 1] }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="ano" class="form-label">Año</label>
                                    <select class="form-select" id="ano" name="ano">
                                        @foreach ($anos as $a)
                                            <option value="{{ $a }}" {{ $a == $ano ? 'selected' : '' }}>
                                                {{ $a }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="usuario_id" class="form-label">Usuario (para gráfico 2)</label>
                                    <select class="form-select" id="usuario_id" name="usuario_id">
                                        <option value="">Todos los usuarios</option>
                                        @foreach ($usuarios as $usuario)
                                            <option value="{{ $usuario->id }}">
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
                                    <label for="tipo_grafico" class="form-label">Tipo de Gráfico (Ingresos)</label>
                                    <select class="form-select" id="tipo_grafico" name="tipo_grafico">
                                        <option value="diario">Ingresos Diarios</option>
                                        <option value="acumulado">Ingresos Acumulados</option>
                                    </select>
                                </div>
                                <div class="col-md-12 mt-2">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-search-line me-1"></i> Generar Gráficos
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas del Mes -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body text-center p-3">
                        <p class="mb-1 text-muted small">Total del Mes</p>
                        <p class="mb-0 fw-bold text-success fs-4" id="totalMes">Bs. 0.00</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body text-center p-3">
                        <p class="mb-1 text-muted small">Promedio Diario</p>
                        <p class="mb-0 fw-bold text-primary fs-4" id="promedioDiario">Bs. 0.00</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body text-center p-3">
                        <p class="mb-1 text-muted small">Días con Ingresos</p>
                        <p class="mb-0 fw-bold text-warning fs-4" id="diasConIngresos">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body text-center p-3">
                        <p class="mb-1 text-muted small">Día de Mayor Ingreso</p>
                        <p class="mb-0 fw-bold text-info fs-4" id="diaMayorIngreso">Día 0: Bs. 0.00</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico 1: Ingresos Diarios/Acumulados -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary-subtle">
                        <h5 class="mb-0 text-primary">
                            <i class="ri-bar-chart-line me-2"></i>
                            <span id="tituloGrafico1">Ingresos Diarios - Mes Actual</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height: 400px;">
                            <canvas id="graficoIngresos" height="400"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico 2: Ingresos por Usuario -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success-subtle">
                        <h5 class="mb-0 text-success">
                            <i class="ri-line-chart-line me-2"></i>
                            <span id="tituloGrafico2">Ingresos por Usuario - Comparativa Diaria</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height: 400px;">
                            <canvas id="graficoIngresosPorUsuario" height="400"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            let graficoIngresos = null;
            let graficoIngresosPorUsuario = null;

            // Función para formatear números con separadores de miles
            function formatoMoneda(valor) {
                // Para formato: 4.165.544,36
                return valor.toLocaleString('es-ES', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            // Inicializar gráficos al cargar la página
            inicializarGraficos();
            cargarDatosGraficos();

            // Formulario de filtros
            $('#formFiltrosGraficos').on('submit', function(e) {
                e.preventDefault();
                cargarDatosGraficos();
            });

            // Botón actualizar
            $('#btnActualizarGraficos').click(function() {
                cargarDatosGraficos();
            });

            function inicializarGraficos() {
                const ctxIngresos = document.getElementById('graficoIngresos').getContext('2d');
                const ctxIngresosPorUsuario = document.getElementById('graficoIngresosPorUsuario').getContext('2d');

                // Configuración del gráfico de ingresos
                graficoIngresos = new Chart(ctxIngresos, {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: []
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: function(context) {
                                        // Formatear con puntos como separadores de miles y coma para decimales
                                        const formattedValue = context.parsed.y.toLocaleString(
                                            'es-ES', {
                                                minimumFractionDigits: 2,
                                                maximumFractionDigits: 2
                                            });
                                        return `Bs. ${formattedValue}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'Bs. ' + value.toLocaleString('es-ES', {
                                            minimumFractionDigits: 0,
                                            maximumFractionDigits: 0
                                        });
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Monto (Bs.)',
                                    font: {
                                        size: 12,
                                        weight: 'bold'
                                    }
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Días del Mes',
                                    font: {
                                        size: 12,
                                        weight: 'bold'
                                    }
                                },
                                ticks: {
                                    font: {
                                        size: 11
                                    }
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        }
                    }
                });

                // Configuración del gráfico de ingresos por usuario
                graficoIngresosPorUsuario = new Chart(ctxIngresosPorUsuario, {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: []
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    font: {
                                        size: 11
                                    }
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: function(context) {
                                        const label = context.dataset.label || '';
                                        const nombreUsuario = label.split('(')[0].trim();
                                        return `${nombreUsuario}: Bs. ${formatoMoneda(context.parsed.y)}`;
                                    },
                                    title: function(tooltipItems) {
                                        return `Día ${tooltiptipItems[0].label}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'Bs. ' + formatoMoneda(value);
                                    },
                                    font: {
                                        size: 11
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Monto (Bs.)',
                                    font: {
                                        size: 12,
                                        weight: 'bold'
                                    }
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Días del Mes',
                                    font: {
                                        size: 12,
                                        weight: 'bold'
                                    }
                                },
                                ticks: {
                                    font: {
                                        size: 11
                                    }
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        }
                    }
                });
            }

            function cargarDatosGraficos() {
                const formData = $('#formFiltrosGraficos').serialize();
                const mes = $('#mes').val();
                const ano = $('#ano').val();
                const tipoGrafico = $('#tipo_grafico').val();
                const usuarioId = $('#usuario_id').val();

                // Actualizar títulos
                const mesNombre = $('#mes option:selected').text();
                $('#tituloGrafico1').text(
                    `${tipoGrafico === 'diario' ? 'Ingresos Diarios' : 'Ingresos Acumulados'} - ${mesNombre} ${ano}`
                );
                $('#tituloGrafico2').text(`Ingresos por Usuario - ${mesNombre} ${ano}`);

                // Mostrar loading
                $('#btnActualizarGraficos').prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2"></span>Generando...'
                );

                // Cargar datos para gráfico 1 (ingresos diarios/acumulados)
                $.ajax({
                    url: "{{ route('admin.reportes.graficos-ingresos.datos') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        mes: mes,
                        ano: ano,
                        tipo_grafico: tipoGrafico
                    },
                    success: function(response) {
                        if (response.success) {
                            // Actualizar gráfico 1
                            graficoIngresos.data.labels = response.labels;
                            graficoIngresos.data.datasets = response.datasets;
                            graficoIngresos.update();

                            // Actualizar estadísticas con formato
                            $('#totalMes').text('Bs. ' + formatoMoneda(response.estadisticas
                                .total_mes));
                            $('#promedioDiario').text('Bs. ' + formatoMoneda(response.estadisticas
                                .promedio_diario));
                            $('#diasConIngresos').text(response.estadisticas.dias_con_ingresos);
                            $('#diaMayorIngreso').text(
                                `Día ${response.estadisticas.dia_maximo.dia}: Bs. ${formatoMoneda(response.estadisticas.dia_maximo.monto)}`
                            );
                        }
                    },
                    error: function(xhr) {
                        console.error('Error al cargar datos del gráfico 1:', xhr.responseText);
                        alert('Error al cargar los datos del gráfico de ingresos.');
                    }
                });

                // Cargar datos para gráfico 2 (ingresos por usuario)
                $.ajax({
                    url: "{{ route('admin.reportes.graficos-ingresos.datos-usuario') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        mes: mes,
                        ano: ano,
                        usuario_id: usuarioId
                    },
                    success: function(response) {
                        if (response.success) {
                            // Actualizar gráfico 2
                            graficoIngresosPorUsuario.data.labels = response.labels;
                            graficoIngresosPorUsuario.data.datasets = response.datasets;
                            graficoIngresosPorUsuario.update();
                        }
                    },
                    error: function(xhr) {
                        console.error('Error al cargar datos del gráfico 2:', xhr.responseText);
                        alert('Error al cargar los datos del gráfico por usuario.');
                    },
                    complete: function() {
                        $('#btnActualizarGraficos').prop('disabled', false).html(
                            '<i class="ri-refresh-line align-bottom me-1"></i> Actualizar Gráficos'
                        );
                    }
                });
            }
        });
    </script>

    <style>
        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }
    </style>
@endpush
