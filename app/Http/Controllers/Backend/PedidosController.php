<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Localidade;
use App\Models\Cliente;
use App\Models\LocalidadesProductos;
use App\Models\PedidoProductos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ConsolidadoExportTodas;
use App\Exports\CobrosDiariosExport;
use App\Exports\RecibosExport;
use App\Models\Pago;
use App\Models\Producto;
use App\Models\User;

class PedidosController extends Controller
{
    public function crearPedidosParaLocalidad(Request $request)
    {
        $request->validate([
            'localidade_id' => 'required|exists:localidades,id',
            'tipo_pedido' => 'required|in:0,1,2' // 0=Regulares, 1=Frial, 2=Frial Sofia
        ]);

        $localidadId = $request->localidade_id;
        $tipoPedido = $request->tipo_pedido;
        $localidad = Localidade::findOrFail($localidadId);

        // Determinar fecha según el tipo de pedido
        if ($tipoPedido == 0) {
            // Pedidos regulares: día siguiente
            $fechaPedido = now()->addDay();
        } else {
            // Pedidos Frial y Frial Sofia: mismo día
            $fechaPedido = now();
        }

        $fechaPedidoStr = $fechaPedido->format('Y-m-d');
        $pedidoDia = (int)$tipoPedido;

        // Verificar si ya existen pedidos para esta localidad, fecha y tipo
        $pedidosExistentes = Pedido::where('localidade_id', $localidadId)
            ->where('pedido_dia', $pedidoDia)
            ->whereDate('created_at', $fechaPedidoStr)
            ->count();

        if ($pedidosExistentes > 0) {
            $tipoNombre = $this->getTipoPedidoNombre($tipoPedido);
            return response()->json([
                'success' => false,
                'msg' => 'Ya se han generado pedidos ' . $tipoNombre . ' para ' . $localidad->nombre . ' para el día ' . $fechaPedidoStr . '. No se pueden generar nuevamente.'
            ], 409);
        }

        // Obtener clientes de la localidad ordenados por 'orden'
        $clientes = Cliente::where('localidade_id', $localidadId)
            ->where('habilitado', 1)
            ->orderBy('orden', 'asc')
            ->get();

        if ($clientes->isEmpty()) {
            return response()->json([
                'success' => false,
                'msg' => 'No hay clientes en esta localidad para crear pedidos.'
            ], 404);
        }

        // Obtener productos relacionados con la localidad
        $productosLocalidad = LocalidadesProductos::where('localidade_id', $localidadId)->get();

        if ($productosLocalidad->isEmpty()) {
            return response()->json([
                'success' => false,
                'msg' => 'No hay productos configurados para esta localidad.'
            ], 404);
        }

        DB::beginTransaction();

        try {
            // ✅ CAMBIO CLAVE: Lógica diferente para determinar el código según el tipo de pedido
            if ($tipoPedido == 0) {
                // Para pedidos regulares: incrementar código global
                $ultimoPedido = Pedido::orderBy('codigo', 'desc')->first();
                $nuevoCodigo = 1;
                if ($ultimoPedido && $ultimoPedido->codigo) {
                    $nuevoCodigo = intval($ultimoPedido->codigo) + 1;
                }
                \Log::info("Asignando código global NUEVO: {$nuevoCodigo} para pedidos REGULARES, localidad {$localidadId}, fecha: {$fechaPedidoStr}");
            } else {
                // Para pedidos Frial y Frial Sofia: buscar el código del pedido regular del día actual
                $pedidoRegularHoy = Pedido::where('localidade_id', $localidadId)
                    ->where('pedido_dia', 0) // Tipo regular
                    ->whereDate('created_at', now()->format('Y-m-d'))
                    ->first();

                if ($pedidoRegularHoy) {
                    $nuevoCodigo = $pedidoRegularHoy->codigo;
                    \Log::info("Asignando código EXISTENTE: {$nuevoCodigo} para pedidos {$this->getTipoPedidoNombre($tipoPedido)}, localidad {$localidadId}, usando código del pedido regular del día");
                } else {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'msg' => 'No se pueden generar pedidos ' . $this->getTipoPedidoNombre($tipoPedido) . ' porque no hay pedidos regulares generados para hoy en esta localidad.'
                    ], 409);
                }
            }

            $pedidosCreados = [];
            $pedidosProductosCreados = [];

            // Desactivar timestamps automáticos temporalmente
            Pedido::withoutTimestamps(function () use ($clientes, $localidadId, $pedidoDia, $fechaPedido, $nuevoCodigo, $fechaPedidoStr, $productosLocalidad, &$pedidosCreados, &$pedidosProductosCreados) {
                foreach ($clientes as $cliente) {
                    // Crear el pedido principal
                    $pedido = Pedido::create([
                        'platillo' => 0,
                        'subtotal' => 0,
                        'descuento_bs' => 0,
                        'pendiente' => 0,
                        'pagado' => '0',
                        'estado' => '0',
                        'cliente_id' => $cliente->id,
                        'localidade_id' => $localidadId,
                        'pedido_dia' => $pedidoDia,
                        'codigo_semana' => $fechaPedido->weekOfYear,
                        'codigo_dia' => $fechaPedido->day,
                        'dia' => $fechaPedido->dayOfWeek,
                        'codigo' => $nuevoCodigo, // ✅ Usar el código determinado según el tipo
                        'total_ingreso' => 0,
                        'generado' => 0,
                        'deshabilitado' => 0,
                        'created_at' => $fechaPedidoStr,
                        'updated_at' => $fechaPedidoStr,
                    ]);

                    $pedidosCreados[] = $pedido;

                    // Crear registros en pedido_productos para cada producto de la localidad
                    foreach ($productosLocalidad as $productoLocalidad) {
                        $pedidoProducto = PedidoProductos::create([
                            'n_cajas' => 0,
                            'peso' => 0,
                            'peso_pedido' => 0,
                            'tara' => 0,
                            'peso_real' => 0,
                            'precio' => $productoLocalidad->precio,
                            'costo_total' => 0,
                            'realizado' => '0',
                            'procesado' => '0',
                            'pedido_id' => $pedido->id,
                            'localidade_id' => $localidadId,
                            'cliente_id' => $cliente->id,
                            'producto_id' => $productoLocalidad->producto_id,
                            'compra_id' => 1,
                            'pedido_dia' => $pedidoDia,
                            'codigo_pedido' => $nuevoCodigo, // ✅ Mismo código para pedido_productos
                            'peso_compra' => 0,
                            'pv' => 0,
                            'descuento_compra' => 0,
                            'precio_compra' => 0,
                            'created_at' => $fechaPedidoStr,
                            'updated_at' => $fechaPedidoStr,
                        ]);

                        $pedidosProductosCreados[] = $pedidoProducto;
                    }
                }
            });

            DB::commit();

            $tipoNombre = $this->getTipoPedidoNombre($tipoPedido);
            return response()->json([
                'success' => true,
                'msg' => 'Pedidos ' . $tipoNombre . ' creados correctamente para ' . count($pedidosCreados) . ' clientes de ' . $localidad->nombre . ' para el día ' . $fechaPedidoStr . ' con código ' . $nuevoCodigo,
                'pedidos' => $pedidosCreados,
                'pedidos_productos' => $pedidosProductosCreados,
                'total_pedidos' => count($pedidosCreados),
                'total_pedidos_productos' => count($pedidosProductosCreados),
                'codigo_asignado' => $nuevoCodigo
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear pedidos para localidad: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'msg' => 'Error al crear los pedidos: ' . $e->getMessage()
            ], 500);
        }
    }

    // Método auxiliar para obtener el nombre del tipo de pedido
    private function getTipoPedidoNombre($tipoPedido)
    {
        $nombres = [
            0 => 'Regulares',
            1 => 'Frial',
            2 => 'Frial Sofia'
        ];
        return $nombres[$tipoPedido] ?? 'Desconocido';
    }

    public function verificarPedidosExistencia(Request $request)
    {
        $request->validate([
            'localidade_id' => 'required|exists:localidades,id',
            'fecha' => 'required|date',
            'tipo_pedido' => 'required|in:0,1,2'
        ]);

        $fecha = $request->fecha;
        $localidadId = $request->localidade_id;
        $tipoPedido = $request->tipo_pedido;

        // Buscar pedidos por localidad, fecha y tipo
        $pedidosExistentes = Pedido::where('localidade_id', $localidadId)
            ->where('pedido_dia', $tipoPedido)
            ->whereDate('created_at', $fecha)
            ->count();

        // Verificar si hay productos configurados para la localidad
        $productosConfigurados = LocalidadesProductos::where('localidade_id', $localidadId)->exists();

        // Obtener total de clientes para esta localidad
        $totalClientes = Cliente::where('localidade_id', $localidadId)->count();

        return response()->json([
            'existen_pedidos' => $pedidosExistentes > 0,
            'cantidad' => $pedidosExistentes,
            'puede_crear' => $pedidosExistentes === 0 && $productosConfigurados,
            'total_clientes' => $totalClientes,
            'productos_configurados' => $productosConfigurados,
            'fecha_verificada' => $fecha
        ]);
    }

    public function index(Request $request)
    {
        $query = Pedido::with(['localidad', 'cliente']);

        // Filtrar por localidad si se proporciona
        if ($request->has('localidad_id') && $request->localidad_id) {
            $query->where('localidade_id', $request->localidad_id);
        }

        // Filtrar por código si se proporciona
        if ($request->has('codigo') && $request->codigo) {
            $query->where('codigo', $request->codigo);
        }

        // Filtrar por fecha si se proporciona
        if ($request->has('fecha') && $request->fecha) {
            $query->whereDate('created_at', $request->fecha);
        }

        $pedidos = $query->orderBy('created_at', 'desc')->get();

        return view('admin.pedidos.index', compact('pedidos'));
    }

    public function mostrarFormularioRegistro($pedidoId)
    {
        $pedido = Pedido::with(['cliente', 'localidad', 'pedidos_productos.producto'])
            ->findOrFail($pedidoId);

        return view('admin.pedidos.registrar-form', compact('pedido'));
    }

    public function verPorCodigo($codigo, Request $request)
    {
        // Obtener parámetros de filtro
        $fecha = $request->get('fecha');
        $localidadId = $request->get('localidad_id');
        $search = $request->get('search');
        $tipoPedido = $request->get('tipo_pedido', 0);

        // Construir consulta base para cada tipo de pedido ORDENADO POR clientes.orden
        $queryRegulares = Pedido::with(['localidad', 'cliente', 'pedidos_productos.producto'])
            ->where('codigo', $codigo)
            ->where('pedido_dia', 0)
            ->join('clientes', 'pedidos.cliente_id', '=', 'clientes.id')
            ->orderBy('clientes.orden', 'asc')
            ->select('pedidos.*');

        $queryFrial = Pedido::with(['localidad', 'cliente', 'pedidos_productos.producto'])
            ->where('codigo', $codigo)
            ->where('pedido_dia', 1)
            ->join('clientes', 'pedidos.cliente_id', '=', 'clientes.id')
            ->orderBy('clientes.orden', 'asc')
            ->select('pedidos.*');

        $queryFrialSofia = Pedido::with(['localidad', 'cliente', 'pedidos_productos.producto'])
            ->where('codigo', $codigo)
            ->where('pedido_dia', 2)
            ->join('clientes', 'pedidos.cliente_id', '=', 'clientes.id')
            ->orderBy('clientes.orden', 'asc')
            ->select('pedidos.*');

        // Aplicar filtros si se proporcionan
        if ($fecha) {
            $queryRegulares->whereDate('pedidos.created_at', $fecha);
            $queryFrial->whereDate('pedidos.created_at', $fecha);
            $queryFrialSofia->whereDate('pedidos.created_at', $fecha);
        }

        if ($localidadId) {
            $queryRegulares->where('pedidos.localidade_id', $localidadId);
            $queryFrial->where('pedidos.localidade_id', $localidadId);
            $queryFrialSofia->where('pedidos.localidade_id', $localidadId);
        }

        // Aplicar búsqueda si se proporciona
        if ($search) {
            $searchCallback = function ($query) use ($search) {
                $query->where('clientes.nombres', 'like', "%{$search}%")
                    ->orWhere('clientes.apellidos', 'like', "%{$search}%")
                    ->orWhere('clientes.ci', 'like', "%{$search}%");
            };

            $queryRegulares->where($searchCallback);
            $queryFrial->where($searchCallback);
            $queryFrialSofia->where($searchCallback);
        }

        // Obtener información del grupo de pedidos para estadísticas
        $primerPedido = $queryRegulares->first() ?? $queryFrial->first() ?? $queryFrialSofia->first();

        // Obtener la fecha de los pedidos regulares para validación
        $fechaPedidosRegulares = null;
        $pedidoRegular = $queryRegulares->first();
        if ($pedidoRegular) {
            $fechaPedidosRegulares = $pedidoRegular->created_at->format('Y-m-d');
        }

        // Determinar si se pueden generar pedidos Frial y Frial Sofia
        $fechaActual = now()->format('Y-m-d');
        $puedeGenerarFrial = false;
        $puedeGenerarFrialSofia = false;

        if ($fechaPedidosRegulares) {
            // Solo se pueden generar pedidos Frial/Frial Sofia el mismo día de los pedidos regulares
            $puedeGenerarFrial = ($fechaPedidosRegulares === $fechaActual);
            $puedeGenerarFrialSofia = ($fechaPedidosRegulares === $fechaActual);
        }

        // Contar totales después de aplicar filtros
        $totalPedidosRegulares = $queryRegulares->count();
        $totalPedidosFrial = $queryFrial->count();
        $totalPedidosFrialSofia = $queryFrialSofia->count();
        $totalPedidos = $totalPedidosRegulares + $totalPedidosFrial + $totalPedidosFrialSofia;

        // Obtener total de clientes para la localidad (si hay filtro)
        $totalClientes = 0;
        if ($localidadId) {
            $totalClientes = Cliente::where('localidade_id', $localidadId)->count();
        } elseif ($primerPedido && $primerPedido->localidade_id) {
            $totalClientes = Cliente::where('localidade_id', $primerPedido->localidade_id)->count();
        }

        // Paginación - mantener los parámetros de filtro en la paginación
        $pedidosRegulares = $queryRegulares->paginate(90, ['*'], 'page_regulares')
            ->appends([
                'fecha' => $fecha,
                'localidad_id' => $localidadId,
                'search' => $search,
                'tipo_pedido' => $tipoPedido
            ]);

        $pedidosFrial = $queryFrial->paginate(9999, ['*'], 'page_frial')
            ->appends([
                'fecha' => $fecha,
                'localidad_id' => $localidadId,
                'search' => $search,
                'tipo_pedido' => $tipoPedido
            ]);

        $pedidosFrialSofia = $queryFrialSofia->paginate(9999, ['*'], 'page_frial_sofia')
            ->appends([
                'fecha' => $fecha,
                'localidad_id' => $localidadId,
                'search' => $search,
                'tipo_pedido' => $tipoPedido
            ]);

        return view('admin.pedidos.ver-por-codigo', compact(
            'codigo',
            'primerPedido',
            'totalPedidos',
            'totalClientes',
            'fecha',
            'localidadId',
            'search',
            'pedidosRegulares',
            'pedidosFrial',
            'pedidosFrialSofia',
            'totalPedidosRegulares',
            'totalPedidosFrial',
            'totalPedidosFrialSofia',
            'puedeGenerarFrial',
            'puedeGenerarFrialSofia',
            'fechaPedidosRegulares',
            'fechaActual'
        ));
    }

    public function obtenerDetalleCompleto($pedidoId)
    {
        try {
            $pedido = Pedido::with([
                'cliente',
                'localidad',
                'pedidos_productos.producto',
                'pedidos_productos' => function ($query) {
                    $query->where('realizado', '1')
                        ->orWhere('procesado', '1');
                }
            ])->findOrFail($pedidoId);

            // Separar productos por tipo y calcular subtotales individuales
            $productosNormales = [];
            $polloPrimeraItems = [];
            $totalPesoPedido = 0;
            $totalPesoReal = 0;
            $totalCosto = 0;

            foreach ($pedido->pedidos_productos as $producto) {
                if ($producto->costo_total > 0 || $producto->peso_pedido > 0) {
                    // Calcular el subtotal de cada producto (peso real * precio)
                    $subtotalProducto = $producto->peso_real * $producto->precio;

                    $productoData = [
                        'id' => $producto->id,
                        'producto_nombre' => $producto->producto->nombre ?? 'N/A',
                        'peso_pedido' => $producto->peso_pedido,
                        'peso_real' => $producto->peso_real,
                        'tara' => $producto->tara,
                        'n_cajas' => $producto->n_cajas,
                        'precio_unitario' => $producto->precio,
                        'subtotal' => $subtotalProducto, // Peso real * precio
                        'costo_total' => $producto->costo_total, // Ya redondeado en la base de datos
                        'realizado' => $producto->realizado,
                        'procesado' => $producto->procesado,
                        'es_con_tara' => in_array($producto->producto_id ?? 0, [1, 2, 3]) ? 1 : 0
                    ];

                    // Consolidar pollo primera (IDs 1, 2, 3)
                    if (in_array($producto->producto_id ?? 0, [1, 2, 3])) {
                        $polloPrimeraItems[] = $productoData;
                    } else {
                        $productosNormales[] = $productoData;
                    }

                    $totalPesoPedido += $producto->peso_pedido;
                    $totalPesoReal += $producto->peso_real;
                    $totalCosto += $producto->costo_total;
                }
            }

            // Calcular totales para pollo primera
            $polloPrimeraPesoTotal = 0;
            $polloPrimeraSubtotal = 0; // Subtotal de pollo primera (peso real * precio)
            $polloPrimeraCostoTotal = 0; // Costo total redondeado
            $polloPrimeraPrecio = null;
            $polloPrimeraPesoPedidoTotal = 0;

            foreach ($polloPrimeraItems as $producto) {
                $polloPrimeraPesoTotal += $producto['peso_real'];
                $polloPrimeraSubtotal += $producto['subtotal'];
                $polloPrimeraCostoTotal += $producto['costo_total'];
                $polloPrimeraPesoPedidoTotal += $producto['peso_pedido'];
                if ($polloPrimeraPrecio === null && $producto['precio_unitario'] > 0) {
                    $polloPrimeraPrecio = $producto['precio_unitario'];
                }
            }

            // Calcular subtotal de productos normales
            $subtotalProductosNormales = array_sum(array_column($productosNormales, 'subtotal'));
            $costoTotalProductosNormales = array_sum(array_column($productosNormales, 'costo_total'));

            return response()->json([
                'success' => true,
                'pedido' => [
                    'id' => $pedido->id,
                    'codigo' => $pedido->codigo,
                    'subtotal' => $pedido->subtotal,
                    'descuento_bs' => $pedido->descuento_bs,
                    'pendiente' => $pedido->pendiente,
                    'pagado' => $pedido->pagado,
                    'estado' => $pedido->estado,
                    'created_at' => $pedido->created_at->format('d/m/Y'),
                    'tipo_pedido' => $this->getTipoPedidoNombre($pedido->pedido_dia)
                ],
                'cliente' => [
                    'id' => $pedido->cliente->id,
                    'nombre_completo' => $pedido->cliente->nombres . ' ' . $pedido->cliente->apellidos,
                    'ci' => $pedido->cliente->ci
                ],
                'localidad' => [
                    'nombre' => $pedido->localidad->nombre ?? 'N/A',
                    'color' => $pedido->localidad->color ?? '#405189'
                ],
                'productos_normales' => $productosNormales,
                'pollo_primera_items' => $polloPrimeraItems,
                'pollo_primera_totales' => [
                    'peso_total' => $polloPrimeraPesoTotal,
                    'subtotal' => $polloPrimeraSubtotal, // Subtotal sin redondear
                    'costo_total' => $polloPrimeraCostoTotal, // Costo total redondeado
                    'precio' => $polloPrimeraPrecio,
                    'peso_pedido_total' => $polloPrimeraPesoPedidoTotal
                ],
                'totales_parciales' => [
                    'subtotal_productos_normales' => $subtotalProductosNormales,
                    'costo_total_productos_normales' => $costoTotalProductosNormales
                ],
                'totales_generales' => [
                    'peso_pedido_total' => $totalPesoPedido,
                    'peso_real_total' => $totalPesoReal,
                    'subtotal_total' => $subtotalProductosNormales + $polloPrimeraSubtotal, // Suma de subtotales
                    'costo_total' => $totalCosto, // Suma de costos totales redondeados
                    'deuda_pendiente' => $pedido->pendiente
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al obtener detalle del pedido: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => 'Error al cargar los detalles del pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    public function registrarProductos(Request $request, $pedidoId)
    {
        $request->validate([
            'pedido_productos' => 'required|array',
            'pedido_productos.*.id' => 'required|exists:pedido_productos,id',
            'pedido_productos.*.producto_id' => 'required|exists:productos,id',
            'pedido_productos.*.peso_pedido' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $pedido = Pedido::findOrFail($pedidoId);
            $tienePedidosRegistrados = $pedido->pedidos_productos->where('realizado', '1')->count() > 0;

            foreach ($request->pedido_productos as $pedidoProductoId => $item) {
                $pesoPedido = $item['peso_pedido'];

                PedidoProductos::where('id', $item['id'])
                    ->update([
                        'peso_pedido' => $pesoPedido,
                        'realizado' => $pesoPedido > 0 ? '1' : '0',
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();

            $mensaje = $tienePedidosRegistrados
                ? 'Pedido modificado correctamente.'
                : 'Pedido registrado correctamente.';

            return redirect()->back()->with('success', $mensaje);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar pesos de productos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al actualizar los pesos: ' . $e->getMessage());
        }
    }

    public function procesarProductos(Request $request, $pedidoId)
    {
        $request->validate([
            'pedido_productos' => 'required|array',
            'pedido_productos.*.id' => 'required|exists:pedido_productos,id',
            'pedido_productos.*.peso_pedido' => 'required|numeric|min:0',
            'pedido_productos.*.peso' => 'sometimes|required|numeric|min:0',
            'pedido_productos.*.precio' => 'sometimes|required|numeric|min:0',
            'pedido_productos.*.n_cajas' => 'sometimes|integer|min:0',
            'pedido_productos.*.tara' => 'sometimes|numeric|min:0',
            'cajas_con_tara' => 'sometimes|required|integer|min:0',
            'peso_con_tara' => 'sometimes|required|numeric|min:0',
            'tara_con_tara' => 'sometimes|required|numeric|min:0',
            'precio_con_tara' => 'sometimes|required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Procesar productos con tara (si existen)
            if ($request->has('cajas_con_tara')) {
                $productosConTara = collect($request->pedido_productos)->filter(function ($item) {
                    return isset($item['es_con_tara']) && $item['es_con_tara'] == 1;
                });

                if ($productosConTara->count() > 0) {
                    $pesoTotalConTara = $request->peso_con_tara;
                    $taraTotal = $request->tara_con_tara;
                    // Por cada 1 kg de tara, se descuentan 2 kg del peso total
                    $descuentoTara = 2 * $taraTotal;
                    $pesoRealTotal = max(0, $pesoTotalConTara - $descuentoTara);
                    $precioConTara = $request->precio_con_tara;
                    $costoTotalConTara = $pesoRealTotal * $precioConTara;

                    // Distribuir el peso real entre los productos con tara proporcionalmente a su peso pedido
                    $pesoPedidoTotalConTara = $productosConTara->sum('peso_pedido');

                    foreach ($productosConTara as $item) {
                        $pedidoProducto = PedidoProductos::find($item['id']);

                        if ($pesoPedidoTotalConTara > 0) {
                            $proporcion = $item['peso_pedido'] / $pesoPedidoTotalConTara;
                            $pesoRealIndividual = $pesoRealTotal * $proporcion;
                            $pesoIndividual = $pesoTotalConTara * $proporcion;
                            $taraIndividual = $taraTotal * $proporcion;
                            $costoTotalIndividual = $pesoRealIndividual * $precioConTara;
                        } else {
                            $pesoRealIndividual = 0;
                            $pesoIndividual = 0;
                            $taraIndividual = 0;
                            $costoTotalIndividual = 0;
                        }

                        // REDONDEAR el costo total a número entero (sin decimales)
                        $costoTotalIndividualRedondeado = round($costoTotalIndividual);

                        $pedidoProducto->update([
                            'n_cajas' => $request->cajas_con_tara, // Mismo número de cajas para todos
                            'peso' => $pesoIndividual,
                            'tara' => $taraIndividual,
                            'peso_real' => $pesoRealIndividual,
                            'precio' => $precioConTara,
                            'costo_total' => $costoTotalIndividualRedondeado, // Usar valor redondeado a entero
                            'procesado' => '1',
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Procesar productos sin tara
            foreach ($request->pedido_productos as $pedidoProductoId => $item) {
                // Saltar productos con tara que ya fueron procesados
                if (isset($item['es_con_tara']) && $item['es_con_tara'] == 1) {
                    continue;
                }

                $pedidoProducto = PedidoProductos::find($item['id']);

                // Para productos sin tara
                $pesoReal = $item['peso'];
                $costoTotal = $pesoReal * $item['precio'];

                // REDONDEAR el costo total a número entero (sin decimales)
                $costoTotalRedondeado = round($costoTotal);

                $pedidoProducto->update([
                    'n_cajas' => 0,
                    'peso' => $item['peso'],
                    'tara' => 0,
                    'peso_real' => $pesoReal,
                    'precio' => $item['precio'],
                    'costo_total' => $costoTotalRedondeado, // Usar valor redondeado a entero
                    'procesado' => '1',
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Pedido procesado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al procesar pedido: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al procesar el pedido: ' . $e->getMessage());
        }
    }

    public function generarDeuda(Request $request, $pedidoId)
    {
        $pedido = Pedido::findOrFail($pedidoId);

        $total = PedidoProductos::where('pedido_id', $pedidoId)
            ->where('procesado', '1')
            ->sum('costo_total');

        DB::beginTransaction();
        try {
            $pedido->update([
                'subtotal' => $total,
            ]);

            DB::commit();

            // Obtener la fecha del pedido para redirigir al reporte consolidado
            $fechaPedido = $pedido->created_at->format('Y-m-d');

            return redirect()->route('admin.pedidos.reporte-consolidado', ['fecha' => $fechaPedido])
                ->with('success', 'Deuda generada correctamente. Subtotal: Bs. ' . number_format($total, 2));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al generar deuda: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar la deuda: ' . $e->getMessage());
        }
    }

    public function verDetalleProcesado($pedidoId)
    {
        $pedido = Pedido::with(['cliente', 'localidad', 'pedidos_productos.producto'])
            ->findOrFail($pedidoId);

        $productosProcesados = $pedido->pedidos_productos->where('procesado', '1');
        $total = $productosProcesados->sum('costo_total');

        return view('admin.pedidos.detalle-procesado', compact('pedido', 'productosProcesados', 'total'));
    }


    public function generarReporteDeudasPdf(Request $request)
    {
        // Validación automática - Laravel maneja correctamente el string "0"
        $validated = $request->validate([
            'codigo' => 'required|integer',
            'tipo_pedido' => 'required|in:0,1,2',
            'fecha' => 'nullable|date',
            'localidad_id' => 'required|exists:localidades,id'
        ]);

        $codigo = $validated['codigo'];
        $tipoPedido = $validated['tipo_pedido'];
        $fecha = $validated['fecha'] ?? null;
        $localidadId = $validated['localidad_id'];

        // Obtener la localidad
        $localidad = Localidade::findOrFail($localidadId);

        // Obtener todos los clientes de la localidad ORDENADOS por orden
        $todosClientes = Cliente::where('localidade_id', $localidadId)
            ->orderBy('orden', 'asc')
            ->get();

        // Obtener clientes con deuda (subtotal > 0) ORDENADOS por clientes.orden
        $query = Pedido::with('cliente')
            ->where('pedidos.codigo', $codigo)  // Especificar tabla
            ->where('pedidos.pedido_dia', $tipoPedido)  // Especificar tabla
            ->where('pedidos.localidade_id', $localidadId)  // Especificar tabla
            ->where('pedidos.subtotal', '>', 0)  // Especificar tabla
            ->join('clientes', 'pedidos.cliente_id', '=', 'clientes.id')
            ->orderBy('clientes.orden', 'asc')
            ->select('pedidos.*');  // Solo columnas de pedidos

        if ($fecha) {
            $query->whereDate('pedidos.created_at', $fecha);  // Especificar tabla
        }

        $clientesConDeuda = $query->get();

        $totalDeuda = $clientesConDeuda->sum('subtotal');

        // Determinar el título del tipo de pedido
        $titulos = [0 => 'Regulares', 1 => 'Frial', 2 => 'Frial Sofia'];
        $titulo = $titulos[$tipoPedido] ?? 'Desconocido';

        // Obtener la fecha del pedido (usando la misma lógica con JOIN para evitar ambigüedad)
        $pedidoReferencia = Pedido::where('pedidos.codigo', $codigo)
            ->where('pedidos.pedido_dia', $tipoPedido)
            ->where('pedidos.localidade_id', $localidadId)
            ->when($fecha, function ($query, $fecha) {
                return $query->whereDate('pedidos.created_at', $fecha);
            })
            ->join('clientes', 'pedidos.cliente_id', '=', 'clientes.id')
            ->orderBy('clientes.orden', 'asc')
            ->select('pedidos.*')
            ->first();

        $fechaPedido = $pedidoReferencia ? $pedidoReferencia->created_at->format('d/m/Y') : ($fecha ? \Carbon\Carbon::parse($fecha)->format('d/m/Y') : now()->format('d/m/Y'));

        $data = [
            'clientesConDeuda' => $clientesConDeuda,
            'totalDeuda' => $totalDeuda,
            'codigo' => $codigo,
            'titulo' => $titulo,
            'localidad' => $localidad,
            'fecha' => $fechaPedido,
            'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
            'todosClientes' => $todosClientes,
        ];

        $pdf = PDF::loadView('admin.pedidos.reportes.deudas-pdf', $data);

        // Configurar opciones del PDF
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Arial'
        ]);

        return $pdf->download("Reporte_Deudas_{$titulo}_Codigo{$codigo}_{$localidad->nombre}.pdf");
    }

    public function generarReporteDetallePedidosPdf(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|integer',
            'tipo_pedido' => 'required|in:0,1,2',
            'fecha' => 'nullable|date',
            'localidad_id' => 'required|exists:localidades,id'
        ]);

        $codigo = $validated['codigo'];
        $tipoPedido = $validated['tipo_pedido'];
        $fecha = $validated['fecha'] ?? null;
        $localidadId = $validated['localidad_id'];

        // Obtener la localidad
        $localidad = Localidade::findOrFail($localidadId);

        // Obtener todos los pedidos con sus productos PROCESADOS
        $pedidos = Pedido::with(['cliente', 'pedidos_productos.producto'])
            ->where('pedidos.codigo', $codigo)
            ->where('pedidos.pedido_dia', $tipoPedido)
            ->where('pedidos.localidade_id', $localidadId)
            ->when($fecha, function ($query, $fecha) {
                return $query->whereDate('pedidos.created_at', $fecha);
            })
            ->whereHas('pedidos_productos', function ($query) {
                $query->where('procesado', '1'); // Solo productos procesados
            })
            ->join('clientes', 'pedidos.cliente_id', '=', 'clientes.id')
            ->orderBy('clientes.orden', 'asc')
            ->select('pedidos.*')
            ->get();

        // Si no hay pedidos, retornar error
        if ($pedidos->isEmpty()) {
            abort(404, 'No hay pedidos procesados para generar el reporte');
        }

        // Determinar el título del tipo de pedido
        $titulos = [0 => 'Regulares', 1 => 'Frial', 2 => 'Frial Sofia'];
        $titulo = $titulos[$tipoPedido] ?? 'Desconocido';

        // Obtener la fecha del pedido
        $pedidoReferencia = $pedidos->first();
        $fechaPedido = $pedidoReferencia ? $pedidoReferencia->created_at->format('d/m/Y') : ($fecha ? \Carbon\Carbon::parse($fecha)->format('d/m/Y') : now()->format('d/m/Y'));

        $data = [
            'pedidos' => $pedidos,
            'codigo' => $codigo,
            'titulo' => $titulo,
            'localidad' => $localidad,
            'fecha' => $fechaPedido,
            'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
        ];

        $pdf = PDF::loadView('admin.pedidos.reportes.detalle-pedidos-pdf', $data);

        // Configurar el papel a carta vertical
        $pdf->setPaper('letter', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Arial',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10
        ]);

        return $pdf->download("Reporte_Detalle_Pedidos_{$titulo}_Codigo{$codigo}_{$localidad->nombre}.pdf");
    }

    public function mostrarFormularioModificacion($pedidoId)
    {
        $pedido = Pedido::with(['cliente', 'localidad', 'pedidos_productos.producto'])
            ->findOrFail($pedidoId);

        return view('admin.pedidos.modificar-form', compact('pedido'));
    }

    public function mostrarFormularioProcesamiento($pedidoId)
    {
        $pedido = Pedido::with(['cliente', 'localidad', 'pedidos_productos.producto'])
            ->findOrFail($pedidoId);

        return view('admin.pedidos.procesar-form', compact('pedido'));
    }

    public function reporteConsolidado(Request $request)
    {
        // Obtener parámetros de filtro
        $fecha = $request->get('fecha', now()->format('Y-m-d'));
        $search = $request->get('search');
        $tipoPedido = $request->get('tipo_pedido', 0);

        // Obtener todos los productos para las columnas
        $productos = Producto::orderBy('id')->get();
        $localidades = Localidade::all();

        $tipos = [0 => 'Regulares', 1 => 'Frial', 2 => 'Frial Sofia'];
        $pedidosPorTipo = [];
        $totalesPorTipo = [];

        foreach ($tipos as $tipoId => $tipoNombre) {
            // 🔴 **NUEVA CONSULTA: Más específica para asegurar que trae pedidos con productos realizados**
            $query = Pedido::with(['localidad', 'cliente'])
                ->whereDate('created_at', $fecha)
                ->where('pedido_dia', $tipoId)
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('pedido_productos')
                        ->whereColumn('pedido_productos.pedido_id', 'pedidos.id')
                        ->where('pedido_productos.realizado', '1')
                        ->where('pedido_productos.peso_pedido', '>', 0);
                });

            // Cargar productos realizados para cada pedido
            $query->with(['pedidos_productos' => function ($q) {
                $q->where('realizado', '1')->with('producto');
            }]);

            // Aplicar búsqueda
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('cliente', function ($q) use ($search) {
                        $q->where('nombres', 'like', "%{$search}%")
                            ->orWhere('apellidos', 'like', "%{$search}%")
                            ->orWhere('ci', 'like', "%{$search}%");
                    })->orWhereHas('localidad', function ($q) use ($search) {
                        $q->where('nombre', 'like', "%{$search}%");
                    });
                });
            }

            // Ordenamiento
            $query->orderByRaw("
            CASE 
                WHEN localidade_id = 3 THEN 1
                WHEN localidade_id = 2 THEN 2
                WHEN localidade_id = 1 THEN 3
                WHEN localidade_id = 4 THEN 4
                ELSE 5
            END
        ")
                ->orderByRaw('(SELECT orden FROM clientes WHERE clientes.id = pedidos.cliente_id) ASC');

            $pedidosPorTipo[$tipoId] = $query->get();
            $totalesPorTipo[$tipoId] = $pedidosPorTipo[$tipoId]->count();
        }

        $totalGeneral = array_sum($totalesPorTipo);

        return view('admin.pedidos.reporte-consolidado', compact(
            'fecha',
            'search',
            'pedidosPorTipo',
            'productos',
            'localidades',
            'tipoPedido',
            'totalesPorTipo',
            'totalGeneral',
            'tipos'
        ));
    }

    public function generarReporteIndividualPdf($pedidoId)
    {
        $pedido = Pedido::with(['cliente', 'localidad', 'pedidos_productos.producto'])
            ->findOrFail($pedidoId);

        // Verificar que el pedido tenga productos procesados
        $productosProcesados = $pedido->pedidos_productos->where('procesado', '1');

        if ($productosProcesados->isEmpty()) {
            abort(404, 'El pedido no tiene productos procesados');
        }

        // Obtener la fecha del pedido
        $fecha = $pedido->created_at->format('Y-m-d');
        $fechaFormateada = $pedido->created_at->format('d/m/Y');

        // Separar productos normales de pollo primera (id 1, 2, 3)
        $productosNormales = [];
        $polloPrimeraItems = [];

        foreach ($productosProcesados as $producto) {
            if ($producto->costo_total > 0) {
                if (in_array($producto->producto_id ?? ($producto->producto->id ?? null), [1, 2, 3])) {
                    $polloPrimeraItems[] = $producto;
                } else {
                    $productosNormales[] = $producto;
                }
            }
        }

        // Calcular totales para pollo primera
        $polloPrimeraPesoTotal = 0;
        $polloPrimeraCostoTotal = 0;
        $polloPrimeraPrecio = null;
        $polloPrimeraPesoPedidoTotal = 0;

        foreach ($polloPrimeraItems as $producto) {
            $polloPrimeraPesoTotal += $producto->peso_real;
            $polloPrimeraCostoTotal += $producto->costo_total;
            $polloPrimeraPesoPedidoTotal += $producto->peso_pedido;
            if ($polloPrimeraPrecio === null) {
                $polloPrimeraPrecio = $producto->precio;
            }
        }

        // Calcular total general
        $totalGeneral = $productosProcesados->sum('costo_total');

        $data = [
            'pedido' => $pedido,
            'productosNormales' => $productosNormales,
            'polloPrimeraItems' => $polloPrimeraItems,
            'polloPrimeraPesoTotal' => $polloPrimeraPesoTotal,
            'polloPrimeraCostoTotal' => $polloPrimeraCostoTotal,
            'polloPrimeraPrecio' => $polloPrimeraPrecio,
            'polloPrimeraPesoPedidoTotal' => $polloPrimeraPesoPedidoTotal,
            'totalGeneral' => $totalGeneral,
            'fecha' => $fechaFormateada,
            'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
            'titulo' => $this->getTipoPedidoNombre($pedido->pedido_dia),
            'localidad' => $pedido->localidad,
        ];

        $pdf = PDF::loadView('admin.pedidos.reportes.detalle-individual-pdf', $data);

        // Configurar el papel a carta vertical
        $pdf->setPaper('letter', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Arial',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10
        ]);

        return $pdf->download("Reporte_Pedido_{$pedido->id}_{$pedido->cliente->nombres}_{$pedido->cliente->apellidos}.pdf");
    }

    public function generarReporteConsolidadoPdf(Request $request)
    {
        $validated = $request->validate([
            'fecha' => 'required|date',
            'tipo_pedido' => 'required|in:0,1,2'
        ]);

        $fecha = $validated['fecha'];
        $tipoPedido = $validated['tipo_pedido'];
        $search = $request->get('search', '');

        // Obtener todos los productos para las columnas
        $productos = Producto::orderBy('id')->get();

        // Construir consulta con el mismo orden que en reporteConsolidado
        $query = Pedido::with([
            'localidad',
            'cliente',
            'pedidos_productos' => function ($q) {
                $q->where('realizado', '1')
                    ->with('producto');
            }
        ])->whereHas('pedidos_productos', function ($query) {
            $query->where('realizado', '1');
        })->whereDate('pedidos.created_at', $fecha)
            ->where('pedido_dia', $tipoPedido);

        // Aplicar búsqueda si se proporciona
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('cliente', function ($q) use ($search) {
                    $q->where('nombres', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%")
                        ->orWhere('ci', 'like', "%{$search}%");
                })->orWhereHas('localidad', function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%");
                });
            });
        }

        // Ordenamiento específico por localidades (id 3, 2, 1, 4) y luego por orden del cliente
        $query->join('clientes', 'pedidos.cliente_id', '=', 'clientes.id')
            ->join('localidades', 'pedidos.localidade_id', '=', 'localidades.id')
            ->orderByRaw('FIELD(localidades.id, 3, 2, 1, 4)') // ORDEN ESPECÍFICO
            ->orderBy('clientes.orden')
            ->select('pedidos.*');

        $pedidos = $query->get();

        // Preparar datos para el reporte
        $datosReporte = [];
        $totalesPesos = array_fill(0, count($productos), 0);

        foreach ($pedidos as $index => $pedido) {
            $fila = [
                'numero' => $index + 1,
                'cliente_nombre' => $pedido->cliente->nombres . ' ' . $pedido->cliente->apellidos,
                'cliente_ci' => $pedido->cliente->ci,
                'localidad' => $pedido->localidad->nombre,
                'productos_peso' => [],
            ];

            foreach ($productos as $productoIndex => $producto) {
                $pedidoProducto = $pedido->pedidos_productos->firstWhere('producto_id', $producto->id);
                $pesoPedido = $pedidoProducto ? ($pedidoProducto->peso_pedido ?? 0) : 0;

                $fila['productos_peso'][$productoIndex] = $pesoPedido;

                // Sumar a totales
                $totalesPesos[$productoIndex] += $pesoPedido;
            }

            $datosReporte[] = $fila;
        }

        // Calcular totales divididos por n_caja y redondeados (CANTIDAD DE CAJAS)
        $cantidadCajas = [];
        foreach ($productos as $index => $producto) {
            $totalPeso = $totalesPesos[$index];
            $nCaja = $producto->n_caja > 0 ? $producto->n_caja : 1; // Evitar división por cero
            $totalDividido = $totalPeso / $nCaja;
            $cantidadCajas[$index] = round($totalDividido); // Redondear
        }

        $fechaGeneracion = now()->format('d/m/Y H:i:s');
        $totalPedidos = $pedidos->count();

        // Determinar el título del tipo de pedido
        $titulos = [0 => 'Regulares', 1 => 'Frial', 2 => 'Frial Sofia'];
        $titulo = $titulos[$tipoPedido] ?? 'Desconocido';

        $data = [
            'datosReporte' => $datosReporte,
            'productos' => $productos,
            'fecha' => \Carbon\Carbon::parse($fecha)->format('d/m/Y'),
            'tipoPedido' => $titulo,
            'fechaGeneracion' => $fechaGeneracion,
            'totalPedidos' => $totalPedidos,
            'titulo' => $titulo,
            'totalesPesos' => $totalesPesos, // TOTALES (suma de pesos)
            'cantidadCajas' => $cantidadCajas, // CANTIDAD DE CAJAS (dividido por n_caja y redondeado)
        ];

        $pdf = PDF::loadView('admin.pedidos.reportes.consolidado-pdf-todas', $data);

        // Configurar papel y orientación
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'dpi' => 150,
            'defaultFont' => 'Arial',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'margin_left' => 10,
            'margin_right' => 10
        ]);

        return $pdf->download("reporte-consolidado-{$titulo}-{$fecha}.pdf");
    }

    public function generarReporteConsolidadoExcel(Request $request)
    {
        $validated = $request->validate([
            'fecha' => 'required|date',
            'tipo_pedido' => 'required|in:0,1,2'
        ]);

        $fecha = $validated['fecha'];
        $tipoPedido = $validated['tipo_pedido'];
        $search = $request->get('search', '');

        // Obtener todos los productos para las columnas
        $productos = Producto::orderBy('id')->get();

        // Construir consulta con el mismo orden que en reporteConsolidado
        $query = Pedido::with([
            'localidad',
            'cliente',
            'pedidos_productos' => function ($q) {
                $q->where('realizado', '1')
                    ->with('producto');
            }
        ])->whereHas('pedidos_productos', function ($query) {
            $query->where('realizado', '1');
        })->whereDate('pedidos.created_at', $fecha)
            ->where('pedido_dia', $tipoPedido);

        // Aplicar búsqueda si se proporciona
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('cliente', function ($q) use ($search) {
                    $q->where('nombres', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%")
                        ->orWhere('ci', 'like', "%{$search}%");
                })->orWhereHas('localidad', function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%");
                });
            });
        }

        // Ordenamiento específico por localidades (id 3, 2, 1, 4) y luego por orden del cliente
        $query->join('clientes', 'pedidos.cliente_id', '=', 'clientes.id')
            ->join('localidades', 'pedidos.localidade_id', '=', 'localidades.id')
            ->orderByRaw('FIELD(localidades.id, 3, 2, 1, 4)')
            ->orderBy('clientes.orden')
            ->select('pedidos.*');

        $pedidos = $query->get();

        // Preparar datos para el Excel
        $datosReporte = [];
        $totalesPesos = array_fill(0, count($productos), 0);

        foreach ($pedidos as $index => $pedido) {
            $fila = [
                'numero' => $index + 1,
                'cliente_nombre' => $pedido->cliente->nombres . ' ' . $pedido->cliente->apellidos,
                'cliente_ci' => $pedido->cliente->ci,
                'localidad' => $pedido->localidad->nombre,
            ];

            foreach ($productos as $productoIndex => $producto) {
                $pedidoProducto = $pedido->pedidos_productos->firstWhere('producto_id', $producto->id);
                $pesoPedido = $pedidoProducto ? ($pedidoProducto->peso_pedido ?? 0) : 0;

                $fila["peso_{$producto->id}"] = $pesoPedido;

                // Sumar a totales
                $totalesPesos[$productoIndex] += $pesoPedido;
            }

            $datosReporte[] = $fila;
        }

        // Calcular CANTIDAD DE CAJAS (dividido por n_caja y redondeado)
        $cantidadCajas = [];
        foreach ($productos as $index => $producto) {
            $totalPeso = $totalesPesos[$index];
            $nCaja = $producto->n_caja > 0 ? $producto->n_caja : 1;
            $totalDividido = $totalPeso / $nCaja;
            $cantidadCajas[$index] = round($totalDividido);
        }

        $totalPedidos = $pedidos->count();
        $tipoNombre = $tipoPedido == 0 ? 'Regulares' : ($tipoPedido == 1 ? 'Frial' : 'Frial Sofia');
        $filename = "reporte-consolidado-{$tipoNombre}-{$fecha}.xlsx";

        return Excel::download(
            new ConsolidadoExportTodas($datosReporte, $productos, $fecha, $tipoPedido, $totalPedidos, $totalesPesos, $cantidadCajas),
            $filename
        );
    }


    public function listarRecibos(Request $request)
    {
        $search = $request->get('search', '');
        $fechaInicio = $request->get('fecha_inicio', '');
        $fechaFin = $request->get('fecha_fin', '');
        $tipoPago = $request->get('tipo_pago', '');
        $localidadId = $request->get('localidad_id', '');

        // Query principal con todas las relaciones necesarias
        $query = Pago::with([
            'cliente',
            'cliente.localidad',
            'usuario',
            'detalles',
            'pedidos' => function ($query) {
                $query->withPivot('monto_asignado', 'descuento_asignado');
            }
        ]);

        // Aplicar filtros
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('recibo', 'like', "%{$search}%")
                    ->orWhereHas('cliente', function ($q) use ($search) {
                        $q->where('ci', 'like', "%{$search}%")
                            ->orWhere('nombres', 'like', "%{$search}%")
                            ->orWhere('apellidos', 'like', "%{$search}%");
                    });
            });
        }

        if ($fechaInicio) {
            $query->whereDate('created_at', '>=', $fechaInicio);
        }

        if ($fechaFin) {
            $query->whereDate('created_at', '<=', $fechaFin);
        }

        if ($tipoPago) {
            $query->where('tipo', $tipoPago);
        }

        if ($localidadId) {
            $query->whereHas('cliente', function ($q) use ($localidadId) {
                $q->where('localidade_id', $localidadId);
            });
        }

        // Ordenar por fecha de creación descendente (más reciente primero)
        $pagos = $query->orderBy('created_at', 'desc')
            ->orderBy('recibo', 'desc')
            ->paginate(20)
            ->appends([
                'search' => $search,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'tipo_pago' => $tipoPago,
                'localidad_id' => $localidadId
            ]);

        // Obtener datos para filtros
        $localidades = Localidade::all();
        $tiposPago = [
            'efectivo' => 'Efectivo',
            'qr' => 'QR/Transferencia',
            'parcial' => 'Pago Parcial'
        ];

        // Calcular totales
        $totalPagado = $pagos->sum('pago_bs');
        $totalDescuento = $pagos->sum('descuento_bs');
        $totalGeneral = $totalPagado + $totalDescuento;

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.recibos.partials.table-body', compact('pagos'))->render(),
                'pagination' => $pagos->links('pagination::bootstrap-5')->toHtml(),
                'total' => $pagos->total(),
                'totales' => [
                    'pagado' => number_format($totalPagado, 2),
                    'descuento' => number_format($totalDescuento, 2),
                    'general' => number_format($totalGeneral, 2)
                ]
            ]);
        }

        return view('admin.recibos.listar', compact(
            'pagos',
            'localidades',
            'tiposPago',
            'totalPagado',
            'totalDescuento',
            'totalGeneral'
        ));
    }

    public function obtenerDetallesPago(Request $request)
    {
        $request->validate([
            'pago_id' => 'required|exists:pagos,id'
        ]);

        try {
            $pago = Pago::with([
                'detalles',
                'cliente',
                'cliente.localidad',
                'pedidos' => function ($query) {
                    $query->withPivot('monto_asignado', 'descuento_asignado');
                }
            ])->findOrFail($request->pago_id);

            // Calcular totales desde los datos de la tabla intermedia
            $totalMontoAsignado = $pago->pedidos->sum(function ($pedido) {
                return $pedido->pivot->monto_asignado;
            });

            $totalDescuentoAsignado = $pago->pedidos->sum(function ($pedido) {
                return $pedido->pivot->descuento_asignado;
            });

            // Devolver el HTML para el modal con los datos adicionales
            return response()->json([
                'success' => true,
                'html' => view('admin.recibos.partials.detalles-pago', [
                    'pago' => $pago,
                    'totalMontoAsignado' => $totalMontoAsignado,
                    'totalDescuentoAsignado' => $totalDescuentoAsignado
                ])->render()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al obtener detalles del pago: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'html' => '<div class="alert alert-danger">Error al cargar los detalles del pago: ' . $e->getMessage() . '</div>'
            ], 500);
        }
    }

    public function reporteCobrosDiarios(Request $request)
    {
        // Filtros por defecto
        $fecha = $request->get('fecha', date('Y-m-d'));
        $usuarioId = $request->get('usuario_id', '');
        $tipoPago = $request->get('tipo_pago', '');

        // Consulta para obtener todos los pagos del día
        $query = Pago::with([
            'usuario',
            'cliente',
            'detalles',
            'pedidos' => function ($query) {
                $query->withPivot('monto_asignado', 'descuento_asignado');
            }
        ])->whereDate('created_at', $fecha);

        // Aplicar filtros
        if ($usuarioId) {
            $query->where('user_id', $usuarioId);
        }

        if ($tipoPago) {
            $query->where('tipo', $tipoPago);
        }

        // Obtener pagos ordenados por usuario
        $pagos = $query->orderBy('user_id')->orderBy('created_at', 'desc')->get();

        // Agrupar por usuario
        $cobrosPorUsuario = [];
        $totalesGenerales = [
            'efectivo' => 0,
            'qr' => 0,
            'parcial_efectivo' => 0,
            'parcial_qr' => 0,
            'descuento' => 0,
            'total' => 0
        ];

        foreach ($pagos as $pago) {
            $usuarioId = $pago->user_id;
            $usuarioNombre = $pago->usuario->name ?? 'Sin usuario';

            if (!isset($cobrosPorUsuario[$usuarioId])) {
                $cobrosPorUsuario[$usuarioId] = [
                    'usuario' => $pago->usuario,
                    'nombre_completo' => $usuarioNombre,
                    'efectivo' => 0,
                    'qr' => 0,
                    'parcial_efectivo' => 0,
                    'parcial_qr' => 0,
                    'descuento' => 0,
                    'total' => 0,
                    'cantidad_recibos' => 0,
                    'detalles' => []
                ];
            }

            // Sumar según el tipo de pago
            $cobrosPorUsuario[$usuarioId]['cantidad_recibos']++;

            if ($pago->tipo === 'efectivo') {
                $cobrosPorUsuario[$usuarioId]['efectivo'] += $pago->pago_bs;
                $totalesGenerales['efectivo'] += $pago->pago_bs;
            } elseif ($pago->tipo === 'qr') {
                $cobrosPorUsuario[$usuarioId]['qr'] += $pago->pago_bs;
                $totalesGenerales['qr'] += $pago->pago_bs;
            } elseif ($pago->tipo === 'parcial') {
                // Para pagos parciales, sumar detalles por tipo
                foreach ($pago->detalles as $detalle) {
                    if ($detalle->tipo === 'efectivo') {
                        $cobrosPorUsuario[$usuarioId]['parcial_efectivo'] += $detalle->pago_bs;
                        $totalesGenerales['parcial_efectivo'] += $detalle->pago_bs;
                    } elseif ($detalle->tipo === 'qr') {
                        $cobrosPorUsuario[$usuarioId]['parcial_qr'] += $detalle->pago_bs;
                        $totalesGenerales['parcial_qr'] += $detalle->pago_bs;
                    }
                }
            }

            // Sumar descuentos
            $cobrosPorUsuario[$usuarioId]['descuento'] += $pago->descuento_bs;
            $totalesGenerales['descuento'] += $pago->descuento_bs;

            // Calcular total por usuario
            $totalUsuario =
                $cobrosPorUsuario[$usuarioId]['efectivo'] +
                $cobrosPorUsuario[$usuarioId]['qr'] +
                $cobrosPorUsuario[$usuarioId]['parcial_efectivo'] +
                $cobrosPorUsuario[$usuarioId]['parcial_qr'];

            $cobrosPorUsuario[$usuarioId]['total'] = $totalUsuario;

            // Agregar detalle del pago
            $cobrosPorUsuario[$usuarioId]['detalles'][] = $pago;
        }

        // Calcular total general
        $totalesGenerales['total'] =
            $totalesGenerales['efectivo'] +
            $totalesGenerales['qr'] +
            $totalesGenerales['parcial_efectivo'] +
            $totalesGenerales['parcial_qr'];

        // ✅ CORRECCIÓN: Obtener TODOS los usuarios para el filtro, no solo los que tienen pagos en la fecha
        $usuarios = User::orderBy('name')->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.reportes.partials.cobros-diarios-table', compact(
                    'cobrosPorUsuario',
                    'totalesGenerales'
                ))->render(),
                'totales' => $totalesGenerales
            ]);
        }

        return view('admin.reportes.cobros-diarios', compact(
            'cobrosPorUsuario',
            'usuarios',
            'totalesGenerales',
            'fecha',
            'usuarioId',
            'tipoPago'
        ));
    }

    public function exportarCobrosDiariosExcel(Request $request)
    {
        $fecha = $request->get('fecha') ?: date('Y-m-d');
        $usuarioId = $request->get('usuario_id', '');
        $tipoPago = $request->get('tipo_pago', '');

        [$cobrosPorUsuario, $totalesGenerales] = $this->calcularCobrosDiarios($fecha, $usuarioId, $tipoPago);

        $usuarioFiltro = 'Todos';
        if ($usuarioId) {
            $usuarioFiltro = User::find($usuarioId)->name ?? 'Usuario';
        }

        $tipoFiltro = $tipoPago ? ucfirst($tipoPago) : 'Todos';

        $filename = 'cobros-diarios-' . $fecha;
        if ($usuarioId) {
            $filename .= '-usuario-' . $usuarioId;
        }
        if ($tipoPago) {
            $filename .= '-' . $tipoPago;
        }
        $filename .= '.xlsx';

        return Excel::download(
            new CobrosDiariosExport(
                $cobrosPorUsuario,
                $totalesGenerales,
                $fecha,
                $usuarioFiltro,
                $tipoFiltro
            ),
            $filename
        );
    }

    private function calcularCobrosDiarios(string $fecha, $usuarioId, $tipoPago): array
    {
        $query = Pago::with([
            'usuario',
            'cliente',
            'detalles',
            'pedidos' => function ($query) {
                $query->withPivot('monto_asignado', 'descuento_asignado');
            }
        ])->whereDate('created_at', $fecha);

        if ($usuarioId) {
            $query->where('user_id', $usuarioId);
        }

        if ($tipoPago) {
            $query->where('tipo', $tipoPago);
        }

        $pagos = $query->orderBy('user_id')->orderBy('created_at', 'desc')->get();

        $cobrosPorUsuario = [];
        $totalesGenerales = [
            'efectivo' => 0,
            'qr' => 0,
            'parcial_efectivo' => 0,
            'parcial_qr' => 0,
            'descuento' => 0,
            'total' => 0
        ];

        foreach ($pagos as $pago) {
            $usuarioPagoId = $pago->user_id;
            $usuarioNombre = $pago->usuario->name ?? 'Sin usuario';

            if (!isset($cobrosPorUsuario[$usuarioPagoId])) {
                $cobrosPorUsuario[$usuarioPagoId] = [
                    'usuario' => $pago->usuario,
                    'nombre_completo' => $usuarioNombre,
                    'efectivo' => 0,
                    'qr' => 0,
                    'parcial_efectivo' => 0,
                    'parcial_qr' => 0,
                    'descuento' => 0,
                    'total' => 0,
                    'cantidad_recibos' => 0,
                    'detalles' => []
                ];
            }

            $cobrosPorUsuario[$usuarioPagoId]['cantidad_recibos']++;

            $efectivo = 0;
            $qr = 0;
            $parcialEfectivo = 0;
            $parcialQr = 0;

            if ($pago->tipo === 'efectivo') {
                $efectivo = (float) $pago->pago_bs;
                $cobrosPorUsuario[$usuarioPagoId]['efectivo'] += $efectivo;
                $totalesGenerales['efectivo'] += $efectivo;
            } elseif ($pago->tipo === 'qr') {
                $qr = (float) $pago->pago_bs;
                $cobrosPorUsuario[$usuarioPagoId]['qr'] += $qr;
                $totalesGenerales['qr'] += $qr;
            } elseif ($pago->tipo === 'parcial') {
                foreach ($pago->detalles as $detalle) {
                    if ($detalle->tipo === 'efectivo') {
                        $parcialEfectivo += (float) $detalle->pago_bs;
                    } elseif ($detalle->tipo === 'qr') {
                        $parcialQr += (float) $detalle->pago_bs;
                    }
                }

                $cobrosPorUsuario[$usuarioPagoId]['parcial_efectivo'] += $parcialEfectivo;
                $cobrosPorUsuario[$usuarioPagoId]['parcial_qr'] += $parcialQr;
                $totalesGenerales['parcial_efectivo'] += $parcialEfectivo;
                $totalesGenerales['parcial_qr'] += $parcialQr;
            }

            $descuento = (float) $pago->descuento_bs;
            $cobrosPorUsuario[$usuarioPagoId]['descuento'] += $descuento;
            $totalesGenerales['descuento'] += $descuento;

            $cobrosPorUsuario[$usuarioPagoId]['total'] =
                $cobrosPorUsuario[$usuarioPagoId]['efectivo'] +
                $cobrosPorUsuario[$usuarioPagoId]['qr'] +
                $cobrosPorUsuario[$usuarioPagoId]['parcial_efectivo'] +
                $cobrosPorUsuario[$usuarioPagoId]['parcial_qr'];

            $cobrosPorUsuario[$usuarioPagoId]['detalles'][] = [
                'recibo' => $pago->recibo,
                'cliente' => trim(($pago->cliente->nombres ?? '') . ' ' . ($pago->cliente->apellidos ?? '')),
                'ci' => $pago->cliente->ci ?? null,
                'hora' => $pago->created_at ? $pago->created_at->format('H:i') : null,
                'tipo' => $pago->tipo,
                'efectivo' => $efectivo,
                'qr' => $qr,
                'parcial_efectivo' => $parcialEfectivo,
                'parcial_qr' => $parcialQr,
                'descuento' => $descuento,
                'total' => $efectivo + $qr + $parcialEfectivo + $parcialQr,
            ];
        }

        $totalesGenerales['total'] =
            $totalesGenerales['efectivo'] +
            $totalesGenerales['qr'] +
            $totalesGenerales['parcial_efectivo'] +
            $totalesGenerales['parcial_qr'];

        return [$cobrosPorUsuario, $totalesGenerales];
    }

    public function detallesCobrosUsuario(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'fecha' => 'required|date'
        ]);

        try {
            $usuario = User::findOrFail($request->usuario_id);
            $fecha = $request->fecha;

            // Obtener pagos del usuario en la fecha especificada
            $pagos = Pago::with([
                'cliente',
                'detalles',
                'pedidos' => function ($query) {
                    $query->withPivot('monto_asignado', 'descuento_asignado');
                }
            ])->where('user_id', $request->usuario_id)
                ->whereDate('created_at', $fecha)
                ->orderBy('created_at', 'desc')
                ->get();

            // Calcular totales
            $totales = [
                'efectivo' => 0,
                'qr' => 0,
                'parcial_efectivo' => 0,
                'parcial_qr' => 0,
                'descuento' => 0,
                'total' => 0
            ];

            foreach ($pagos as $pago) {
                if ($pago->tipo === 'efectivo') {
                    $totales['efectivo'] += $pago->pago_bs;
                } elseif ($pago->tipo === 'qr') {
                    $totales['qr'] += $pago->pago_bs;
                } elseif ($pago->tipo === 'parcial') {
                    foreach ($pago->detalles as $detalle) {
                        if ($detalle->tipo === 'efectivo') {
                            $totales['parcial_efectivo'] += $detalle->pago_bs;
                        } elseif ($detalle->tipo === 'qr') {
                            $totales['parcial_qr'] += $detalle->pago_bs;
                        }
                    }
                }
                $totales['descuento'] += $pago->descuento_bs;
            }

            $totales['total'] =
                $totales['efectivo'] +
                $totales['qr'] +
                $totales['parcial_efectivo'] +
                $totales['parcial_qr'];

            return response()->json([
                'success' => true,
                'html' => view('admin.reportes.partials.detalles-cobros-usuario', compact(
                    'usuario',
                    'pagos',
                    'fecha',
                    'totales'
                ))->render()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al obtener detalles de cobros por usuario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'html' => '<div class="alert alert-danger">Error al cargar los detalles: ' . $e->getMessage() . '</div>'
            ], 500);
        }
    }

    public function graficosIngresos(Request $request)
    {
        // Filtros por defecto: mes y año actual
        $mes = $request->get('mes', date('m'));
        $ano = $request->get('ano', date('Y'));

        // Obtener años disponibles (desde el año del primer pago hasta el actual)
        $primerPago = Pago::orderBy('created_at')->first();
        $anoInicio = $primerPago ? $primerPago->created_at->year : date('Y');
        $anos = range($anoInicio, date('Y'));

        // Obtener usuarios para el filtro
        $usuarios = User::whereHas('pagos')->get();

        return view('admin.reportes.graficos-ingresos', compact(
            'mes',
            'ano',
            'anos',
            'usuarios'
        ));
    }

    public function datosGraficosIngresos(Request $request)
    {
        $request->validate([
            'mes' => 'required|integer|between:1,12',
            'ano' => 'required|integer|min:2020',
            'tipo_grafico' => 'nullable|in:diario,acumulado'
        ]);

        $mes = $request->mes;
        $ano = $request->ano;
        $tipoGrafico = $request->tipo_grafico ?? 'diario';

        // Determinar los días del mes
        $diasEnMes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);

        // Consulta para obtener los pagos del mes y año seleccionados
        $pagos = Pago::with(['detalles'])
            ->whereYear('created_at', $ano)
            ->whereMonth('created_at', $mes)
            ->get();

        // Inicializar arrays para los datos
        $ingresosDiarios = [];
        $ingresosAcumulados = [];
        $acumulado = 0;

        // Inicializar todos los días del mes
        for ($dia = 1; $dia <= $diasEnMes; $dia++) {
            $ingresosDiarios[$dia] = 0;
            $ingresosAcumulados[$dia] = 0;
        }

        // Procesar cada pago
        foreach ($pagos as $pago) {
            $dia = (int) $pago->created_at->format('d');
            $montoPago = $pago->pago_bs;

            // Sumar al día correspondiente
            $ingresosDiarios[$dia] += $montoPago;
        }

        // Calcular acumulado
        for ($dia = 1; $dia <= $diasEnMes; $dia++) {
            $acumulado += $ingresosDiarios[$dia];
            $ingresosAcumulados[$dia] = $acumulado;
        }

        // Preparar datos para el gráfico
        $labels = [];
        $datasets = [];

        for ($dia = 1; $dia <= $diasEnMes; $dia++) {
            $labels[] = $dia;
        }

        if ($tipoGrafico === 'diario') {
            $datasets[] = [
                'label' => 'Ingresos Diarios (Bs)',
                'data' => array_values($ingresosDiarios),
                'borderColor' => '#14503c',
                'backgroundColor' => 'rgba(20, 80, 60, 0.16)',
                'borderWidth' => 2,
                'fill' => true,
                'tension' => 0.4
            ];
        } else {
            $datasets[] = [
                'label' => 'Ingresos Acumulados (Bs)',
                'data' => array_values($ingresosAcumulados),
                'borderColor' => '#e3a323',
                'backgroundColor' => 'rgba(227, 163, 35, 0.18)',
                'borderWidth' => 2,
                'fill' => true,
                'tension' => 0.4
            ];
        }

        // Calcular estadísticas
        $totalMes = array_sum($ingresosDiarios);
        $diasConIngresos = count(array_filter($ingresosDiarios));
        $promedioDiario = $diasConIngresos > 0 ? $totalMes / $diasConIngresos : 0;
        $diaMaximo = array_keys($ingresosDiarios, max($ingresosDiarios))[0] + 1;
        $diaMinimo = array_keys($ingresosDiarios, min(array_filter($ingresosDiarios)))[0] + 1;

        return response()->json([
            'success' => true,
            'labels' => $labels,
            'datasets' => $datasets,
            'estadisticas' => [
                'total_mes' => $totalMes,
                'promedio_diario' => $promedioDiario,
                'dias_con_ingresos' => $diasConIngresos,
                'dia_maximo' => [
                    'dia' => $diaMaximo,
                    'monto' => max($ingresosDiarios)
                ],
                'dia_minimo' => [
                    'dia' => $diaMinimo,
                    'monto' => min(array_filter($ingresosDiarios))
                ]
            ]
        ]);
    }

    public function datosGraficosIngresosPorUsuario(Request $request)
    {
        $request->validate([
            'mes' => 'required|integer|between:1,12',
            'ano' => 'required|integer|min:2020',
            'usuario_id' => 'nullable|exists:users,id'
        ]);

        $mes = $request->mes;
        $ano = $request->ano;
        $usuarioId = $request->usuario_id;

        // Determinar los días del mes
        $diasEnMes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);

        // Consulta para obtener los pagos del mes y año seleccionados
        $query = Pago::with(['usuario', 'detalles'])
            ->whereYear('created_at', $ano)
            ->whereMonth('created_at', $mes);

        if ($usuarioId) {
            $query->where('user_id', $usuarioId);
        }

        $pagos = $query->get();

        // Agrupar por usuario
        $usuariosData = [];
        $labels = [];

        // Preparar labels (días del mes)
        for ($dia = 1; $dia <= $diasEnMes; $dia++) {
            $labels[] = $dia;
        }

        // Inicializar datos por usuario
        $usuarios = User::whereHas('pagos', function ($query) use ($mes, $ano) {
            $query->whereYear('created_at', $ano)->whereMonth('created_at', $mes);
        });

        if ($usuarioId) {
            $usuarios = $usuarios->where('id', $usuarioId);
        }

        $usuarios = $usuarios->get();

        foreach ($usuarios as $usuario) {
            $usuariosData[$usuario->id] = [
                'nombre' => $usuario->name,
                'datos' => array_fill(1, $diasEnMes, 0),
                'total' => 0
            ];
        }

        // Procesar cada pago
        foreach ($pagos as $pago) {
            $dia = (int) $pago->created_at->format('d');
            $usuarioId = $pago->user_id;
            $montoPago = $pago->pago_bs;

            if (isset($usuariosData[$usuarioId])) {
                $usuariosData[$usuarioId]['datos'][$dia] += $montoPago;
                $usuariosData[$usuarioId]['total'] += $montoPago;
            }
        }

        // Preparar datasets para el gráfico
        $datasets = [];
        $colores = [
            '#14503c',
            '#e3a323',
            '#2b7a78',
            '#b4483d',
            '#5b4b8a',
            '#2a7a5a',
            '#c77d1f',
            '#4f6d7a',
            '#8c5a3c',
            '#3f7f5f'
        ];

        $colorIndex = 0;
        foreach ($usuariosData as $usuarioId => $data) {
            $datasets[] = [
                'label' => $data['nombre'] . ' (Total: Bs. ' . number_format($data['total'], 2) . ')',
                'data' => array_values($data['datos']),
                'borderColor' => $colores[$colorIndex % count($colores)],
                'backgroundColor' => str_replace(')', ', 0.1)', $colores[$colorIndex % count($colores)]),
                'borderWidth' => 2,
                'fill' => false,
                'tension' => 0.4
            ];
            $colorIndex++;
        }

        return response()->json([
            'success' => true,
            'labels' => $labels,
            'datasets' => $datasets,
            'total_usuarios' => count($usuariosData),
            'total_general' => array_sum(array_column($usuariosData, 'total'))
        ]);
    }

    public function controlIngresos(Request $request)
    {
        // Fechas por defecto: hoy
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', Carbon::now()->format('Y-m-d'));

        // Obtener reporte
        $reporte = $this->obtenerReporteIngresos($fechaInicio, $fechaFin);

        // Obtener totales generales
        $totalesGenerales = $this->calcularTotalesGenerales($reporte);

        // Si es una petición AJAX, devolver JSON
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.pedidos.partials.control-ingresos-table', compact(
                    'reporte',
                    'totalesGenerales',
                    'fechaInicio',
                    'fechaFin'
                ))->render(),
                'totalesGenerales' => $totalesGenerales
            ]);
        }

        return view('admin.pedidos.control-ingresos', compact(
            'reporte',
            'fechaInicio',
            'fechaFin',
            'totalesGenerales'
        ));
    }

    private function obtenerReporteIngresos($fechaInicio, $fechaFin)
    {
        $inicio = Carbon::parse($fechaInicio)->startOfDay();
        $fin = Carbon::parse($fechaFin)->endOfDay();

        // Obtener usuarios que han realizado cobros en el período
        $usuarios = User::whereHas('pagos', function ($query) use ($inicio, $fin) {
            $query->whereBetween('created_at', [$inicio, $fin]);
        })->get();

        $reporte = [];

        foreach ($usuarios as $usuario) {
            // Obtener pagos del usuario en el período
            $pagos = Pago::with('detalles')
                ->where('user_id', $usuario->id)
                ->whereBetween('created_at', [$inicio, $fin])
                ->get();

            $totalEfectivo = 0;
            $totalQr = 0;
            $pagosDetalle = [];

            foreach ($pagos as $pago) {
                $montoEfectivo = 0;
                $montoQr = 0;

                if ($pago->tipo === 'parcial') {
                    // Sumar detalles por tipo
                    foreach ($pago->detalles as $detalle) {
                        if ($detalle->tipo === 'efectivo') {
                            $montoEfectivo += $detalle->pago_bs;
                        } elseif ($detalle->tipo === 'qr') {
                            $montoQr += $detalle->pago_bs;
                        }
                    }
                } else {
                    // Pago simple
                    if ($pago->tipo === 'efectivo') {
                        $montoEfectivo += $pago->pago_bs;
                    } elseif ($pago->tipo === 'qr') {
                        $montoQr += $pago->pago_bs;
                    }
                }

                $totalEfectivo += $montoEfectivo;
                $totalQr += $montoQr;

                // Guardar detalle del pago
                $pagosDetalle[] = [
                    'recibo' => $pago->recibo,
                    'fecha' => $pago->created_at->format('d/m/Y H:i'),
                    'cliente' => $pago->cliente ? $pago->cliente->nombres . ' ' . $pago->cliente->apellidos : 'N/A',
                    'efectivo' => $montoEfectivo,
                    'qr' => $montoQr,
                    'total' => $montoEfectivo + $montoQr,
                    'tipo' => $pago->tipo
                ];
            }

            $reporte[] = [
                'usuario' => [
                    'id' => $usuario->id,
                    'name' => $usuario->name,
                    'email' => $usuario->email
                ],
                'total_efectivo' => $totalEfectivo,
                'total_qr' => $totalQr,
                'total_general' => $totalEfectivo + $totalQr,
                'pagos_detalle' => $pagosDetalle,
                'cantidad_pagos' => count($pagosDetalle)
            ];
        }

        // Ordenar por total general descendente
        usort($reporte, function ($a, $b) {
            return $b['total_general'] <=> $a['total_general'];
        });

        return $reporte;
    }

    private function calcularTotalesGenerales($reporte)
    {
        $totalEfectivo = 0;
        $totalQr = 0;
        $totalGeneral = 0;
        $totalPagos = 0;

        foreach ($reporte as $item) {
            $totalEfectivo += $item['total_efectivo'];
            $totalQr += $item['total_qr'];
            $totalGeneral += $item['total_general'];
            $totalPagos += $item['cantidad_pagos'];
        }

        return [
            'total_efectivo' => $totalEfectivo,
            'total_qr' => $totalQr,
            'total_general' => $totalGeneral,
            'total_pagos' => $totalPagos,
            'cantidad_usuarios' => count($reporte)
        ];
    }

    public function exportarControlIngresosPdf(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date'
        ]);

        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        $reporte = $this->obtenerReporteIngresos($fechaInicio, $fechaFin);
        $totalesGenerales = $this->calcularTotalesGenerales($reporte);

        $pdf = Pdf::loadView('admin.pedidos.reportes.control-ingresos-pdf', compact(
            'reporte',
            'fechaInicio',
            'fechaFin',
            'totalesGenerales'
        ));

        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);

        return $pdf->download('control-ingresos-' . $fechaInicio . '-al-' . $fechaFin . '.pdf');
    }

    public function exportarControlIngresosExcel(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date'
        ]);

        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        $reporte = $this->obtenerReporteIngresos($fechaInicio, $fechaFin);
        $totalesGenerales = $this->calcularTotalesGenerales($reporte);

        return Excel::download(
            new ControlIngresosExport($reporte, $fechaInicio, $fechaFin, $totalesGenerales),
            'control-ingresos-' . $fechaInicio . '-al-' . $fechaFin . '.xlsx'
        );
    }

    public function exportarRecibosExcel(Request $request)
    {
        $search = $request->get('search', '');
        $fechaInicio = $request->get('fecha_inicio', '');
        $fechaFin = $request->get('fecha_fin', '');
        $tipoPago = $request->get('tipo_pago', '');
        $localidadId = $request->get('localidad_id', '');

        $query = Pago::with(['cliente', 'cliente.localidad', 'usuario']);

        // Aplicar los mismos filtros que en listarRecibos
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('recibo', 'like', "%{$search}%")
                    ->orWhereHas('cliente', function ($q) use ($search) {
                        $q->where('ci', 'like', "%{$search}%")
                            ->orWhere('nombres', 'like', "%{$search}%")
                            ->orWhere('apellidos', 'like', "%{$search}%");
                    });
            });
        }

        if ($fechaInicio) {
            $query->whereDate('created_at', '>=', $fechaInicio);
        }

        if ($fechaFin) {
            $query->whereDate('created_at', '<=', $fechaFin);
        }

        if ($tipoPago) {
            $query->where('tipo', $tipoPago);
        }

        if ($localidadId) {
            $query->whereHas('cliente', function ($q) use ($localidadId) {
                $q->where('localidade_id', $localidadId);
            });
        }

        $pagos = $query->orderBy('created_at', 'desc')->get();

        return Excel::download(new RecibosExport($pagos), 'recibos-' . date('Y-m-d') . '.xlsx');
    }
}
