@extends('admin.dashboard')
@section('admin')
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Pedidos - Código {{ $codigo }}</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.pedidos.index') }}">Pedidos</a></li>
                            <li class="breadcrumb-item active">Código {{ $codigo }}</li>
                        </ol>
                    </div>
                    <!-- Botón para reporte consolidado -->
                    @if ($primerPedido)
                        <a href="{{ route('admin.pedidos.reporte-consolidado', ['fecha' => $primerPedido->created_at->format('Y-m-d')]) }}"
                            class="btn btn-info btn-sm">
                            <i class="ri-bar-chart-line me-1"></i> Reporte Consolidado
                        </a>
                    @else
                        <a href="{{ route('admin.pedidos.reporte-consolidado') }}" class="btn btn-info btn-sm">
                            <i class="ri-bar-chart-line me-1"></i> Reporte Consolidado
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Alertas de Confirmación -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-checkbox-circle-line me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif



        <!-- Información del Grupo -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h6 class="text-muted mb-1">Código</h6>
                                    <h4 class="text-primary">{{ $codigo }}</h4>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h6 class="text-muted mb-1">Fecha</h6>
                                    <h5>{{ $primerPedido ? $primerPedido->created_at->format('d/m/Y') : 'N/A' }}</h5>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h6 class="text-muted mb-1">Total Pedidos</h6>
                                    <h5 class="text-success">{{ $totalPedidos }}</h5>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h6 class="text-muted mb-1">Regulares</h6>
                                    <h5 class="text-primary">{{ $totalPedidosRegulares }}</h5>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h6 class="text-muted mb-1">Frial</h6>
                                    <h5 class="text-success">{{ $totalPedidosFrial }}</h5>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h6 class="text-muted mb-1">Frial Sofia</h6>
                                    <h5 class="text-info">{{ $totalPedidosFrialSofia }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resto del código de pestañas permanece igual -->
        <!-- Pestañas para los diferentes tipos de pedidos -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs-custom card-header-tabs border-bottom-0" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#pedidosRegulares" role="tab">
                                    <i class="ri-shopping-cart-line me-1 align-middle"></i> Pedidos Regulares
                                    <span class="badge bg-primary ms-1">{{ $pedidosRegulares->total() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#pedidosFrial" role="tab">
                                    <i class="ri-restaurant-line me-1 align-middle"></i> Pedidos Frial
                                    <span class="badge bg-success ms-1">{{ $pedidosFrial->total() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#pedidosFrialSofia" role="tab">
                                    <i class="ri-store-line me-1 align-middle"></i> Pedidos Frial Sofia
                                    <span class="badge bg-info ms-1">{{ $pedidosFrialSofia->total() }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Pestaña Pedidos Regulares -->
                            <div class="tab-pane active" id="pedidosRegulares" role="tabpanel">
                                @include('admin.pedidos.partials.lista-pedidos', [
                                    'pedidos' => $pedidosRegulares,
                                    'tipoPedido' => 0,
                                    'titulo' => 'Regulares',
                                    'localidadId' => $primerPedido->localidade_id ?? null,
                                    'localidadNombre' => $primerPedido->localidad->nombre ?? 'N/A',
                                    'codigo' => $codigo,
                                    'totalClientes' => $totalClientes ?? 0,
                                    'puedeGenerar' => true,
                                    'fechaPedidosRegulares' => $fechaPedidosRegulares ?? null,
                                    'fechaActual' => $fechaActual ?? null,
                                    'primerPedido' => $primerPedido ?? null,
                                ])
                            </div>

                            <!-- Pestaña Pedidos Frial -->
                            <div class="tab-pane" id="pedidosFrial" role="tabpanel">
                                @include('admin.pedidos.partials.lista-pedidos', [
                                    'pedidos' => $pedidosFrial,
                                    'tipoPedido' => 1,
                                    'titulo' => 'Frial',
                                    'localidadId' => $primerPedido->localidade_id ?? null,
                                    'localidadNombre' => $primerPedido->localidad->nombre ?? 'N/A',
                                    'codigo' => $codigo,
                                    'totalClientes' => $totalClientes ?? 0,
                                    'puedeGenerar' => $puedeGenerarFrial,
                                    'fechaPedidosRegulares' => $fechaPedidosRegulares ?? null,
                                    'fechaActual' => $fechaActual ?? null,
                                    'primerPedido' => $primerPedido ?? null,
                                ])
                            </div>

                            <!-- Pestaña Pedidos Frial Sofia -->
                            <div class="tab-pane" id="pedidosFrialSofia" role="tabpanel">
                                @include('admin.pedidos.partials.lista-pedidos', [
                                    'pedidos' => $pedidosFrialSofia,
                                    'tipoPedido' => 2,
                                    'titulo' => 'Frial Sofia',
                                    'localidadId' => $primerPedido->localidade_id ?? null,
                                    'localidadNombre' => $primerPedido->localidad->nombre ?? 'N/A',
                                    'codigo' => $codigo,
                                    'totalClientes' => $totalClientes ?? 0,
                                    'puedeGenerar' => $puedeGenerarFrialSofia,
                                    'fechaPedidosRegulares' => $fechaPedidosRegulares ?? null,
                                    'fechaActual' => $fechaActual ?? null,
                                    'primerPedido' => $primerPedido ?? null,
                                ])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .info-box {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }

        .producto-item {
            background: #fff;
            transition: all 0.3s ease;
        }

        .producto-item:hover {
            background: #f8f9fa;
            border-color: #007bff;
        }

        .producto-item .form-label {
            font-size: 0.9rem;
            color: #495057;
        }

        /* Estilos para las pestañas */
        .nav-tabs-custom .nav-link {
            border: none;
            font-weight: 500;
            color: #495057;
            padding: 0.75rem 1.5rem;
        }

        .nav-tabs-custom .nav-link.active {
            color: #007bff;
            border-bottom: 3px solid #007bff;
            background: transparent;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #007bff;
            border-bottom: 3px solid #dee2e6;
        }

        /* Estilos mejorados para la paginación */
        .pagination {
            margin-bottom: 0;
            gap: 2px;
        }

        .page-link {
            color: #495057;
            border: 1px solid #dee2e6;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 0.375rem;
            min-width: 38px;
            text-align: center;
        }

        .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
        }

        .page-link:hover {
            color: #007bff;
            background-color: #e9ecef;
            border-color: #dee2e6;
        }

        .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #fff;
            border-color: #dee2e6;
        }

        .page-item:not(.active):not(.disabled) .page-link {
            background-color: #fff;
        }
    </style>
@endpush

@push('scripts')
@endpush

@push('styles')
    <style>
        .ri-spin {
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

        .alert {
            border-radius: 0.5rem;
            border-left: 4px solid;
        }

        .alert-success {
            border-left-color: #198754;
        }

        .alert-danger {
            border-left-color: #dc3545;
        }

        .alert-info {
            border-left-color: #0dcaf0;
        }

        .alert-warning {
            border-left-color: #ffc107;
        }

        /* Estilos para el buscador */
        .search-container {
            min-width: 300px;
        }

        .search-container .input-group {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .search-container .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
        }

        /* Asegurar que las alertas se muestren correctamente */
        .alert {
            position: relative;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.375rem;
            z-index: 1050;
        }

        /* Estilos para las tablas de procesar */
        .table-sm input.form-control-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .table-sm th {
            padding: 0.5rem;
            font-size: 0.875rem;
        }

        .table-sm td {
            padding: 0.5rem;
            vertical-align: middle;
        }

        /* Estilos para los badges de peso pedido */
        .badge.bg-info.text-dark {
            background-color: #d1ecf1 !important;
            color: #0c5460 !important;
            font-weight: 500;
        }

        /* Estilos para campos deshabilitados visualmente */
        .text-muted {
            font-style: italic;
        }

        /* Estilos para la alerta informativa */
        .alert-info {
            border-left: 4px solid #0dcaf0;
        }

        /* Estilos para la tabla de detalle */
        .table-info {
            background-color: #d1ecf1 !important;
            border-left: 4px solid #0dcaf0;
        }

        .table-info td {
            font-weight: 600;
        }

        /* Estilos para la tarjeta de resumen */
        .card-header.bg-info {
            background: linear-gradient(45deg, #0dcaf0, #0ba8cc) !important;
        }

        /* Mejorar la legibilidad de los textos pequeños */
        .text-muted {
            font-size: 0.875rem;
        }

        /* Estilos para las filas de la tabla */
        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.075);
        }

        .table-light th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        /* Estilos para la tabla de ventas con scroll */
        .table-responsive {
            border: 1px solid #dee2e6;
        }

        /* Estilos para encabezados y pies fijos */
        .sticky-top {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .sticky-bottom {
            position: sticky;
            bottom: 0;
            z-index: 10;
        }

        .sticky-left {
            position: sticky;
            left: 0;
            z-index: 5;
            background-color: white;
        }

        .sticky-right {
            position: sticky;
            right: 0;
            z-index: 5;
            background-color: white;
        }

        .sticky-left.bg-light,
        .sticky-right.bg-light {
            background-color: #f8f9fa !important;
        }

        /* Estilos para la tabla */
        .table th.text-center {
            vertical-align: middle;
            background-color: #f8f9fa;
        }

        .table td.text-end {
            vertical-align: middle;
        }

        /* Estilos para estados */
        .badge.bg-warning {
            background-color: #ffc107 !important;
            color: #000 !important;
        }

        .badge.bg-info {
            background-color: #0dcaf0 !important;
            color: #000 !important;
        }

        /* Estilos para impresión */
        @media print {

            .modal-header,
            .modal-footer {
                display: none !important;
            }

            .modal-dialog {
                max-width: 100% !important;
                margin: 0 !important;
            }

            .modal-content {
                border: none !important;
                box-shadow: none !important;
            }

            .modal-body {
                padding: 0 !important;
            }

            .table {
                font-size: 10px;
            }

            .table-responsive {
                max-height: none !important;
                overflow: visible !important;
            }

            .sticky-top,
            .sticky-bottom,
            .sticky-left,
            .sticky-right {
                position: static !important;
            }
        }

        /* Mejorar la apariencia de las tarjetas */
        .card-header.bg-primary {
            background: linear-gradient(45deg, #007bff, #0056b3) !important;
        }

        .card-header.bg-info {
            background: linear-gradient(45deg, #0dcaf0, #0ba8cc) !important;
        }

        /* Estilos para la tabla */
        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.1);
        }

        .table-light th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        /* Estilos para la tabla de ventas con scroll */
        .table-responsive {
            border: 1px solid #dee2e6;
        }

        /* Estilos para encabezados y pies fijos */
        .sticky-top {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .sticky-bottom {
            position: sticky;
            bottom: 0;
            z-index: 10;
        }

        .sticky-left {
            position: sticky;
            left: 0;
            z-index: 5;
            background-color: white;
        }

        .sticky-right {
            position: sticky;
            right: 0;
            z-index: 5;
            background-color: white;
        }

        .sticky-left.bg-light,
        .sticky-right.bg-light {
            background-color: #f8f9fa !important;
        }

        /* Estilos para la tabla */
        .table th.text-center {
            vertical-align: middle;
            background-color: #f8f9fa;
        }

        .table td.text-end {
            vertical-align: middle;
        }

        /* Estilos para estados */
        .badge.bg-warning {
            background-color: #ffc107 !important;
            color: #000 !important;
        }

        .badge.bg-info {
            background-color: #0dcaf0 !important;
            color: #000 !important;
        }

        /* Mejorar la apariencia de las tarjetas */
        .card-header.bg-primary {
            background: linear-gradient(45deg, #007bff, #0056b3) !important;
        }

        .card-header.bg-info {
            background: linear-gradient(45deg, #0dcaf0, #0ba8cc) !important;
        }

        /* Estilos para la tabla */
        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.1);
        }

        .table-light th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        /* Estilos para el botón de Excel */
        .btn-success {
            background: linear-gradient(45deg, #198754, #146c43);
            border-color: #198754;
        }

        /* Estilos para el botón de deudas */
        .btn-danger {
            background: linear-gradient(45deg, #dc3545, #c82333);
            border-color: #dc3545;
        }

        /* Estilos para la tabla de deudas */
        .table-responsive {
            border: 1px solid #dee2e6;
        }

        .table th.text-center {
            vertical-align: middle;
            background-color: #f8f9fa;
        }

        .table td.text-end {
            vertical-align: middle;
        }

        /* Estilos para las tarjetas */
        .card-header.bg-danger {
            background: linear-gradient(45deg, #dc3545, #c82333) !important;
        }

        .card-header.bg-info {
            background: linear-gradient(45deg, #0dcaf0, #0ba8cc) !important;
        }

        /* Estilos para la tabla */
        .table-hover tbody tr:hover {
            background-color: rgba(220, 53, 69, 0.1);
        }

        .table-light th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        /* Estilos para el botón de PDF */
        .btn-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border-color: #007bff;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #0056b3, #004085);
            border-color: #0056b3;
        }

        /* Estilos para el formulario en línea */
        .modal-footer form {
            margin: 0;
        }
    </style>
@endpush
