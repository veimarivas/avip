@extends('admin.dashboard')
@section('admin')
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Gestión de Permisos por Rol</h4>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-8">
                                <div class="search-box">
                                    <input type="text" id="searchInput" class="form-control" placeholder="Buscar rol...">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                            <div class="col-md-4">
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
                        <h5 class="card-title mb-0">Roles Disponibles</h5>
                    </div>
                    <div class="card-body">
                        <div id="results-container">
                            <div class="table-responsive">
                                <table class="table table-hover table-centered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">ID</th>
                                            <th width="25%">Nombre del Rol</th>
                                            <th width="60%">Permisos Asignados</th>
                                            <th width="10%" class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    @include('admin.role-permissions.partials.roles-table')
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted">
                                    Mostrando <span id="showing-count">{{ $roles->firstItem() ?? 0 }}</span>
                                    a <span id="to-count">{{ $roles->lastItem() ?? 0 }}</span>
                                    de <span id="total-count">{{ $roles->total() ?? 0 }}</span> registros
                                </div>
                                <div id="pagination-container">
                                    {{ $roles->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .search-box {
            position: relative;
        }

        .search-box .search-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #74788d;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(85, 110, 230, 0.04);
        }

        .badge-permission {
            background-color: #e7f1ff;
            color: #14503c;
            font-size: 0.75rem;
            border: 1px solid #14503c;
        }

        .permission-group {
            font-weight: 600;
            color: #495057;
            font-size: 0.8rem;
            margin-top: 0.5rem;
        }

        .permission-item {
            display: flex;
            align-items: center;
            padding: 0.25rem 0;
            font-size: 0.8rem;
        }

        .permission-checkbox {
            margin-right: 0.5rem;
        }

        .btn-more {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
    </style>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            let debounceTimer;

            // Función para cargar resultados con filtros
            function loadResults(search = '') {
                $.ajax({
                    url: '{{ route('admin.role-permissions.index') }}',
                    method: 'GET',
                    data: {
                        search: search
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#results-container .table-responsive table').find('tbody').replaceWith(
                            response.html);
                        $('#pagination-container').html(response.pagination);

                        // Actualizar contadores si están disponibles en la respuesta
                        if (response.total !== undefined) {
                            updateCounters(response);
                        }

                        // Actualizar los parámetros de los enlaces de paginación
                        $('#pagination-container .pagination a').each(function() {
                            const href = $(this).attr('href');
                            if (href) {
                                const separator = href.includes('?') ? '&' : '?';
                                const newHref = href + separator +
                                    'search=' + encodeURIComponent(search);
                                $(this).attr('href', newHref);
                            }
                        });

                        if (typeof feather !== 'undefined') {
                            feather.replace();
                        }
                    },
                    error: function() {
                        showNotification('error', 'Error al cargar los resultados');
                    }
                });
            }

            // Actualizar contadores de registros
            function updateCounters(response) {
                const total = response.total || 0;
                const from = response.from || 0;
                const to = response.to || 0;

                $('#showing-count').text(from);
                $('#to-count').text(to);
                $('#total-count').text(total);
            }

            // Búsqueda por texto
            $('#searchInput').on('input', function() {
                const searchTerm = $(this).val().trim();
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    loadResults(searchTerm);
                }, 300);
            });

            // Limpiar filtros
            $('#clearFilters').on('click', function() {
                $('#searchInput').val('');
                loadResults();
            });

            // Manejar clics en la paginación
            $(document).on('click', '#pagination-container .pagination a', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                const search = $('#searchInput').val().trim();

                if (!url) return;

                $.ajax({
                    url: url,
                    method: 'GET',
                    data: {
                        search: search
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#results-container .table-responsive table').find('tbody')
                            .replaceWith(response.html);
                        $('#pagination-container').html(response.pagination);

                        // Actualizar contadores si están disponibles
                        if (response.total !== undefined) {
                            updateCounters(response);
                        }

                        // Actualizar los parámetros de los enlaces de paginación
                        $('#pagination-container .pagination a').each(function() {
                            const href = $(this).attr('href');
                            if (href) {
                                const separator = href.includes('?') ? '&' : '?';
                                const newHref = href + separator +
                                    'search=' + encodeURIComponent(search);
                                $(this).attr('href', newHref);
                            }
                        });

                        if (typeof feather !== 'undefined') {
                            feather.replace();
                        }
                    },
                    error: function() {
                        showNotification('error', 'Error al cargar la página');
                    }
                });
            });

            // Función para mostrar/ocultar permisos
            $(document).on('click', '.show-more-permissions', function(e) {
                e.preventDefault();
                const $btn = $(this);
                const $morePermissions = $btn.siblings('.more-permissions');
                const $icon = $btn.find('i');

                if ($morePermissions.hasClass('d-none')) {
                    $morePermissions.removeClass('d-none');
                    $btn.html('Ver menos <i class="ri-arrow-up-s-line"></i>');
                } else {
                    $morePermissions.addClass('d-none');
                    $btn.html('Ver más <i class="ri-arrow-down-s-line"></i>');
                }
            });

            // Función para mostrar notificaciones
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
                    <div class="toast-body">
                        ${message}
                    </div>
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

            // Inicializar tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>

    <style>
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

        .toast {
            min-width: 250px;
        }
    </style>

    <!-- Container para notificaciones toast -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999"></div>
@endpush
