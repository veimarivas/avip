@extends('admin.dashboard')
@section('admin')
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Reporte Consolidado de Pedidos</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.pedidos.index') }}">Pedidos</a></li>
                            <li class="breadcrumb-item active">Reporte Consolidado</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="fecha" class="form-label">Fecha</label>
                                <input type="date" name="fecha" class="form-control" value="{{ $fecha }}">
                            </div>
                            <div class="col-md-4">
                                <label for="search" class="form-label">Buscar</label>
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Buscar por cliente, CI o localidad..." value="{{ $search }}">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="ri-search-line"></i>
                                    </button>
                                    @if ($search)
                                        <a href="{{ route('admin.pedidos.reporte-consolidado', ['fecha' => $fecha]) }}"
                                            class="btn btn-outline-secondary">
                                            <i class="ri-close-line"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-2 align-self-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-filter-line me-1"></i> Filtrar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de exportación -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="d-flex justify-content-end gap-2">
                    @foreach ($tipos as $tipoId => $tipoNombre)
                        <form action="{{ route('admin.pedidos.reporte-consolidado-pdf') }}" method="POST" target="_blank">
                            @csrf
                            <input type="hidden" name="fecha" value="{{ $fecha }}">
                            <input type="hidden" name="tipo_pedido" value="{{ $tipoId }}">
                            <button type="submit" class="btn btn-soft-danger btn-sm">
                                <i class="ri-file-pdf-line me-1"></i> PDF {{ $tipoNombre }}
                            </button>
                        </form>

                        <form action="{{ route('admin.pedidos.reporte-consolidado-excel') }}" method="POST"
                            target="_blank">
                            @csrf
                            <input type="hidden" name="fecha" value="{{ $fecha }}">
                            <input type="hidden" name="tipo_pedido" value="{{ $tipoId }}">
                            <button type="submit" class="btn btn-soft-success btn-sm">
                                <i class="ri-file-excel-line me-1"></i> Excel {{ $tipoNombre }}
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h6 class="text-muted mb-1">Fecha</h6>
                                    <h5>{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h6 class="text-muted mb-1">Total Pedidos</h6>
                                    <h5 class="text-success">{{ $totalGeneral }}</h5>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h6 class="text-muted mb-1">Regulares</h6>
                                    <h5 class="text-primary">{{ $totalesPorTipo[0] }}</h5>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h6 class="text-muted mb-1">Frial</h6>
                                    <h5 class="text-success">{{ $totalesPorTipo[1] }}</h5>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h6 class="text-muted mb-1">Frial Sofia</h6>
                                    <h5 class="text-info">{{ $totalesPorTipo[2] }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pestañas para los diferentes tipos de pedidos -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs-custom card-header-tabs border-bottom-0" role="tablist">
                            @foreach ($tipos as $tipoId => $tipoNombre)
                                @php
                                    $tabId = str_replace(' ', '', $tipoNombre);
                                @endphp
                                <li class="nav-item">
                                    <a class="nav-link {{ $tipoPedido == $tipoId ? 'active' : '' }}" data-bs-toggle="tab"
                                        href="#pedidos{{ $tabId }}" role="tab">
                                        <i class="ri-shopping-cart-line me-1 align-middle"></i>
                                        Pedidos {{ $tipoNombre }}
                                        <span
                                            class="badge bg-{{ $tipoId == 0 ? 'primary' : ($tipoId == 1 ? 'success' : 'info') }} ms-1">
                                            {{ $totalesPorTipo[$tipoId] }}
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            @foreach ($tipos as $tipoId => $tipoNombre)
                                @php
                                    $tabId = str_replace(' ', '', $tipoNombre);
                                @endphp
                                <div class="tab-pane {{ $tipoPedido == $tipoId ? 'active' : '' }}"
                                    id="pedidos{{ $tabId }}" role="tabpanel">
                                    @include('admin.pedidos.partials.lista-pedidos-consolidado', [
                                        'pedidos' => $pedidosPorTipo[$tipoId],
                                        'productos' => $productos,
                                        'tipoPedido' => $tipoId,
                                        'fecha' => $fecha,
                                        'search' => $search,
                                        'titulo' => $tipoNombre,
                                    ])
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
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
    </style>
@endpush
