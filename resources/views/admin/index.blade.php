@extends('admin.dashboard')
@section('admin')
    <div class="container-fluid">
        <!-- Bienvenida -->
        <div class="avip-welcome">
            <div>
                <p class="avip-welcome__eyebrow">Panel de operación</p>
                <h1>Hola, {{ Auth::guard('web')->user()->name ?? 'equipo AVIP' }}</h1>
                <p>Este es el pulso de tu operación para hoy.</p>
            </div>
            <span class="avip-date-pill">
                <i class="ri-calendar-line"></i>
                {{ now()->locale('es')->translatedFormat('l, d \d\e F \d\e Y') }}
            </span>
        </div>

        <!-- Indicadores -->
        <div class="row g-3 avip-kpi-row">
            <div class="col-md-4">
                <div class="avip-kpi">
                    <span class="avip-kpi__icon"><i class="ri-user-3-line"></i></span>
                    <div>
                        <p class="avip-kpi__label">Total clientes</p>
                        <p class="avip-kpi__value">{{ number_format($totalClientes ?? 0) }}</p>
                        <p class="avip-kpi__hint">Clientes registrados</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="avip-kpi avip-kpi--debt">
                    <span class="avip-kpi__icon"><i class="ri-money-dollar-circle-line"></i></span>
                    <div>
                        <p class="avip-kpi__label">Deuda total</p>
                        <p class="avip-kpi__value">Bs. {{ number_format($deudaTotal ?? 0, 2) }}</p>
                        <p class="avip-kpi__hint">Saldo pendiente de cobro</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="avip-kpi avip-kpi--orders">
                    <span class="avip-kpi__icon"><i class="ri-shopping-basket-2-line"></i></span>
                    <div>
                        <p class="avip-kpi__label">Pedidos hoy</p>
                        <p class="avip-kpi__value">{{ number_format($pedidosHoy ?? 0) }}</p>
                        <p class="avip-kpi__hint">Pedidos generados hoy</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Sidebar con Localidades -->
            <div class="col-xl-4 col-12">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">
                            <i class="ri-map-pin-line me-2"></i>Localidades
                        </h5>
                        <span class="badge bg-soft-primary text-primary">{{ $localidades->count() }}</span>
                    </div>
                    @if (Auth::guard('web')->user()->can('pedidos.generar.regular'))
                        <div class="card-body">
                            <div class="d-flex flex-column gap-2">
                                @foreach ($localidades as $localidad)
                                    @php
                                        $pedidoManana = $localidad->pedido_manana ?? false;
                                    @endphp
                                    <div class="localidad-item p-3 border rounded {{ $pedidoManana ? 'localidad-bloqueada' : '' }}"
                                        style="border-left: 4px solid {{ $localidad->color }} !important;"
                                        data-localidad-id="{{ $localidad->id }}"
                                        data-localidad-nombre="{{ $localidad->nombre }}">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 d-flex align-items-center gap-2 flex-wrap">
                                                    {{ $localidad->nombre }}
                                                    @if ($pedidoManana)
                                                        <span class="badge bg-success-subtle text-success">
                                                            Día siguiente listo
                                                        </span>
                                                    @endif
                                                </h6>
                                                <p class="text-muted mb-0 small">
                                                    <i class="ri-user-line me-1"></i>
                                                    {{ $localidad->clientes_count ?? 0 }} clientes
                                                </p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <span class="badge"
                                                    style="background-color: {{ $localidad->color }}; color: white;">
                                                    <i class="ri-map-pin-fill"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-outline-primary w-100 btn-crear-pedido"
                                                data-localidad-id="{{ $localidad->id }}"
                                                data-localidad-nombre="{{ $localidad->nombre }}"
                                                {{ $pedidoManana ? 'disabled' : '' }}
                                                title="{{ $pedidoManana ? 'Ya se generaron pedidos para el día siguiente' : 'Crear pedidos para el día siguiente' }}">
                                                @if ($pedidoManana)
                                                    <i class="ri-check-double-line me-1"></i> Pedidos Generados
                                                @else
                                                    <i class="ri-add-line me-1"></i> Crear Pedidos Día Siguiente
                                                @endif
                                            </button>
                                        </div>
                                    </div>
                                @endforeach

                                @if ($localidades->isEmpty())
                                    <div class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="ri-map-pin-line display-4"></i>
                                            <p class="mt-2">No hay localidades registradas</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

            </div>

            <!-- Calendario -->
            <div class="col-xl-8 col-12">
                <div class="card h-100">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h5 class="card-title mb-0">
                                    <i class="ri-calendar-2-line me-2"></i>Calendario
                                </h5>
                                <span class="calendar-title" id="calendar-titulo">
                                    {{ ucfirst(now()->locale('es')->translatedFormat('F Y')) }}
                                </span>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-primary btn-sm" id="btn-hoy">
                                    Hoy
                                </button>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-light btn-sm" id="btn-prev">
                                        <i class="ri-arrow-left-line"></i>
                                    </button>
                                    <button type="button" class="btn btn-light btn-sm" id="btn-next">
                                        <i class="ri-arrow-right-line"></i>
                                    </button>
                                </div>
                                <select class="form-select form-select-sm" id="selector-vista" style="width: auto;">
                                    <option value="dayGridMonth">Mes</option>
                                    <option value="timeGridWeek">Semana</option>
                                    <option value="timeGridDay">Día</option>
                                    <option value="listMonth">Lista</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Confirmación Pedidos -->
    <div class="modal fade" id="modalConfirmarPedidos" tabindex="-1" aria-labelledby="modalConfirmarPedidosLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info bg-soft">
                    <h5 class="modal-title text-white" id="modalConfirmarPedidosLabel">
                        <i class="ri-information-line me-2"></i>Confirmar Creación de Pedidos
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="avatar-title bg-info bg-soft text-info rounded-circle">
                                <i class="ri-calendar-check-line fs-2"></i>
                            </div>
                        </div>
                        <h5 id="confirmar-titulo">¿Crear pedidos para el día siguiente?</h5>
                        <p class="text-muted" id="confirmar-descripcion">
                            Se crearán pedidos para todos los clientes de esta localidad para el día siguiente.
                        </p>
                        <div class="alert alert-warning mt-3" id="alert-nota">
                            <i class="ri-alert-line me-2"></i>
                            <strong>Nota:</strong> Los pedidos se crearán con valores en 0 y se generará un código
                            automático.
                        </div>
                        <div class="alert alert-secondary mt-3" id="alert-inactivos" style="display: none;">
                            <i class="ri-forbid-line me-2"></i>
                            <strong>Clientes inactivos (no se incluirán):</strong>
                            <ul id="lista-inactivos" class="mb-0 mt-2"></ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" id="modal-footer-buttons">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-info" id="btn-confirmar-crear">
                        <i class="ri-check-line me-1"></i> Sí, Crear Pedidos
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <!-- FullCalendar Spanish -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.min.js'></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ========== CONFIGURACIÓN DE ZONA HORARIA ==========
            // Función para formatear fechas en zona horaria de La Paz
            function formatearFechaLaPaz(fecha) {
                return new Date(fecha).toLocaleString('es-BO', {
                    timeZone: 'America/La_Paz',
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit'
                });
            }

            // Función para obtener la fecha de mañana en La Paz
            function obtenerMananaLaPaz() {
                const ahora = new Date();
                // Convertir a zona horaria de La Paz
                const opciones = {
                    timeZone: 'America/La_Paz'
                };
                const fechaLaPaz = new Date(ahora.toLocaleString('en-US', opciones));
                fechaLaPaz.setDate(fechaLaPaz.getDate() + 1);
                return fechaLaPaz;
            }

            // Función para formatear fecha en formato YYYY-MM-DD para La Paz
            function formatearFechaISOLaPaz(fecha) {
                const date = new Date(fecha);
                return date.toLocaleDateString('en-CA', {
                    timeZone: 'America/La_Paz'
                });
            }

            // Función para crear fecha en La Paz desde string
            function crearFechaLaPaz(fechaStr) {
                // Asumimos que fechaStr viene en formato YYYY-MM-DD
                const [year, month, day] = fechaStr.split('-');
                return new Date(year, month - 1, day).toLocaleDateString('es-BO', {
                    timeZone: 'America/La_Paz'
                });
            }

            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                timeZone: 'America/La_Paz', // ← ESTA ES LA CLAVE
                firstDay: 1,
                height: 'auto',
                contentHeight: 'auto',
                datesSet: function(info) {
                    const titulo = info.view.title;

                    $('#calendar-titulo').text(
                        titulo.charAt(0).toUpperCase() + titulo.slice(1)
                    );
                },
                headerToolbar: {
                    left: '',
                    center: '',
                    right: ''
                },
                buttonText: {
                    today: 'Hoy',
                    month: 'Mes',
                    week: 'Semana',
                    day: 'Día',
                    list: 'Lista'
                },
                allDayText: 'Todo el día',
                events: function(fetchInfo, successCallback, failureCallback) {
                    console.log('Cargando eventos para:', fetchInfo.start, fetchInfo.end);

                    $.ajax({
                        url: "{{ route('admin.calendario.eventos') }}",
                        method: 'GET',
                        data: {
                            start: fetchInfo.start.toISOString(),
                            end: fetchInfo.end.toISOString()
                        },
                        success: function(response) {
                            console.log('Eventos cargados:', response);

                            // Procesar eventos con zona horaria correcta
                            const eventosConEstilo = response.map(evento => {
                                // Asegurar que las fechas se interpreten en La Paz
                                const start = new Date(evento.start);
                                const end = evento.end ? new Date(evento.end) :
                                    null;

                                return {
                                    ...evento,
                                    start: start,
                                    end: end,
                                    display: 'block',
                                    className: 'evento-localidad'
                                };
                            });

                            successCallback(eventosConEstilo);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al cargar eventos:', error);
                            failureCallback('Error al cargar eventos');
                        }
                    });
                },
                eventClick: function(info) {
                    console.log('Evento clickeado:', info.event);

                    try {
                        const localidadId = info.event.extendedProps.localidad_id;
                        const localidadNombre = info.event.extendedProps.localidad_nombre;
                        const codigo = info.event.extendedProps.codigo;
                        const fecha = info.event.extendedProps.fecha;
                        const totalPedidos = info.event.extendedProps.total_pedidos;

                        console.log('Datos del evento:', {
                            localidadId,
                            localidadNombre,
                            codigo,
                            fecha,
                            totalPedidos
                        });

                        // CORRECCIÓN: Obtener la fecha correctamente del evento de FullCalendar
                        const fechaEvento = info.event.start;
                        const fechaStr = fechaEvento.toLocaleDateString('es-BO', {
                            timeZone: 'America/La_Paz'
                        });

                        console.log('Fecha formateada:', fechaStr);

                        // Mostrar modal con opciones
                        mostrarModalPedidos(localidadId, localidadNombre, codigo, fechaStr,
                            totalPedidos);

                        info.jsEvent.preventDefault();
                    } catch (error) {
                        console.error('Error al hacer clic en el evento:', error);
                        alert('Error al cargar los detalles del pedido');
                    }
                },
                dateClick: function(info) {
                    console.log('Fecha clickeada en La Paz:', info.dateStr);
                }
            });

            // Función para formatear fecha en formato DD/MM/YYYY desde cualquier formato
            function formatearFechaEspanol(fecha) {
                try {
                    // Si ya viene en formato DD/MM/YYYY, devolver tal cual
                    if (typeof fecha === 'string' && fecha.includes('/')) {
                        return fecha;
                    }

                    // Si es un objeto Date
                    if (fecha instanceof Date) {
                        const dia = String(fecha.getDate()).padStart(2, '0');
                        const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                        const anio = fecha.getFullYear();
                        return `${dia}/${mes}/${anio}`;
                    }

                    // Si viene en formato YYYY-MM-DD
                    if (typeof fecha === 'string' && fecha.includes('-')) {
                        const [anio, mes, dia] = fecha.split('-');
                        return `${dia}/${mes}/${anio}`;
                    }

                    return fecha;
                } catch (error) {
                    console.error('Error formateando fecha:', error);
                    return fecha;
                }
            }

            calendar.render();
            window.avipCalendar = calendar;

            function actualizarTituloCalendario() {
                const titulo = calendar.view.title;

                $('#calendar-titulo').text(
                    titulo.charAt(0).toUpperCase() + titulo.slice(1)
                );
            }

            // Controles personalizados
            document.getElementById('btn-prev').addEventListener('click', function() {
                calendar.prev();
                setTimeout(actualizarTituloCalendario, 100);
            });

            document.getElementById('btn-next').addEventListener('click', function() {
                calendar.next();
                setTimeout(actualizarTituloCalendario, 100);
            });

            document.getElementById('btn-hoy').addEventListener('click', function() {
                calendar.today();
                setTimeout(actualizarTituloCalendario, 100);
            });

            document.getElementById('selector-vista').addEventListener('change', function() {
                calendar.changeView(this.value);
                setTimeout(actualizarTituloCalendario, 100);
            });

            // ========== FUNCIONALIDAD CREAR PEDIDOS ==========

            let localidadSeleccionadaId = null;
            let localidadSeleccionadaNombre = null;

            // Botón crear pedidos en las localidades
            $(document).on('click', '.btn-crear-pedido:not(:disabled)', function(e) {
                e.stopPropagation();
                const localidadId = $(this).data('localidad-id');
                const localidadNombre = $(this).data('localidad-nombre');
                abrirModalCrearPedidos(localidadId, localidadNombre);
            });

            $(document).on('click', '.localidad-item', function() {
                if ($(this).hasClass('localidad-bloqueada')) {
                    return;
                }

                const boton = $(this).find('.btn-crear-pedido');
                if (boton.is(':disabled')) {
                    return;
                }
                const localidadId = $(this).data('localidad-id');
                const localidadNombre = $(this).data('localidad-nombre');
                abrirModalCrearPedidos(localidadId, localidadNombre);
            });

            function abrirModalCrearPedidos(localidadId, localidadNombre) {
                localidadSeleccionadaId = localidadId;
                localidadSeleccionadaNombre = localidadNombre;

                // Obtener fecha de mañana en zona horaria de La Paz
                const fechaManana = obtenerMananaLaPaz();
                const fechaMananaStr = formatearFechaISOLaPaz(fechaManana);

                console.log('Verificando pedidos para:', {
                    localidadId: localidadId,
                    localidadNombre: localidadNombre,
                    fechaManana: fechaMananaStr,
                    fechaMananaObjeto: fechaManana
                });

                // Verificar si ya existen pedidos para esta localidad y fecha
                $.ajax({
                    url: "{{ route('admin.pedidos.verificar-existencia') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        localidade_id: localidadId,
                        fecha: fechaMananaStr,
                        tipo_pedido: 0
                    },
                    success: function(response) {
                        console.log('Respuesta del servidor:', response);

                        if (response.existen_pedidos) {
                            // Si ya existen pedidos, mostrar mensaje informativo
                            $('#confirmar-titulo').text(`Pedidos ya generados`);
                            $('#confirmar-descripcion').html(`
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>
                            <strong>Información</strong><br>
                            Ya existen ${response.cantidad} pedidos para <strong>${localidadNombre}</strong> 
                            en la fecha <strong>${fechaMananaStr}</strong>.
                        </div>
                        <p class="text-muted">No se pueden generar nuevos pedidos para la misma localidad y fecha.</p>
                    `);

                            $('#alert-nota').hide();
                            $('#modal-footer-buttons').html(`
                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i> Cerrar
                        </button>
                    `);
                        } else {
                            // Formatear fecha para mostrar en español
                            const fechaMostrar = fechaManana.toLocaleDateString('es-BO', {
                                weekday: 'long',
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric',
                                timeZone: 'America/La_Paz'
                            });

                            // Si no existen pedidos, permitir creación normal
                            $('#confirmar-titulo').text(`Crear pedidos para ${localidadNombre}`);
                            $('#confirmar-descripcion').html(`
                        <strong>Fecha:</strong> ${fechaMostrar}<br>
                        <strong>Localidad:</strong> ${localidadNombre}<br>
                        <strong>Clientes:</strong> ${response.total_clientes || 'Todos'}<br>
                        <div class="alert alert-info mt-2">
                            <i class="ri-information-line me-1"></i>
                            Se crearán pedidos para todos los clientes de esta localidad.
                        </div>
                    `);

                            $('#alert-nota').show();
                            $('#modal-footer-buttons').html(`
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-info" id="btn-confirmar-crear">
                            <i class="ri-check-line me-1"></i> Sí, Crear Pedidos
                        </button>
                    `);

                            $('#btn-confirmar-crear').off('click').on('click',
                                confirmarCreacionPedidos);

                            // Cargar clientes inactivos de esta localidad
                            $.ajax({
                                url: "{{ url('/admin/clientes/inactivos') }}/" + localidadId,
                                method: 'GET',
                                success: function(response) {
                                    if (response.success && response.clientes.length > 0) {
                                        let html = '';
                                        response.clientes.forEach(function(c) {
                                            html += '<li>' + c.ci + ' - ' + c.nombres + ' ' + c.apellidos + '</li>';
                                        });
                                        $('#lista-inactivos').html(html);
                                        $('#alert-inactivos').show();
                                    } else {
                                        $('#alert-inactivos').hide();
                                    }
                                },
                                error: function() {
                                    $('#alert-inactivos').hide();
                                }
                            });
                        }

                        $('#modalConfirmarPedidos').modal('show');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al verificar pedidos existentes:', error);
                        alert('Error al verificar pedidos existentes: ' + error);
                    }
                });
            }

            function confirmarCreacionPedidos() {
                if (!localidadSeleccionadaId) return;

                const btn = $(this);
                btn.prop('disabled', true).html('<i class="ri-loader-4-line spin me-1"></i> Creando...');

                $.ajax({
                    url: "{{ route('admin.pedidos.crear-para-localidad') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        localidade_id: localidadSeleccionadaId,
                        tipo_pedido: 0
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('success', response.msg);
                            $('#modalConfirmarPedidos').modal('hide');
                            actualizarEstadoBotonesLocalidad(localidadSeleccionadaId);

                            // Refrescar el calendario para mostrar el nuevo evento
                            calendar.refetchEvents();
                        } else {
                            showNotification('error', response.msg);
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Error al crear los pedidos';
                        if (xhr.responseJSON?.msg) {
                            errorMsg = xhr.responseJSON.msg;
                        }
                        showNotification('error', errorMsg);
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(
                            '<i class="ri-check-line me-1"></i> Sí, Crear Pedidos');
                    }
                });
            }

            function mostrarModalPedidos(localidadId, localidadNombre, codigo, fechaStr, totalPedidos) {
                console.log('Mostrando modal para:', {
                    localidadId,
                    localidadNombre,
                    codigo,
                    fechaStr,
                    totalPedidos
                });

                try {
                    // Usar la función auxiliar para asegurar formato correcto
                    const fechaFormateada = formatearFechaEspanol(fechaStr);
                    console.log('Fecha después de formatear:', fechaFormateada);

                    // Parsear la fecha correctamente
                    const [dia, mes, anio] = fechaFormateada.split('/');
                    const fechaURL = `${anio}-${mes.padStart(2, '0')}-${dia.padStart(2, '0')}`;

                    // Crear objeto Date para formateo de visualización
                    const fechaObj = new Date(anio, mes - 1, dia);
                    const fechaMostrar = fechaObj.toLocaleDateString('es-BO', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        timeZone: 'America/La_Paz'
                    });

                    console.log('Fecha para URL:', fechaURL);
                    console.log('Fecha para mostrar:', fechaMostrar);

                    // El resto del código permanece igual...
                    const contenido = `
            <div class="text-center mb-4">
                <div class="avatar-lg mx-auto mb-3">
                    <div class="avatar-title bg-primary bg-soft text-primary rounded-circle">
                        <i class="ri-shopping-cart-line fs-2"></i>
                    </div>
                </div>
                <h5>Pedidos de ${localidadNombre}</h5>
                <p class="text-muted">
                    <strong>Fecha:</strong> ${fechaMostrar}<br>
                    <strong>Código:</strong> ${codigo}<br>
                    <strong>Total de pedidos:</strong> ${totalPedidos}
                </p>
            </div>
            <div class="d-grid gap-2">
                <a href="{{ url('admin/pedidos') }}/${codigo}?fecha=${fechaURL}&localidad_id=${localidadId}" 
                   class="btn btn-primary">
                    <i class="ri-eye-line me-1"></i> Ver Pedidos
                </a>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i> Cerrar
                </button>
            </div>
        `;

                    $('#confirmar-titulo').text('Detalles de Pedidos');
                    $('#confirmar-descripcion').html(contenido);
                    $('#alert-nota').hide();
                    $('#modal-footer-buttons').html(`
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
        `);

                    $('#modalConfirmarPedidos').modal('show');

                } catch (error) {
                    console.error('Error al mostrar modal:', error);
                    alert('Error al mostrar los detalles del pedido: ' + error.message);
                }
            }

            function actualizarEstadoBotonesLocalidad(localidadId) {
                const boton = $(`.btn-crear-pedido[data-localidad-id="${localidadId}"]`);
                const itemLocalidad = $(`.localidad-item[data-localidad-id="${localidadId}"]`);

                boton.prop('disabled', true)
                    .removeClass('btn-outline-primary')
                    .addClass('btn-outline-secondary')
                    .html('<i class="ri-check-double-line me-1"></i> Pedidos Generados');

                itemLocalidad.addClass('localidad-bloqueada').removeClass('bg-light');
            }

            // Función para verificar y actualizar el estado de todos los botones al cargar la página
            function actualizarEstadosBotones() {
                const fechaManana = obtenerMananaLaPaz();
                const fechaMananaStr = formatearFechaISOLaPaz(fechaManana);

                console.log('Actualizando estados de botones para fecha:', fechaMananaStr);

                $('.localidad-item').each(function() {
                    const localidadId = $(this).data('localidad-id');
                    const boton = $(this).find('.btn-crear-pedido');

                    // Solo verificar si el botón no está ya deshabilitado manualmente
                    if (!boton.is(':disabled')) {
                        $.ajax({
                            url: "{{ route('admin.pedidos.verificar-existencia') }}",
                            method: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                localidade_id: localidadId,
                                fecha: fechaMananaStr,
                                tipo_pedido: 0
                            },
                            success: function(response) {
                                console.log(`Localidad ${localidadId}:`, response);

                                if (response.existen_pedidos) {
                                    boton.prop('disabled', true)
                                        .removeClass('btn-outline-primary')
                                        .addClass('btn-outline-secondary')
                                        .html(
                                            '<i class="ri-check-double-line me-1"></i> Pedidos Generados'
                                        );

                                    $(`.localidad-item[data-localidad-id="${localidadId}"]`)
                                        .addClass('localidad-bloqueada')
                                        .removeClass('bg-light');
                                } else {
                                    // Asegurarse de que el botón esté habilitado si no hay pedidos
                                    boton.prop('disabled', false)
                                        .removeClass('btn-outline-secondary')
                                        .addClass('btn-outline-primary')
                                        .html(
                                            '<i class="ri-add-line me-1"></i> Crear Pedidos Día Siguiente'
                                        );

                                    $(`.localidad-item[data-localidad-id="${localidadId}"]`)
                                        .removeClass('localidad-bloqueada')
                                        .removeClass('bg-light');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error(`Error verificando localidad ${localidadId}:`,
                                    error);
                            }
                        });
                    }
                });
            }

            // El estado inicial de cada localidad se calcula en el servidor.
            // La revalidación AJAX se omite para evitar parpadeos y errores de sesión.

            function showNotification(type, message) {
                let toastContainer = $('#toast-container');
                if (toastContainer.length === 0) {
                    toastContainer = $(
                        '<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999"></div>'
                    );
                    $('body').append(toastContainer);
                }
                const toast = $(`
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `);
                toastContainer.append(toast);
                const bsToast = new bootstrap.Toast(toast[0]);
                bsToast.show();
                toast.on('hidden.bs.toast', function() {
                    $(this).remove();
                });
            }

            // Función para crear pedidos (mantener por compatibilidad)
            function crearPedidosParaLocalidad(localidadId) {
                const tipoPedido = 0;

                fetch('/admin/pedidos/crear-para-localidad', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            localidade_id: localidadId,
                            tipo_pedido: tipoPedido
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.msg);
                            location.reload();
                        } else {
                            alert('Error: ' + data.msg);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al crear los pedidos');
                    });
            }
        });
    </script>

    <style>
        .localidad-item {
            cursor: pointer;
        }

        .localidad-item.bg-light {
            opacity: 0.8;
        }

        .btn-crear-pedido:disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }

        @media (max-width: 991.98px) {
            #calendar {
                min-height: 520px;
            }
        }

        .spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush
