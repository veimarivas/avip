@extends('admin.dashboard')
@section('admin')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">
                        <i class="ri-shopping-cart-line me-2"></i>
                        {{ $pedido->pedidos_productos->where('realizado', '1')->count() > 0 ? 'Modificar' : 'Registrar' }}
                        Pedido - {{ $pedido->cliente->nombres }} {{ $pedido->cliente->apellidos }}
                    </h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.pedidos.index') }}">Pedidos</a></li>
                            <li class="breadcrumb-item active">
                                {{ $pedido->pedidos_productos->where('realizado', '1')->count() > 0 ? 'Modificar' : 'Registrar' }}
                                Pedido
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

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

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Información del Cliente -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="info-box p-3 border rounded">
                                    <strong>Cliente:</strong><br>
                                    {{ $pedido->cliente->nombres }} {{ $pedido->cliente->apellidos }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box p-3 border rounded">
                                    <strong>CI:</strong><br>
                                    {{ $pedido->cliente->ci }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box p-3 border rounded">
                                    <strong>Localidad:</strong><br>
                                    <span class="badge"
                                        style="background-color: {{ $pedido->localidad->color }}; color: white;">
                                        {{ $pedido->localidad->nombre }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('admin.pedidos.registrar-productos', $pedido->id) }}" method="POST">
                            @csrf

                            <!-- Productos en 3 columnas -->
                            @php
                                $pedidoProductos = $pedido->pedidos_productos;
                                $chunkedProducts = $pedidoProductos->chunk(ceil($pedidoProductos->count() / 3));
                            @endphp

                            <div class="row">
                                @foreach ($chunkedProducts as $columnProducts)
                                    <div class="col-md-4">
                                        @foreach ($columnProducts as $index => $pedidoProducto)
                                            <div class="producto-item mb-3 p-3 border rounded">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <label class="form-label fw-bold mb-0">
                                                        {{ $pedidoProducto->producto->nombre ?? 'N/A' }}
                                                    </label>
                                                    <span class="badge bg-light text-dark">
                                                        Bs. {{ number_format($pedidoProducto->precio, 2) }}
                                                    </span>
                                                </div>
                                                <input type="hidden"
                                                    name="pedido_productos[{{ $pedidoProducto->id }}][id]"
                                                    value="{{ $pedidoProducto->id }}">
                                                <input type="hidden"
                                                    name="pedido_productos[{{ $pedidoProducto->id }}][producto_id]"
                                                    value="{{ $pedidoProducto->producto_id }}">

                                                <div class="input-group">
                                                    <span class="input-group-text">Peso (kg)</span>
                                                    <input type="number"
                                                        name="pedido_productos[{{ $pedidoProducto->id }}][peso_pedido]"
                                                        class="form-control" value="{{ $pedidoProducto->peso_pedido }}"
                                                        step="0.01" min="0" placeholder="0.00" required>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>

                            @if ($pedidoProductos->isEmpty())
                                <div class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="ri-inbox-line display-4"></i>
                                        <p class="mt-2">No hay productos configurados para este pedido</p>
                                    </div>
                                </div>
                            @endif

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                                            <i class="ri-arrow-left-line me-1"></i> Volver
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-save-line me-1"></i> Guardar Pesos
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
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
    </style>
@endpush
