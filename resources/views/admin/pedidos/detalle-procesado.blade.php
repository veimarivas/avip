@extends('admin.dashboard')
@section('admin')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Detalle del Pedido Procesado</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.pedidos.index') }}">Pedidos</a></li>
                            <li class="breadcrumb-item active">Detalle Procesado</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del Pedido -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-box">
                                    <strong>Cliente:</strong><br>
                                    {{ $pedido->cliente->nombres }} {{ $pedido->cliente->apellidos }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <strong>CI:</strong><br>
                                    {{ $pedido->cliente->ci }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <strong>Localidad:</strong><br>
                                    <span class="badge"
                                        style="background-color: {{ $pedido->localidad->color }}; color: white;">
                                        {{ $pedido->localidad->nombre }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <strong>Total:</strong><br>
                                    <h4 class="text-success">Bs. {{ number_format($total, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalle de Productos Procesados -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-list-check-2 me-2"></i>Productos Procesados
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th>N° Cajas</th>
                                        <th>Peso (kg)</th>
                                        <th>Tara (kg)</th>
                                        <th>Peso Real (kg)</th>
                                        <th>Precio (Bs.)</th>
                                        <th>Costo Total (Bs.)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($productosProcesados as $producto)
                                        <tr>
                                            <td>{{ $producto->producto->nombre ?? 'N/A' }}</td>
                                            <td>{{ $producto->n_cajas }}</td>
                                            <td>{{ number_format($producto->peso, 2) }}</td>
                                            <td>{{ in_array($producto->producto_id, [1, 2, 3]) ? number_format($producto->tara, 2) : 'N/A' }}
                                            </td>
                                            <td>{{ number_format($producto->peso_real, 2) }}</td>
                                            <td>Bs. {{ number_format($producto->precio, 2) }}</td>
                                            <td>Bs. {{ number_format($producto->costo_total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <td colspan="6" class="text-end"><strong>Total:</strong></td>
                                        <td><strong>Bs. {{ number_format($total, 2) }}</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        <i class="ri-arrow-left-line me-1"></i> Volver
                    </button>
                    <form action="{{ route('admin.pedidos.generar-deuda', $pedido->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="ri-money-dollar-circle-line me-1"></i> Generar Deuda
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
