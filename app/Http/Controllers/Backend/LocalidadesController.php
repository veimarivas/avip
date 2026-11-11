<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Localidade;
use App\Models\LocalidadesProductos;
use App\Models\Pedido;
use App\Models\PedidoProductos;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class LocalidadesController extends Controller
{
    public function localidadesListar(Request $request)
    {
        $search = $request->get('search', '');
        $query = Localidade::query();
        if ($search) {
            $query->where('nombre', 'like', "%{$search}%");
        }
        $localidades = $query->paginate(10);

        // Es importante mantener los parámetros de búsqueda en la paginación
        $localidades->appends(['search' => $search]);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.localidades.partials.table-body', compact('localidades'))->render(),
                'pagination' => $localidades->links('pagination::bootstrap-5')->toHtml(),
                'total' => $localidades->total(),
                'from' => $localidades->firstItem(),
                'to' => $localidades->lastItem()
            ]);
        }
        return view('admin.localidades.listar', compact('localidades'));
    }

    public function localidadesRegistrar(Request $request)
    {
        $request->validate([
            'nombre' => 'required|unique:localidades,nombre',
            'color' => 'required|string|max:7'
        ]);

        $localidad = new Localidade();
        $localidad->nombre = $request->nombre;
        $localidad->color = $request->color;
        $localidad->save();

        return response()->json([
            'success' => true,
            'msg' => 'Localidad registrada correctamente.',
            'localidad' => $localidad
        ]);
    }

    public function verificarNombre(Request $request)
    {
        $nombre = $request->input('nombre');
        $id = $request->input('id');

        $query = Localidade::where('nombre', $nombre);

        if ($id && is_numeric($id) && $id > 0) {
            $query->where('id', '!=', $id);
        }

        return response()->json(['exists' => $query->exists()]);
    }

    public function verificarNombreEdicion(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
        ]);

        $nombre = trim($request->input('nombre'));
        $id = $request->input('id');

        $id = is_numeric($id) && $id > 0 ? (int) $id : null;

        $exists = Localidade::where('nombre', $nombre)
            ->when($id, function ($query) use ($id) {
                return $query->where('id', '!=', $id);
            })
            ->exists();

        return response()->json(['exists' => $exists]);
    }

    public function localidadesModificar(Request $request)
    {
        \Log::info('Datos recibidos para modificar localidad:', $request->all());

        $request->validate([
            'id' => 'required|exists:localidades,id',
            'nombre' => 'required|unique:localidades,nombre,' . $request->id,
            'color' => 'required|string|max:7'
        ]);

        $localidad = Localidade::findOrFail($request->id);

        // Verificar si el nombre realmente cambió
        if ($localidad->nombre !== $request->nombre) {
            $existe = Localidade::where('nombre', $request->nombre)
                ->where('id', '!=', $request->id)
                ->exists();

            if ($existe) {
                return response()->json([
                    'success' => false,
                    'msg' => 'El nombre de la localidad ya existe.'
                ], 422);
            }
        }

        $localidad->nombre = $request->nombre;
        $localidad->color = $request->color;
        $localidad->save();

        return response()->json([
            'success' => true,
            'msg' => 'Localidad actualizada correctamente.'
        ]);
    }

    public function localidadesEliminar(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:localidades,id'
        ]);

        $localidad = Localidade::findOrFail($request->id);

        // Verificar si hay relaciones antes de eliminar
        /*if (
            $localidad->clientes()->count() > 0 ||
            $localidad->pedidos()->count() > 0 ||
            $localidad->localidades_productos()->count() > 0 ||
            $localidad->pedidos_productos()->count() > 0
        ) {
            return response()->json([
                'success' => false,
                'msg' => 'No se puede eliminar la localidad porque tiene registros asociados.'
            ], 422);
        }*/

        $localidad->delete();

        return response()->json([
            'success' => true,
            'msg' => 'Localidad eliminada correctamente.'
        ]);
    }

    public function localidadesVer($id)
    {
        $localidad = Localidade::with(['productos'])->findOrFail($id);
        $todosProductos = Producto::all(); // Para poder agregar nuevos productos

        $ultimoPedido = Pedido::where('localidade_id', $id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        $deudaGenerada = false;
        if ($ultimoPedido) {
            $deudaGenerada = Pedido::where('localidade_id', $id)
                ->whereDate('created_at', $ultimoPedido->created_at->format('Y-m-d'))
                ->where('subtotal', '>', 0)
                ->exists();
        }

        return view('admin.localidades.ver', compact(
            'localidad',
            'todosProductos',
            'ultimoPedido',
            'deudaGenerada'
        ));
    }

    public function actualizarPrecioProducto(Request $request)
    {
        $request->validate([
            'localidad_id' => 'required|exists:localidades,id',
            'producto_id' => 'required|exists:productos,id',
            'precio' => 'required|numeric|min:0'
        ]);

        try {
            $localidadProducto = LocalidadesProductos::updateOrCreate(
                [
                    'localidade_id' => $request->localidad_id,
                    'producto_id' => $request->producto_id
                ],
                [
                    'precio' => $request->precio
                ]
            );

            return response()->json([
                'success' => true,
                'msg' => 'Precio actualizado correctamente.',
                'precio' => $localidadProducto->precio
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => 'Error al actualizar el precio: ' . $e->getMessage()
            ], 500);
        }
    }

    public function agregarProducto(Request $request)
    {
        $request->validate([
            'localidad_id' => 'required|exists:localidades,id',
            'producto_id' => 'required|exists:productos,id',
            'precio' => 'required|numeric|min:0'
        ]);

        try {
            // Verificar si ya existe la relación
            $existente = LocalidadesProductos::where('localidade_id', $request->localidad_id)
                ->where('producto_id', $request->producto_id)
                ->first();

            if ($existente) {
                return response()->json([
                    'success' => false,
                    'msg' => 'Este producto ya está asociado a la localidad.'
                ], 422);
            }

            $localidadProducto = LocalidadesProductos::create([
                'localidade_id' => $request->localidad_id,
                'producto_id' => $request->producto_id,
                'precio' => $request->precio
            ]);

            $producto = Producto::find($request->producto_id);

            return response()->json([
                'success' => true,
                'msg' => 'Producto agregado correctamente.',
                'producto' => [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'n_caja' => $producto->n_caja,
                    'pivot' => [
                        'precio' => $localidadProducto->precio
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => 'Error al agregar el producto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function eliminarProducto(Request $request)
    {
        $request->validate([
            'localidad_id' => 'required|exists:localidades,id',
            'producto_id' => 'required|exists:productos,id'
        ]);

        try {
            $localidadProducto = LocalidadesProductos::where('localidade_id', $request->localidad_id)
                ->where('producto_id', $request->producto_id)
                ->first();

            if (!$localidadProducto) {
                return response()->json([
                    'success' => false,
                    'msg' => 'El producto no está asociado a esta localidad.'
                ], 404);
            }

            $localidadProducto->delete();

            return response()->json([
                'success' => true,
                'msg' => 'Producto eliminado de la localidad correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => 'Error al eliminar el producto: ' . $e->getMessage()
            ], 500);
        }
    }

    public function actualizarPreciosMasivo(Request $request)
    {
        \Log::info('=== INICIO ACTUALIZAR PRECIOS MASIVO ===');
        \Log::info('Datos recibidos para actualización masiva:', $request->all());

        $esAjax = $request->expectsJson() || $request->ajax();

        try {
            // Validación
            if (!$request->has('precios') || !is_array($request->precios)) {
                \Log::error('Datos de precios no recibidos o formato incorrecto');
                $mensaje = 'Formato de datos incorrecto. Los precios deben ser un array.';

                if ($esAjax) {
                    return response()->json(['success' => false, 'msg' => $mensaje], 422);
                }

                return redirect()->back()->with('error', $mensaje);
            }

            $validator = Validator::make($request->all(), [
                'localidad_id' => 'required|exists:localidades,id',
                'precios' => 'required|array',
                'precios.*.producto_id' => 'required|exists:productos,id',
                'precios.*.precio' => 'required|numeric|min:0'
            ]);

            if ($validator->fails()) {
                \Log::error('Error de validación:', $validator->errors()->toArray());

                if ($esAjax) {
                    return response()->json([
                        'success' => false,
                        'msg' => 'Error de validación en los datos enviados.',
                        'errors' => $validator->errors(),
                    ], 422);
                }

                return redirect()->back()
                    ->withErrors($validator)
                    ->with('error', 'Error de validación en los datos enviados.');
            }

            \Log::info('Validación pasada correctamente');

            DB::beginTransaction();

            $localidadId = $request->localidad_id;
            $precios = $request->precios;

            // 1. Obtener el último pedido generado para la localidad
            $ultimoPedido = Pedido::where('localidade_id', $localidadId)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->first();

            if (!$ultimoPedido) {
                DB::rollBack();
                \Log::warning('No hay pedidos registrados para esta localidad');
                $mensaje = 'No hay pedidos registrados para esta localidad.';

                if ($esAjax) {
                    return response()->json(['success' => false, 'msg' => $mensaje], 422);
                }

                return redirect()->back()->with('error', $mensaje);
            }

            $fechaUltimoPedido = $ultimoPedido->created_at->format('Y-m-d');
            $fechaMostrar = $ultimoPedido->created_at->format('d/m/Y');
            $inicioFecha = $fechaUltimoPedido . ' 00:00:00';
            $finFecha = $fechaUltimoPedido . ' 23:59:59';
            \Log::info("Fecha del último pedido: $fechaUltimoPedido");

            // 2. Si el último pedido ya tiene deuda generada, no se pueden
            // modificar los precios (aunque los pedidos ya estén registrados).
            $deudaGenerada = Pedido::where('localidade_id', $localidadId)
                ->whereDate('created_at', $fechaUltimoPedido)
                ->where('subtotal', '>', 0)
                ->exists();

            if ($deudaGenerada) {
                DB::rollBack();
                $mensaje = 'El último pedido de la localidad (código '
                    . $ultimoPedido->codigo . ', fecha ' . $fechaMostrar
                    . ') ya tiene deuda generada. No se pueden modificar los precios.';
                \Log::warning('Actualización de precios bloqueada: deuda generada', [
                    'localidade_id' => $localidadId,
                    'fecha' => $fechaUltimoPedido,
                ]);

                if ($esAjax) {
                    return response()->json([
                        'success' => false,
                        'msg' => $mensaje,
                        'fecha' => $fechaMostrar,
                        'codigo' => $ultimoPedido->codigo,
                    ], 409);
                }

                return redirect()->back()->with('error', $mensaje);
            }

            // 3. Actualizar precios en localidades_productos
            foreach ($precios as $item) {
                $productoId = $item['producto_id'];
                $nuevoPrecio = $item['precio'];

                \Log::info("Actualizando producto $productoId a precio $nuevoPrecio");

                $actualizados = LocalidadesProductos::where('localidade_id', $localidadId)
                    ->where('producto_id', $productoId)
                    ->update(['precio' => $nuevoPrecio]);

                \Log::info("Registros actualizados en localidades_productos: $actualizados");
            }

            // 4. Actualizar precios en pedido_productos del último pedido generado.
            // Si no hay deuda generada, se actualizan todos los pedidos de esa fecha.
            foreach ($precios as $item) {
                $productoId = $item['producto_id'];
                $nuevoPrecio = $item['precio'];

                $actualizados = PedidoProductos::where('localidade_id', $localidadId)
                    ->where('producto_id', $productoId)
                    ->whereBetween('created_at', [$inicioFecha, $finFecha])
                    ->update(['precio' => $nuevoPrecio]);

                \Log::info("Actualizados $actualizados registros en pedido_productos para producto $productoId");
            }

            DB::commit();

            \Log::info('=== ACTUALIZACIÓN MASIVA COMPLETADA EXITOSAMENTE ===');

            $mensaje = 'Precios actualizados correctamente en la localidad y en el último pedido del '
                . $fechaMostrar . '.';

            if ($esAjax) {
                return response()->json([
                    'success' => true,
                    'msg' => $mensaje,
                    'fecha' => $fechaMostrar,
                    'codigo' => $ultimoPedido->codigo,
                ]);
            }

            return redirect()->route('admin.localidades.ver', $localidadId)
                ->with('success', $mensaje);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar precios masivamente: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());

            $mensaje = 'Error al actualizar los precios: ' . $e->getMessage();

            if ($esAjax) {
                return response()->json(['success' => false, 'msg' => $mensaje], 500);
            }

            return redirect()->back()->with('error', $mensaje);
        }
    }
}
