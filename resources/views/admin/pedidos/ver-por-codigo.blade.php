@extends('admin.dashboard')
@section('admin')
    <div class="container-fluid pedido-detalle-page">
        <style>
        .pedido-detalle-page .avip-welcome {
            margin-bottom: 1.2rem;
        }

        .pedido-detalle-page .avip-welcome h1 {
            font-size: clamp(1.65rem, 2.3vw, 2rem);
        }

        .pedido-kpi-row .avip-kpi {
            padding: 1rem 1.05rem;
            gap: 0.7rem;
        }

        .pedido-kpi-row .avip-kpi__icon {
            width: 38px;
            height: 38px;
            flex-basis: 38px;
            font-size: 1.05rem;
        }

        .pedido-kpi-row .avip-kpi__label {
            font-size: 0.64rem;
        }

        .pedido-kpi-row .avip-kpi__value {
            font-size: 1.3rem;
        }

        .pedido-kpi--date .avip-kpi__value {
            font-size: 1.05rem;
            white-space: nowrap;
            letter-spacing: -0.03em;
        }

        .pedido-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .pedido-toolbar h6 {
            font-weight: 800;
            color: var(--avip-ink);
        }

        .pedido-toolbar .btn {
            white-space: nowrap;
        }

        .pedido-search-group {
            min-width: 280px;
        }

        .pedido-actions-cell .btn {
            white-space: nowrap;
        }

        .pedido-detalle-page .report-table th,
        .pedido-detalle-page .report-table td {
            vertical-align: middle;
        }

        .pedido-detalle-page .modal-content {
            border: 0;
            border-radius: 20px;
            box-shadow: 0 40px 80px -40px rgba(15, 36, 28, 0.6);
            overflow: hidden;
        }

        .pedido-detalle-page .modal-header {
            padding: 1.1rem 1.35rem;
            background: linear-gradient(120deg, #f0f7f1, #fbfcf9);
            border-bottom: 1px solid var(--avip-line);
        }

        .pedido-detalle-page .modal-title {
            font-size: 1rem;
            font-weight: 800;
            color: var(--avip-ink);
        }

        .pedido-detalle-page .modal-title i {
            color: var(--avip-leaf);
        }

        .pedido-detalle-page .modal-body {
            padding: 1.35rem;
        }

        .pedido-detalle-page .modal-footer {
            padding: 0.95rem 1.35rem;
            background: #fbfcf9;
            border-top: 1px solid var(--avip-line);
            gap: 0.5rem;
        }

        .pedido-detalle-page .modal-footer form {
            margin: 0;
        }

        .pedido-detalle-page .modal-footer .btn-secondary {
            background: #f4f7f2;
            border-color: #e2e9df;
            color: var(--avip-body);
        }

        .pedido-modal-kpi {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
            height: 100%;
            padding: 0.75rem 0.9rem;
            border: 1px solid var(--avip-line);
            border-radius: 14px;
            background: #ffffff;
        }

        .pedido-modal-kpi span {
            font-size: 0.64rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--avip-muted);
        }

        .pedido-modal-kpi strong {
            font-family: var(--avip-font-display);
            font-size: 1.12rem;
            color: var(--avip-ink);
        }

        .pedido-info-tile {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
            height: 100%;
            padding: 0.8rem 0.9rem;
            border: 1px solid var(--avip-line);
            border-radius: 14px;
            background: #ffffff;
        }

        .pedido-info-tile i {
            color: var(--avip-leaf);
        }

        .pedido-info-tile span {
            font-size: 0.64rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--avip-muted);
        }

        .pedido-info-tile strong {
            font-size: 0.9rem;
            color: var(--avip-ink);
        }

        .pedido-detalle-page .info-box {
            height: 100%;
            padding: 0.8rem 0.9rem;
            border-left: 4px solid var(--avip-leaf);
            border-radius: 12px;
            background: #f6f9f4;
        }

        .pedido-detalle-page .info-box strong {
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--avip-muted);
        }

        .pedido-detalle-page .table-responsive {
            border: 1px solid var(--avip-line);
            border-radius: 14px;
        }

        .pedido-detalle-page .table-sm th {
            font-size: 0.68rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #5c6f63;
        }

        .pedido-detalle-page .sticky-top {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .pedido-detalle-page .sticky-bottom {
            position: sticky;
            bottom: 0;
            z-index: 10;
        }

        .pedido-detalle-page .sticky-left {
            position: sticky;
            left: 0;
            z-index: 5;
            background: #ffffff;
        }

        .pedido-detalle-page .sticky-right {
            position: sticky;
            right: 0;
            z-index: 5;
            background: #ffffff;
        }

        .pedido-detalle-page .sticky-left.bg-light,
        .pedido-detalle-page .sticky-right.bg-light {
            background: #f6f9f4 !important;
        }

        .pedido-detalle-page .ri-spin {
            animation: pedido-spin 1s linear infinite;
        }

        @keyframes pedido-spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 767.98px) {
            .pedido-toolbar {
                align-items: flex-start;
            }

            .pedido-search-group {
                min-width: 100%;
            }

            .pedido-detalle-page .modal-body {
                padding: 1rem;
            }

            .pedido-detalle-page .modal-footer {
                flex-wrap: wrap;
            }
        }

        @media print {
            .pedido-detalle-page .modal-header,
            .pedido-detalle-page .modal-footer {
                display: none !important;
            }

            .pedido-detalle-page .modal-dialog {
                max-width: 100% !important;
                margin: 0 !important;
            }

            .pedido-detalle-page .modal-content {
                border: none !important;
                box-shadow: none !important;
            }

            .pedido-detalle-page .modal-body {
                padding: 0 !important;
            }

            .pedido-detalle-page .table {
                font-size: 10px;
            }

            .pedido-detalle-page .table-responsive {
                max-height: none !important;
                overflow: visible !important;
            }

            .pedido-detalle-page .sticky-top,
            .pedido-detalle-page .sticky-bottom,
            .pedido-detalle-page .sticky-left,
            .pedido-detalle-page .sticky-right {
                position: static !important;
            }
        }

        /* Pestañas de pedidos con color propio */
        .pedido-tabs {
            gap: 0.4rem;
            border-bottom: 0;
        }

        .pedido-tab {
            --pedido-tab-color: #3b7fb5;
            --pedido-tab-soft: #eef5fb;
            border: 1px solid rgba(59, 127, 181, 0.22) !important;
            border-radius: 12px 12px 0 0 !important;
            color: var(--pedido-tab-color) !important;
            background: var(--pedido-tab-soft) !important;
            font-weight: 800 !important;
            transition: background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        }

        .pedido-tab--regulares {
            --pedido-tab-color: #3b7fb5;
            --pedido-tab-soft: #eef5fb;
            border-color: rgba(59, 127, 181, 0.22) !important;
        }

        .pedido-tab--frial {
            --pedido-tab-color: #2b8a5a;
            --pedido-tab-soft: #eaf6ee;
            border-color: rgba(43, 138, 90, 0.22) !important;
        }

        .pedido-tab--sofia {
            --pedido-tab-color: #c47a12;
            --pedido-tab-soft: #fdf4e5;
            border-color: rgba(196, 122, 18, 0.24) !important;
        }

        .pedido-tab:hover {
            color: var(--pedido-tab-color) !important;
            background: var(--pedido-tab-soft) !important;
        }

        .pedido-tabs .pedido-tab.active {
            background: var(--pedido-tab-color) !important;
            color: #ffffff !important;
            border-color: var(--pedido-tab-color) !important;
            box-shadow: 0 10px 20px -14px var(--pedido-tab-color);
        }

        .pedido-tab .badge {
            background: rgba(255, 255, 255, 0.28) !important;
            color: inherit !important;
            font-weight: 800;
        }

        .pedido-tabs .pedido-tab.active .badge {
            background: rgba(255, 255, 255, 0.28) !important;
            color: #ffffff !important;
        }

        .pedido-pane {
            --pedido-pane-color: #3b7fb5;
            --pedido-pane-soft: #f5f9fd;
            margin-top: 0;
            padding: 1rem;
            border-top: 3px solid var(--pedido-pane-color);
            border-radius: 0 0 14px 14px;
            background: var(--pedido-pane-soft);
        }

        .pedido-pane--regulares {
            --pedido-pane-color: #3b7fb5;
            --pedido-pane-soft: #f5f9fd;
        }

        .pedido-pane--frial {
            --pedido-pane-color: #2b8a5a;
            --pedido-pane-soft: #f4faf6;
        }

        .pedido-pane--sofia {
            --pedido-pane-color: #c47a12;
            --pedido-pane-soft: #fdf9f1;
        }

        .pedido-pane .table-responsive {
            background: #ffffff;
        }

        @media (max-width: 767.98px) {
            .pedido-tabs {
                flex-wrap: nowrap;
                overflow-x: auto;
                overflow-y: hidden;
                -webkit-overflow-scrolling: touch;
                padding-bottom: 0.35rem;
            }

            .pedido-tabs .nav-item {
                flex: 0 0 auto;
            }

            .pedido-tab {
                white-space: nowrap;
                border-radius: 10px !important;
                font-size: 0.76rem;
            }

            .pedido-pane {
                padding: 0.75rem;
                border-radius: 0 0 12px 12px;
            }
        }
    </style>

        <div class="avip-welcome">
            <div>
                <p class="avip-welcome__eyebrow">Detalle de pedidos</p>
                <h1>Pedido #{{ $codigo }}</h1>
                <p>
                    <i class="ri-map-pin-line me-1"></i>
                    {{ $primerPedido?->localidad?->nombre ?? 'Sin localidad' }}
                    <span class="mx-2">·</span>
                    <i class="ri-calendar-line me-1"></i>
                    {{ $primerPedido ? $primerPedido->created_at->format('d/m/Y') : 'Sin fecha' }}
                    <span class="mx-2">·</span>
                    {{ $totalPedidos }} pedidos
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line me-1"></i> Volver
                </a>
                @if ($primerPedido)
                    <a href="{{ route('admin.pedidos.reporte-consolidado', ['fecha' => $primerPedido->created_at->format('Y-m-d')]) }}"
                        class="btn btn-soft-info">
                        <i class="ri-bar-chart-line me-1"></i> Reporte consolidado
                    </a>
                @else
                    <a href="{{ route('admin.pedidos.reporte-consolidado') }}" class="btn btn-soft-info">
                        <i class="ri-bar-chart-line me-1"></i> Reporte consolidado
                    </a>
                @endif
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

        <div class="row g-3 pedido-kpi-row">
            <div class="col-6 col-md-4 col-xl-2">
                <div class="avip-kpi pedido-kpi">
                    <span class="avip-kpi__icon"><i class="ri-hashtag"></i></span>
                    <div>
                        <p class="avip-kpi__label">Código</p>
                        <p class="avip-kpi__value">{{ $codigo }}</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="avip-kpi pedido-kpi pedido-kpi--date">
                    <span class="avip-kpi__icon"><i class="ri-calendar-line"></i></span>
                    <div>
                        <p class="avip-kpi__label">Fecha</p>
                        <p class="avip-kpi__value">
                            {{ $primerPedido ? $primerPedido->created_at->format('d/m/Y') : 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="avip-kpi pedido-kpi">
                    <span class="avip-kpi__icon"><i class="ri-shopping-basket-2-line"></i></span>
                    <div>
                        <p class="avip-kpi__label">Total pedidos</p>
                        <p class="avip-kpi__value">{{ $totalPedidos }}</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="avip-kpi pedido-kpi">
                    <span class="avip-kpi__icon"><i class="ri-file-list-3-line"></i></span>
                    <div>
                        <p class="avip-kpi__label">Regulares</p>
                        <p class="avip-kpi__value">{{ $totalPedidosRegulares }}</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="avip-kpi pedido-kpi">
                    <span class="avip-kpi__icon"><i class="ri-store-2-line"></i></span>
                    <div>
                        <p class="avip-kpi__label">Frial</p>
                        <p class="avip-kpi__value">{{ $totalPedidosFrial }}</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="avip-kpi pedido-kpi">
                    <span class="avip-kpi__icon"><i class="ri-star-line"></i></span>
                    <div>
                        <p class="avip-kpi__label">Frial Sofia</p>
                        <p class="avip-kpi__value">{{ $totalPedidosFrialSofia }}</p>
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
                        <ul class="nav nav-tabs-custom card-header-tabs border-bottom-0 pedido-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active pedido-tab pedido-tab--regulares" data-bs-toggle="tab"
                                    href="#pedidosRegulares" role="tab">
                                    <i class="ri-shopping-cart-line me-1 align-middle"></i> Pedidos Regulares
                                    <span class="badge bg-primary ms-1">{{ $pedidosRegulares->total() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link pedido-tab pedido-tab--frial" data-bs-toggle="tab" href="#pedidosFrial"
                                    role="tab">
                                    <i class="ri-restaurant-line me-1 align-middle"></i> Pedidos Frial
                                    <span class="badge bg-success ms-1">{{ $pedidosFrial->total() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link pedido-tab pedido-tab--sofia" data-bs-toggle="tab"
                                    href="#pedidosFrialSofia" role="tab">
                                    <i class="ri-store-line me-1 align-middle"></i> Pedidos Frial Sofia
                                    <span class="badge bg-info ms-1">{{ $pedidosFrialSofia->total() }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Pestaña Pedidos Regulares -->
                            <div class="tab-pane active pedido-pane pedido-pane--regulares" id="pedidosRegulares" role="tabpanel">
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
                            <div class="tab-pane pedido-pane pedido-pane--frial" id="pedidosFrial" role="tabpanel">
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
                            <div class="tab-pane pedido-pane pedido-pane--sofia" id="pedidosFrialSofia" role="tabpanel">
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Activar la pestaña indicada en el hash (#pedidosFrial, etc.)
            const hash = window.location.hash;
            if (hash) {
                const trigger = document.querySelector(
                    '.pedido-tabs .nav-link[data-bs-toggle="tab"][href="' + hash + '"]'
                );
                if (trigger && window.bootstrap) {
                    bootstrap.Tab.getOrCreateInstance(trigger).show();
                }
            }

            // Mantener el hash sincronizado al cambiar de pestaña
            document.querySelectorAll('.pedido-tabs .nav-link[data-bs-toggle="tab"]').forEach(function(tab) {
                tab.addEventListener('shown.bs.tab', function(event) {
                    const target = event.target.getAttribute('href');
                    if (target && history.replaceState) {
                        history.replaceState(null, '', target);
                    }
                });
            });
        });
    </script>
@endpush