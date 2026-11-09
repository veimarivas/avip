<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Detalle;
use App\Models\Localidade;
use App\Models\Pago;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class ClientesController extends Controller
{
    public function clientesListar(Request $request)
    {
        $search = $request->get('search', '');
        $localidadId = $request->get('localidad_id', '');

        // Consulta optimizada que calcula correctamente la deuda considerando pagos parciales
        $query = Cliente::with('localidad')
            ->select('clientes.*')
            ->selectRaw('(
            SELECT COALESCE(SUM(
                CASE 
                    WHEN pedidos.pagado = 0 
                    THEN GREATEST(subtotal - COALESCE(pagado_bs, 0) - COALESCE(descuento_bs, 0), 0)
                    ELSE 0 
                END
            ), 0)
            FROM pedidos
            WHERE pedidos.cliente_id = clientes.id
        ) as deuda_calculada');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ci', 'like', "%{$search}%")
                    ->orWhere('nombres', 'like', "%{$search}%")
                    ->orWhere('apellidos', 'like', "%{$search}%");
            });
        }

        if ($localidadId) {
            $query->where('localidade_id', $localidadId);
        }

        $clientes = $query->paginate(30)->appends([
            'search' => $search,
            'localidad_id' => $localidadId
        ]);

        // Obtener todas las localidades para las pestañas
        $localidades = Localidade::all();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.clientes.partials.table-body', compact('clientes'))->render(),
                'pagination' => $clientes->links('pagination::bootstrap-5')->toHtml(),
                'total' => $clientes->total(),
                'from' => $clientes->firstItem(),
                'to' => $clientes->lastItem()
            ]);
        }

        return view('admin.clientes.listar', compact('clientes', 'localidades'));
    }

    public function clientesPorLocalidad($localidadId, Request $request)
    {
        $search = $request->get('search', '');

        // Consulta que muestra todos los clientes de la localidad (sin paginación)
        $query = Cliente::with('localidad')
            ->select('clientes.*')
            ->selectRaw('(
            SELECT COALESCE(SUM(
                CASE 
                    WHEN pedidos.pagado = 0 
                    THEN GREATEST(subtotal - COALESCE(pagado_bs, 0) - COALESCE(descuento_bs, 0), 0)
                    ELSE 0 
                END
            ), 0)
            FROM pedidos
            WHERE pedidos.cliente_id = clientes.id
        ) as deuda_calculada')
            ->where('localidade_id', $localidadId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ci', 'like', "%{$search}%")
                    ->orWhere('nombres', 'like', "%{$search}%")
                    ->orWhere('apellidos', 'like', "%{$search}%");
            });
        }

        $clientes = $query->orderBy('orden', 'asc')->get();

        return response()->json([
            'html' => view('admin.clientes.partials.table-body', compact('clientes'))->render(),
            'total' => $clientes->count()
        ]);
    }

    public function obtenerDetallesDeuda($id)
    {
        try {
            $cliente = Cliente::with([
                'localidad',
                'pedidos' => function ($query) {
                    $query->where('pagado', 0)
                        ->whereRaw('(subtotal - IFNULL(pagado_bs, 0) - IFNULL(descuento_bs, 0)) > 0')
                        ->orderBy('created_at', 'asc');
                }
            ])->findOrFail($id);

            $detalles = [];
            $deudaTotal = 0;

            foreach ($cliente->pedidos as $pedido) {
                $pagadoBs = $pedido->pagado_bs ?? 0;
                $descuentoBs = $pedido->descuento_bs ?? 0;
                $pendiente = $pedido->subtotal - $pagadoBs - $descuentoBs;

                if ($pendiente > 0) {
                    $detalles[] = [
                        'pedido_id' => $pedido->id,
                        'codigo' => $pedido->codigo ?? 'N/A',
                        'fecha' => $pedido->created_at->format('d/m/Y'),
                        'subtotal' => $pedido->subtotal,
                        'pagado' => $pagadoBs,
                        'descuento' => $descuentoBs,
                        'pendiente' => $pendiente,
                        'dias_atraso' => now()->diffInDays($pedido->created_at)
                    ];
                    $deudaTotal += $pendiente;
                }
            }

            return response()->json([
                'success' => true,
                'html' => view('admin.clientes.partials.detalles-deuda-modal', [
                    'cliente' => $cliente,
                    'detalles' => $detalles,
                    'deudaTotal' => $deudaTotal
                ])->render()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al obtener detalles de deuda: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => 'Error al obtener detalles de deuda'
            ], 500);
        }
    }

    public function obtenerClientesPorLocalidad($localidadId)
    {
        try {
            $clientes = Cliente::with('localidad')
                ->where('localidade_id', $localidadId)
                ->orderBy('orden', 'asc')
                ->get(['id', 'ci', 'nombres', 'apellidos', 'orden']);

            return response()->json([
                'success' => true,
                'clientes' => $clientes
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al obtener clientes por localidad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => 'Error al obtener los clientes'
            ], 500);
        }
    }

    public function actualizarOrdenClientes(Request $request)
    {
        $request->validate([
            'localidad_id' => 'required|exists:localidades,id',
            'clientes' => 'required|array',
            'clientes.*.id' => 'required|exists:clientes,id',
            'clientes.*.orden' => 'required|integer|min:1'
        ]);

        try {
            \DB::beginTransaction();

            foreach ($request->clientes as $clienteData) {
                Cliente::where('id', $clienteData['id'])
                    ->where('localidade_id', $request->localidad_id)
                    ->update(['orden' => $clienteData['orden']]);
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'msg' => 'Orden de clientes actualizado correctamente.'
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error al actualizar orden de clientes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => 'Error al actualizar el orden: ' . $e->getMessage()
            ], 500);
        }
    }

    public function clientesRegistrar(Request $request)
    {
        $request->validate([
            'ci' => 'required|unique:clientes,ci',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'deuda' => 'nullable|numeric|min:0',
            'orden' => 'nullable|integer|min:0',
            'localidade_id' => 'required|exists:localidades,id'
        ]);

        $cliente = new Cliente();
        $cliente->ci = $request->ci;
        $cliente->nombres = $request->nombres;
        $cliente->apellidos = $request->apellidos;
        $cliente->deuda = $request->deuda ?? 0;
        $cliente->orden = $request->orden ?? 0;
        $cliente->localidade_id = $request->localidade_id;
        $cliente->save();

        return response()->json([
            'success' => true,
            'msg' => 'Cliente registrado correctamente.',
            'cliente' => $cliente
        ]);
    }

    public function verificarCi(Request $request)
    {
        $ci = $request->input('ci');
        $id = $request->input('id');

        $query = Cliente::where('ci', $ci);

        if ($id && is_numeric($id) && $id > 0) {
            $query->where('id', '!=', $id);
        }

        return response()->json(['exists' => $query->exists()]);
    }

    public function verificarCiEdicion(Request $request)
    {
        $ci = trim($request->input('ci'));
        $id = $request->input('id');

        $id = is_numeric($id) && $id > 0 ? (int) $id : null;

        $exists = Cliente::where('ci', $ci)
            ->when($id, function ($query) use ($id) {
                return $query->where('id', '!=', $id);
            })
            ->exists();

        return response()->json(['exists' => $exists]);
    }

    public function clientesModificar(Request $request)
    {
        \Log::info('Datos recibidos para modificar cliente:', $request->all());

        $request->validate([
            'id' => 'required|exists:clientes,id',
            'ci' => 'required|unique:clientes,ci,' . $request->id,
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'deuda' => 'nullable|numeric|min:0',
            'orden' => 'nullable|integer|min:0',
            'localidade_id' => 'required|exists:localidades,id'
        ]);

        try {
            $cliente = Cliente::findOrFail($request->id);

            $cliente->ci = $request->ci;
            $cliente->nombres = $request->nombres;
            $cliente->apellidos = $request->apellidos;
            $cliente->deuda = $request->deuda ?? 0;
            $cliente->orden = $request->orden ?? 0;
            $cliente->localidade_id = $request->localidade_id;
            $cliente->save();

            return response()->json([
                'success' => true,
                'msg' => 'Cliente actualizado correctamente.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al modificar cliente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => 'Error al actualizar el cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function clientesEliminar(Request $request)
    {
        \Log::info('Datos recibidos para eliminar cliente:', $request->all());

        $request->validate([
            'id' => 'required|exists:clientes,id'
        ]);

        try {
            $cliente = Cliente::findOrFail($request->id);

            // Verificar si hay relaciones antes de eliminar
            if (
                $cliente->pagos()->count() > 0 ||
                $cliente->pedidos()->count() > 0 ||
                $cliente->pedidos_productos()->count() > 0
            ) {
                return response()->json([
                    'success' => false,
                    'msg' => 'No se puede eliminar el cliente porque tiene registros asociados (pagos, pedidos, etc.).'
                ], 422);
            }

            $cliente->delete();

            return response()->json([
                'success' => true,
                'msg' => 'Cliente eliminado correctamente.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar cliente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => 'Error al eliminar el cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function clienteCambiarEstado(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:clientes,id',
            'habilitado' => 'required|boolean|in:0,1'
        ]);

        try {
            $cliente = Cliente::findOrFail($request->id);

            // Validar al deshabilitar: deuda debe ser 0
            if ($request->habilitado == 0) {
                $deuda = $cliente->pedidos()
                    ->where('pagado', 0)
                    ->selectRaw('COALESCE(SUM(
                        CASE
                            WHEN (subtotal - IFNULL(pagado_bs, 0) - IFNULL(descuento_bs, 0)) > 0
                            THEN (subtotal - IFNULL(pagado_bs, 0) - IFNULL(descuento_bs, 0))
                            ELSE 0
                        END
                    ), 0) as deuda')
                    ->value('deuda');

                $deuda = floatval($deuda);

                if ($deuda > 0) {
                    return response()->json([
                        'success' => false,
                        'msg' => 'No se puede deshabilitar el cliente porque tiene deuda pendiente de Bs. ' . number_format($deuda, 2)
                    ], 422);
                }
            }

            $cliente->habilitado = $request->habilitado;
            $cliente->save();

            $msg = $request->habilitado == 1
                ? 'Cliente habilitado correctamente.'
                : 'Cliente deshabilitado correctamente.';

            return response()->json([
                'success' => true,
                'msg' => $msg
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al cambiar estado del cliente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => 'Error al cambiar el estado del cliente.'
            ], 500);
        }
    }

    public function obtenerClientesInactivos($localidadId)
    {
        try {
            $clientes = Cliente::where('localidade_id', $localidadId)
                ->where('habilitado', 0)
                ->orderBy('orden', 'asc')
                ->get(['id', 'ci', 'nombres', 'apellidos', 'orden']);

            return response()->json([
                'success' => true,
                'clientes' => $clientes
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al obtener clientes inactivos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => 'Error al obtener los clientes inactivos'
            ], 500);
        }
    }

    public function clientesVer($id)
    {
        $cliente = Cliente::with([
            'localidad',
            'pedidos' => function ($query) {
                $query->with(['pedidos_productos'])
                    ->orderBy('created_at', 'desc');
            },
            'pagos' => function ($query) {
                $query->with(['detalles', 'pedidos.pedidos_productos'])
                    ->orderBy('created_at', 'desc');
            }
        ])->findOrFail($id);

        // Separar pedidos pendientes y pagados
        $pedidosPendientes = $cliente->pedidos->filter(function ($pedido) {
            $pagadoBs = $pedido->pagado_bs ?? 0;
            $descuentoBs = $pedido->descuento_bs ?? 0;
            $pendiente = $pedido->subtotal - $pagadoBs - $descuentoBs;
            return $pendiente > 0 && $pedido->pagado == 0;
        });

        $pedidosPagados = $cliente->pedidos->filter(function ($pedido) {
            $pagadoBs = $pedido->pagado_bs ?? 0;
            $descuentoBs = $pedido->descuento_bs ?? 0;
            $pendiente = $pedido->subtotal - $pagadoBs - $descuentoBs;
            return  $pedido->pagado == 1;
        });

        // Calcular deuda total
        $deudaTotal = $pedidosPendientes->sum(function ($pedido) {
            $pagadoBs = $pedido->pagado_bs ?? 0;
            $descuentoBs = $pedido->descuento_bs ?? 0;
            return $pedido->subtotal - $pagadoBs - $descuentoBs;
        });

        // Obtener pagos ordenados descendente por recibo
        $pagos = $cliente->pagos()->with('detalles')
            ->orderBy('created_at', 'desc')
            ->orderBy('recibo', 'desc')
            ->get();

        return view('admin.clientes.ver', compact(
            'cliente',
            'deudaTotal',
            'pedidosPendientes',
            'pedidosPagados',
            'pagos'
        ));
    }

    public function rankingDeudas(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $localidadId = $request->get('localidad_id', '');
            $limit = $request->get('limit', 50);

            // Consulta base
            $query = Cliente::with('localidad');

            // Aplicar filtros
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('ci', 'like', "%{$search}%")
                        ->orWhere('nombres', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%");
                });
            }

            if ($localidadId) {
                $query->where('localidade_id', $localidadId);
            }

            // Obtener todos los clientes que cumplen con los filtros
            $clientes = $query->get();

            // Calcular deuda para cada cliente
            $clientesConDeuda = [];
            foreach ($clientes as $cliente) {
                $deuda = $cliente->pedidos()
                    ->where('pagado', 0)
                    ->selectRaw('COALESCE(SUM(
                    CASE 
                        WHEN (subtotal - IFNULL(pagado_bs, 0) - IFNULL(descuento_bs, 0)) > 0 
                        THEN (subtotal - IFNULL(pagado_bs, 0) - IFNULL(descuento_bs, 0))
                        ELSE 0 
                    END
                ), 0) as deuda')
                    ->value('deuda');

                $deuda = floatval($deuda);

                if ($deuda > 0) {
                    $cliente->deuda_calculada = $deuda;
                    $clientesConDeuda[] = $cliente;
                }
            }

            // Ordenar por deuda (mayor a menor)
            usort($clientesConDeuda, function ($a, $b) {
                return $b->deuda_calculada <=> $a->deuda_calculada;
            });

            // Paginar manualmente
            $page = $request->get('page', 1);
            $perPage = $limit;
            $offset = ($page - 1) * $perPage;
            $items = array_slice($clientesConDeuda, $offset, $perPage);

            $clientesPaginados = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                count($clientesConDeuda),
                $perPage,
                $page,
                [
                    'path' => $request->url(),
                    'query' => $request->query()
                ]
            );

            // Calcular estadísticas
            $totalDeuda = 0;
            foreach ($clientesConDeuda as $cliente) {
                $totalDeuda += $cliente->deuda_calculada;
            }

            $promedioDeuda = count($clientesConDeuda) > 0 ? $totalDeuda / count($clientesConDeuda) : 0;
            $clienteMayorDeuda = !empty($clientesConDeuda) ? $clientesConDeuda[0] : null;
            $localidades = Localidade::all();

            // En el método rankingDeudas, actualiza la sección de stats:
            if ($request->ajax()) {
                $mayorDeuda = !empty($clientesConDeuda) ? $clientesConDeuda[0]->deuda_calculada : 0;

                return response()->json([
                    'success' => true,
                    'html' => view('admin.clientes.partials.ranking-table-body', [
                        'clientes' => $clientesPaginados
                    ])->render(),
                    'pagination' => (string) $clientesPaginados->links('pagination::bootstrap-5'),
                    'total' => $clientesPaginados->total(),
                    'from' => $clientesPaginados->firstItem(),
                    'to' => $clientesPaginados->lastItem(),
                    'stats' => [
                        'total_deuda' => $totalDeuda,
                        'promedio_deuda' => number_format($promedioDeuda, 2),
                        'mayor_deuda' => $mayorDeuda,
                        'total_clientes' => $clientesPaginados->total()
                    ]
                ]);
            }

            // Para vista normal - renombrar variable para la vista
            $clientes = $clientesPaginados;

            return view('admin.clientes.ranking-deudas', compact(
                'clientes',
                'localidades',
                'totalDeuda',
                'promedioDeuda',
                'clienteMayorDeuda',
                'limit'
            ));
        } catch (\Exception $e) {
            \Log::error('Error en rankingDeudas: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());

            // En caso de error, devolver datos vacíos
            $clientes = collect();
            $localidades = Localidade::all();
            $totalDeuda = 0;
            $promedioDeuda = 0;
            $clienteMayorDeuda = null;
            $limit = 50;

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error al cargar el ranking',
                    'html' => '<tr><td colspan="7" class="text-center text-danger py-4">Error al cargar el ranking. Por favor, intente nuevamente.</td></tr>'
                ], 500);
            }

            return view('admin.clientes.ranking-deudas', compact(
                'clientes',
                'localidades',
                'totalDeuda',
                'promedioDeuda',
                'clienteMayorDeuda',
                'limit'
            ));
        }
    }

    public function realizarPagoConDescuento(Request $request)
    {
        \Log::info('=== INICIO realizarPagoConDescuento ===');
        \Log::info('Datos recibidos:', $request->all());
        \Log::info('Usuario autenticado:', auth()->user() ? ['id' => auth()->user()->id, 'name' => auth()->user()->name] : 'No autenticado');

        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'pedido_id' => 'required|exists:pedidos,id',
            'monto_pago' => 'required|numeric|min:0.01',
            'descuento_bs' => 'nullable|numeric|min:0',
            'tipo_pago' => 'required|in:efectivo,qr,parcial',
            'monto_efectivo' => 'nullable|numeric|min:0',
            'monto_qr' => 'nullable|numeric|min:0'
        ]);

        try {
            \DB::beginTransaction();

            $pedido = Pedido::where('id', $request->pedido_id)
                ->where('cliente_id', $request->cliente_id)
                ->firstOrFail();

            $montoPago = $request->monto_pago;
            $descuento = $request->descuento_bs ?? 0;
            $subtotal = $pedido->subtotal;

            // Obtener valores actuales
            $pagadoActual = $pedido->pagado_bs ?? 0;
            $descuentoActual = $pedido->descuento_bs ?? 0;

            // Calcular pendiente actual
            $pendienteActual = $subtotal - $pagadoActual - $descuentoActual;

            // Validar que el monto a pagar no sea mayor al pendiente
            if ($montoPago > $pendienteActual) {
                return response()->json([
                    'success' => false,
                    'msg' => 'El monto a pagar no puede ser mayor al pendiente actual (Bs. ' . number_format($pendienteActual, 2) . ')'
                ], 422);
            }

            // Calcular nuevos valores
            $nuevoPagadoBs = $pagadoActual + $montoPago;
            $nuevoDescuentoBs = $descuentoActual + $descuento;
            $nuevoPendiente = $subtotal - $nuevoPagadoBs - $nuevoDescuentoBs;

            // Verificar que no se pague más del subtotal
            if ($nuevoPagadoBs + $nuevoDescuentoBs > $subtotal) {
                return response()->json([
                    'success' => false,
                    'msg' => 'El monto pagado más el descuento no puede ser mayor al total del pedido.'
                ], 422);
            }

            // Actualizar el pedido
            $pedido->pagado_bs = $nuevoPagadoBs;
            $pedido->descuento_bs = $nuevoDescuentoBs;
            $pedido->pendiente = $nuevoPendiente;

            // Si el pendiente es 0 o menos, marcar como pagado
            if ($nuevoPendiente <= 0) {
                $pedido->pagado = 1;
                $pedido->pendiente = 0;
            } else {
                $pedido->pagado = 0;
            }

            $pedido->save();

            // Determinar montos para el pago parcial
            $montoEfectivo = 0;
            $montoQr = 0;

            if ($request->tipo_pago === 'parcial') {
                $montoEfectivo = $request->monto_efectivo ?? 0;
                $montoQr = $request->monto_qr ?? 0;

                // Validar que la suma sea igual al monto a pagar
                if (($montoEfectivo + $montoQr) != $montoPago) {
                    return response()->json([
                        'success' => false,
                        'msg' => 'La suma de los montos en efectivo y QR debe ser igual al monto a pagar.'
                    ], 422);
                }
            }

            // Registrar el pago (sin asociar pedido_id directamente)
            $pago = Pago::create([
                'cliente_id' => $request->cliente_id,
                'pago_bs' => $montoPago,
                'descuento_bs' => $descuento,
                'tipo' => $request->tipo_pago,
                'user_id' => auth()->user()->id ?? null,
            ]);

            // Asociar el pago con el pedido en la tabla intermedia
            $pago->pedidos()->attach($pedido->id, [
                'monto_asignado' => $montoPago,
                'descuento_asignado' => $descuento
            ]);

            // Registrar detalles según el tipo de pago
            if ($request->tipo_pago === 'parcial') {
                // Registrar detalle de efectivo
                if ($montoEfectivo > 0) {
                    Detalle::create([
                        'pago_id' => $pago->id,
                        'pago_bs' => $montoEfectivo,
                        'descuentos_bs' => 0,
                        'tipo' => 'efectivo'
                    ]);
                }

                // Registrar detalle de QR
                if ($montoQr > 0) {
                    Detalle::create([
                        'pago_id' => $pago->id,
                        'pago_bs' => $montoQr,
                        'descuentos_bs' => 0,
                        'tipo' => 'qr'
                    ]);
                }
            } else {
                // Para pagos no parciales, registrar un solo detalle
                Detalle::create([
                    'pago_id' => $pago->id,
                    'pago_bs' => $montoPago,
                    'descuentos_bs' => $descuento,
                    'tipo' => $request->tipo_pago
                ]);
            }

            // Actualizar la deuda del cliente
            $cliente = Cliente::find($request->cliente_id);
            if ($cliente) {
                $cliente->deuda = $cliente->pedidos()
                    ->where('pagado', 0)
                    ->where(function ($query) {
                        $query->whereRaw('(subtotal - IFNULL(pagado_bs, 0) - IFNULL(descuento_bs, 0)) > 0');
                    })
                    ->sum(\DB::raw('(subtotal - IFNULL(pagado_bs, 0) - IFNULL(descuento_bs, 0))'));
                $cliente->save();
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'msg' => 'Pago realizado correctamente. Recibo: ' . $pago->recibo,
                'recibo' => $pago->recibo,
                'recibo_url' => route('admin.recibos.ver', $pago->id),
                'nuevo_pendiente' => $pedido->pendiente,
                'esta_pagado' => $pedido->pagado,
                'deuda_actualizada' => $cliente->deuda ?? 0
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error al realizar pago con descuento: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'msg' => 'Error al realizar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    public function pagarPedidoCompleto(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'pedido_id' => 'required|exists:pedidos,id',
            'tipo_pago' => 'nullable|in:efectivo,qr,parcial',
            'monto_efectivo' => 'nullable|numeric|min:0',
            'monto_qr' => 'nullable|numeric|min:0'
        ]);

        try {
            \DB::beginTransaction();

            $pedido = Pedido::where('id', $request->pedido_id)
                ->where('cliente_id', $request->cliente_id)
                ->firstOrFail();

            $subtotal = $pedido->subtotal;
            $pagadoBs = $pedido->pagado_bs ?? 0;
            $descuentoBs = $pedido->descuento_bs ?? 0;
            $pendiente = $subtotal - $pagadoBs - $descuentoBs;

            // Calcular lo que falta pagar
            $montoFaltante = $pendiente;

            // Determinar tipo de pago
            $tipoPago = $request->tipo_pago ?? 'efectivo';
            $montoEfectivo = $request->monto_efectivo ?? $montoFaltante;
            $montoQr = $request->monto_qr ?? 0;

            // Si es parcial, validar que la suma sea igual al monto faltante
            if ($tipoPago === 'parcial') {
                if (($montoEfectivo + $montoQr) != $montoFaltante) {
                    return response()->json([
                        'success' => false,
                        'msg' => 'La suma de los montos en efectivo y QR debe ser igual al monto faltante.'
                    ], 422);
                }
            }

            // Actualizar el pedido
            $pedido->pagado_bs = $subtotal - $descuentoBs;
            $pedido->pendiente = 0;
            $pedido->pagado = 1;
            $pedido->save();

            // Registrar el pago por el monto faltante (el recibo se genera automáticamente)
            $pago = Pago::create([
                'cliente_id' => $request->cliente_id,
                'pedido_id' => $request->pedido_id,
                'pago_bs' => $montoFaltante,
                'descuento_bs' => 0,
                'tipo' => $tipoPago,
                'cobrador' => auth()->user()->name ?? 'Sistema',
                'user_id' => auth()->user()->id ?? null,
                'pago_total' => $montoFaltante
            ]);

            // Registrar detalles según el tipo de pago
            if ($tipoPago === 'parcial') {
                // Registrar detalle de efectivo
                if ($montoEfectivo > 0) {
                    Detalle::create([
                        'pago_id' => $pago->id,
                        'pago_bs' => $montoEfectivo,
                        'descuentos_bs' => 0,
                        'tipo' => 'efectivo'
                    ]);
                }

                // Registrar detalle de QR
                if ($montoQr > 0) {
                    Detalle::create([
                        'pago_id' => $pago->id,
                        'pago_bs' => $montoQr,
                        'descuentos_bs' => 0,
                        'tipo' => 'qr'
                    ]);
                }
            } else {
                // Para pagos no parciales, registrar un solo detalle
                Detalle::create([
                    'pago_id' => $pago->id,
                    'pago_bs' => $montoFaltante,
                    'descuentos_bs' => 0,
                    'tipo' => $tipoPago
                ]);
            }

            // Actualizar la deuda del cliente
            $cliente = Cliente::find($request->cliente_id);
            $cliente->deuda = $cliente->pedidos()
                ->where('pagado', 0)
                ->where(function ($query) {
                    $query->whereRaw('(subtotal - IFNULL(pagado_bs, 0) - IFNULL(descuento_bs, 0)) > 0');
                })
                ->sum(\DB::raw('(subtotal - IFNULL(pagado_bs, 0) - IFNULL(descuento_bs, 0))'));
            $cliente->save();

            \DB::commit();

            return response()->json([
                'success' => true,
                'msg' => 'Pedido marcado como completamente pagado. Recibo: ' . $pago->recibo,
                'recibo' => $pago->recibo,
                'deuda_actualizada' => $cliente->deuda
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error al pagar pedido completo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => 'Error al procesar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    public function obtenerPedidosPendientes($id)
    {
        $cliente = Cliente::findOrFail($id);
        $pedidosPendientes = $cliente->pedidos()
            ->where('pagado', 0)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'pedidos' => $pedidosPendientes
        ]);
    }

    public function realizarPagoIndividual(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'pedido_id' => 'required|exists:pedidos,id',
            'monto_pago' => 'required|numeric|min:0.01',
            'descuento_bs' => 'nullable|numeric|min:0',
            'tipo_pago' => 'required|in:efectivo,qr,parcial'
        ]);

        try {
            $pedido = Pedido::where('id', $request->pedido_id)
                ->where('cliente_id', $request->cliente_id)
                ->firstOrFail();

            $montoPago = $request->monto_pago;
            $descuento = $request->descuento_bs ?? 0;
            $subtotal = $pedido->subtotal;

            // Obtener pagado_bs actual
            $pagadoActual = $pedido->pagado_bs ?? 0;
            $descuentoActual = $pedido->descuento_bs ?? 0;

            // Calcular nuevo pagado_bs
            $nuevoPagadoBs = $pagadoActual + $montoPago;

            // Calcular nuevo descuento_bs
            $nuevoDescuentoBs = $descuentoActual + $descuento;

            // Calcular pendiente
            $nuevoPendiente = $subtotal - $nuevoPagadoBs - $nuevoDescuentoBs;

            // Verificar que el monto no sea mayor al subtotal
            if ($nuevoPagadoBs + $nuevoDescuentoBs > $subtotal) {
                return response()->json([
                    'success' => false,
                    'msg' => 'El monto a pagar más el descuento no puede ser mayor al total del pedido.'
                ], 422);
            }

            // Si el pago es igual al subtotal, marcar como completamente pagado
            if ($nuevoPendiente <= 0) {
                $pedido->pagado = 1;
                $pedido->pendiente = 0;
                $montoPago = $subtotal - $pagadoActual - $descuentoActual; // Ajustar para no pagar más
            } else {
                // Si es un pago parcial, actualizar el pendiente
                $pedido->pendiente = $nuevoPendiente;
                $pedido->pagado = 0; // Aún no está completamente pagado
            }

            $pedido->pagado_bs = $nuevoPagadoBs;
            $pedido->descuento_bs = $nuevoDescuentoBs;
            $pedido->save();

            // Registrar el pago (el recibo se genera automáticamente)
            $pago = Pago::create([
                'cliente_id' => $request->cliente_id,
                'pedido_id' => $request->pedido_id,
                'pago_bs' => $montoPago,
                'descuento_bs' => $descuento,
                'tipo' => $request->tipo_pago,
                'cobrador' => auth()->user()->name ?? 'Sistema',
                'user_id' => auth()->user()->id ?? null,
                'pago_total' => $montoPago + $descuento
            ]);

            // Actualizar la deuda del cliente
            $cliente = Cliente::find($request->cliente_id);
            $cliente->deuda = $cliente->pedidos()->where('pagado', 0)->sum('pendiente');
            $cliente->save();

            return response()->json([
                'success' => true,
                'msg' => 'Pago realizado correctamente. Recibo: ' . $pago->recibo,
                'recibo' => $pago->recibo,
                'nuevo_pendiente' => $pedido->pendiente,
                'esta_pagado' => $pedido->pagado,
                'deuda_actualizada' => $cliente->deuda
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al realizar pago individual: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => 'Error al realizar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    public function realizarPagoMultiple(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'pedidos' => 'required|array',
            'pedidos.*.id' => 'required|exists:pedidos,id',
            'pedidos.*.monto_pago' => 'required|numeric|min:0.01',
            'pedidos.*.descuento_bs' => 'nullable|numeric|min:0',
            'pedidos.*.tipo_pago' => 'required|in:efectivo,qr,parcial'
        ]);

        try {
            $cliente = Cliente::findOrFail($request->cliente_id);
            $totalPagado = 0;
            $pedidosProcesados = [];
            $recibosGenerados = [];

            foreach ($request->pedidos as $pedidoData) {
                $pedido = Pedido::where('id', $pedidoData['id'])
                    ->where('cliente_id', $request->cliente_id)
                    ->first();

                if (!$pedido) {
                    continue;
                }

                $montoPago = $pedidoData['monto_pago'];
                $descuento = $pedidoData['descuento_bs'] ?? 0;
                $subtotal = $pedido->subtotal;

                // Obtener pagado_bs actual
                $pagadoActual = $pedido->pagado_bs ?? 0;
                $descuentoActual = $pedido->descuento_bs ?? 0;

                // Calcular nuevo pagado_bs
                $nuevoPagadoBs = $pagadoActual + $montoPago;

                // Calcular nuevo descuento_bs
                $nuevoDescuentoBs = $descuentoActual + $descuento;

                // Calcular pendiente
                $nuevoPendiente = $subtotal - $nuevoPagadoBs - $nuevoDescuentoBs;

                // Verificar que el monto no sea mayor al subtotal
                if ($nuevoPagadoBs + $nuevoDescuentoBs > $subtotal) {
                    return response()->json([
                        'success' => false,
                        'msg' => "El monto a pagar para el pedido #{$pedido->id} no puede ser mayor al total del pedido."
                    ], 422);
                }

                // Si el pago cubre todo el pendiente, marcar como pagado
                if ($nuevoPendiente <= 0) {
                    $pedido->pagado = 1;
                    $pedido->pendiente = 0;
                } else {
                    // Si es un pago parcial, actualizar el pendiente
                    $pedido->pendiente = $nuevoPendiente;
                    $pedido->pagado = 0;
                }

                $pedido->pagado_bs = $nuevoPagadoBs;
                $pedido->descuento_bs = $nuevoDescuentoBs;
                $pedido->save();
                $totalPagado += $montoPago;

                // Registrar el pago (el recibo se genera automáticamente)
                $pago = Pago::create([
                    'cliente_id' => $request->cliente_id,
                    'pedido_id' => $pedido->id,
                    'pago_bs' => $montoPago,
                    'descuento_bs' => $descuento,
                    'tipo' => $pedidoData['tipo_pago'],
                    'cobrador' => auth()->user()->name ?? 'Sistema',
                    'user_id' => auth()->user()->id ?? null,
                    'pago_total' => $montoPago + $descuento
                ]);

                $recibosGenerados[] = $pago->recibo;

                $pedidosProcesados[] = [
                    'id' => $pedido->id,
                    'pagado_completo' => $pedido->pagado,
                    'nuevo_pendiente' => $pedido->pendiente,
                    'recibo' => $pago->recibo
                ];
            }

            // Actualizar la deuda del cliente
            $cliente->deuda = $cliente->pedidos()->where('pagado', 0)->sum('pendiente');
            $cliente->save();

            return response()->json([
                'success' => true,
                'msg' => 'Pagos realizados correctamente. Recibos: ' . implode(', ', $recibosGenerados),
                'total_pagado' => $totalPagado,
                'deuda_actualizada' => $cliente->deuda,
                'pedidos_procesados' => $pedidosProcesados,
                'recibos' => $recibosGenerados
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al realizar pago múltiple: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => 'Error al realizar los pagos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generarReciboPdf($pagoId)
    {
        try {
            // Cargar la relación con pedidos a través de la tabla intermedia
            $pago = Pago::with([
                'cliente',
                'detalles',
                'cliente.localidad',
                'pedidos' => function ($query) {
                    $query->withPivot('monto_asignado', 'descuento_asignado')
                        ->with('pedidos_productos'); // Agregar esta línea
                }
            ])->findOrFail($pagoId);

            $cliente = $pago->cliente;

            // Calcular totales desde los datos de la tabla intermedia
            $totalMontoAsignado = $pago->pedidos->sum(function ($pedido) {
                return $pedido->pivot->monto_asignado;
            });

            $totalDescuentoAsignado = $pago->pedidos->sum(function ($pedido) {
                return $pedido->pivot->descuento_asignado;
            });

            $pdf = Pdf::loadView('admin.recibos.recibo-pago', [
                'pago' => $pago,
                'cliente' => $cliente,
                'totalMontoAsignado' => $totalMontoAsignado,
                'totalDescuentoAsignado' => $totalDescuentoAsignado
            ]);

            // Configurar papel y orientación
            $pdf->setPaper('A4', 'portrait');

            // Opciones para mejorar la calidad
            $pdf->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

            return $pdf->download('recibo-' . $pago->recibo . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Error al generar recibo PDF: ' . $e->getMessage());
            abort(404, 'Recibo no encontrado: ' . $e->getMessage());
        }
    }

    public function verRecibo($pagoId)
    {
        try {
            // Cargar la relación con pedidos a través de la tabla intermedia
            $pago = Pago::with([
                'cliente',
                'detalles',
                'cliente.localidad',
                'pedidos' => function ($query) {
                    $query->withPivot('monto_asignado', 'descuento_asignado')
                        ->with('pedidos_productos'); // Agregar esta línea
                }
            ])->findOrFail($pagoId);

            $cliente = $pago->cliente;

            // Calcular totales desde los datos de la tabla intermedia
            $totalMontoAsignado = $pago->pedidos->sum(function ($pedido) {
                return $pedido->pivot->monto_asignado;
            });

            $totalDescuentoAsignado = $pago->pedidos->sum(function ($pedido) {
                return $pedido->pivot->descuento_asignado;
            });

            $pdf = Pdf::loadView('admin.recibos.recibo-pago', [
                'pago' => $pago,
                'cliente' => $cliente,
                'totalMontoAsignado' => $totalMontoAsignado,
                'totalDescuentoAsignado' => $totalDescuentoAsignado
            ]);

            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

            return $pdf->stream('recibo-' . $pago->recibo . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Error al ver recibo: ' . $e->getMessage());
            abort(404, 'Recibo no encontrado: ' . $e->getMessage());
        }
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

            // Calcular totales
            $totalMontoAsignado = $pago->pedidos->sum(function ($pedido) {
                return $pedido->pivot->monto_asignado;
            });

            $totalDescuentoAsignado = $pago->pedidos->sum(function ($pedido) {
                return $pedido->pivot->descuento_asignado;
            });

            // Devolver el HTML para el modal con los datos adicionales
            return response()->json([
                'success' => true,
                'html' => view('admin.clientes.partials.detalles-pago', [
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

    public function realizarPagoMultiplePriorizado(Request $request)
    {
        \Log::info('=== INICIO realizarPagoMultiplePriorizado (UN SOLO RECIBO) ===');
        \Log::info('Datos recibidos:', $request->all());

        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'pedidos_ids' => 'required|array|min:1',
            'pedidos_ids.*' => 'exists:pedidos,id',
            'monto_total_pago' => 'required|numeric|min:0.01',
            'descuento_total' => 'nullable|numeric|min:0',
            'tipo_pago' => 'required|in:efectivo,qr,parcial',
            'monto_efectivo' => 'nullable|numeric|min:0',
            'monto_qr' => 'nullable|numeric|min:0',
            'distribucion' => 'required|array'
        ]);

        try {
            \DB::beginTransaction();

            $cliente = Cliente::findOrFail($request->cliente_id);
            $montoTotalPago = $request->monto_total_pago;
            $descuentoTotal = $request->descuento_total ?? 0;
            $totalAPagar = $montoTotalPago + $descuentoTotal;

            // Calcular total pendiente de los pedidos seleccionados
            $totalPendienteSeleccionado = 0;
            foreach ($request->pedidos_ids as $pedidoId) {
                $pedido = Pedido::find($pedidoId);
                if ($pedido) {
                    $pagadoBs = $pedido->pagado_bs ?? 0;
                    $descuentoBs = $pedido->descuento_bs ?? 0;
                    $pendiente = $pedido->subtotal - $pagadoBs - $descuentoBs;
                    $totalPendienteSeleccionado += $pendiente;
                }
            }

            // Validar que el monto a pagar no sea mayor al pendiente seleccionado
            if ($totalAPagar > $totalPendienteSeleccionado) {
                return response()->json([
                    'success' => false,
                    'msg' => 'El monto a pagar no puede ser mayor al pendiente total de los pedidos seleccionados (Bs. ' . number_format($totalPendienteSeleccionado, 2) . ')'
                ], 422);
            }

            // ========== CREAR UN SOLO PAGO (RECIBO) ==========
            $pago = Pago::create([
                'cliente_id' => $request->cliente_id,
                'pago_bs' => $montoTotalPago,
                'descuento_bs' => $descuentoTotal,
                'tipo' => $request->tipo_pago,
                'user_id' => auth()->user()->id ?? null,
            ]);

            $pedidosProcesados = [];

            foreach ($request->distribucion as $distribucion) {
                $pedido = Pedido::find($distribucion['pedido_id']);
                if (!$pedido) continue;

                $montoParaEstePedido = floatval($distribucion['monto_asignado'] ?? 0);
                $descuentoParaEstePedido = floatval($distribucion['descuento_asignado'] ?? 0);

                if ($montoParaEstePedido <= 0 && $descuentoParaEstePedido <= 0) continue;

                // Actualizar valores actuales del pedido
                $pagadoActual = $pedido->pagado_bs ?? 0;
                $descuentoActual = $pedido->descuento_bs ?? 0;

                // Calcular nuevos valores
                $nuevoPagadoBs = $pagadoActual + $montoParaEstePedido;
                $nuevoDescuentoBs = $descuentoActual + $descuentoParaEstePedido;
                $nuevoPendiente = $pedido->subtotal - $nuevoPagadoBs - $nuevoDescuentoBs;

                // Actualizar el pedido
                $pedido->pagado_bs = $nuevoPagadoBs;
                $pedido->descuento_bs = $nuevoDescuentoBs;
                $pedido->pendiente = $nuevoPendiente;

                if ($nuevoPendiente <= 0) {
                    $pedido->pagado = 1;
                    $pedido->pendiente = 0;
                } else {
                    $pedido->pagado = 0;
                }

                $pedido->save();

                // ========== GUARDAR EN TABLA INTERMEDIA ==========
                $pago->pedidos()->attach($pedido->id, [
                    'monto_asignado' => $montoParaEstePedido,
                    'descuento_asignado' => $descuentoParaEstePedido
                ]);

                $pedidosProcesados[] = [
                    'id' => $pedido->id,
                    'codigo' => $pedido->codigo ?? 'N/A',
                    'monto_aplicado' => $montoParaEstePedido,
                    'descuento_aplicado' => $descuentoParaEstePedido,
                    'nuevo_pendiente' => $nuevoPendiente,
                    'pagado_completo' => $pedido->pagado
                ];
            }

            // Registrar detalles según el tipo de pago
            if ($request->tipo_pago === 'parcial') {
                $montoEfectivo = $request->monto_efectivo ?? 0;
                $montoQr = $request->monto_qr ?? 0;

                if ($montoEfectivo > 0) {
                    Detalle::create([
                        'pago_id' => $pago->id,
                        'pago_bs' => $montoEfectivo,
                        'descuentos_bs' => 0,
                        'tipo' => 'efectivo'
                    ]);
                }

                if ($montoQr > 0) {
                    Detalle::create([
                        'pago_id' => $pago->id,
                        'pago_bs' => $montoQr,
                        'descuentos_bs' => 0,
                        'tipo' => 'qr'
                    ]);
                }
            } else {
                Detalle::create([
                    'pago_id' => $pago->id,
                    'pago_bs' => $montoTotalPago,
                    'descuentos_bs' => $descuentoTotal,
                    'tipo' => $request->tipo_pago
                ]);
            }

            // Actualizar la deuda del cliente
            $cliente->deuda = $cliente->pedidos()
                ->where('pagado', 0)
                ->where(function ($query) {
                    $query->whereRaw('(subtotal - IFNULL(pagado_bs, 0) - IFNULL(descuento_bs, 0)) > 0');
                })
                ->sum(\DB::raw('(subtotal - IFNULL(pagado_bs, 0) - IFNULL(descuento_bs, 0))'));
            $cliente->save();

            \DB::commit();

            return response()->json([
                'success' => true,
                'msg' => 'Pago múltiple realizado correctamente con un solo recibo: ' . $pago->recibo,
                'recibo' => $pago->recibo,  // Solo un recibo
                'recibo_url' => route('admin.recibos.ver', $pago->id),
                'pedidos_procesados' => $pedidosProcesados,
                'deuda_actualizada' => $cliente->deuda,
                'total_pagado' => $montoTotalPago,
                'total_descuento' => $descuentoTotal
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error en realizarPagoMultiplePriorizado: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'msg' => 'Error al realizar el pago múltiple: ' . $e->getMessage()
            ], 500);
        }
    }
}
