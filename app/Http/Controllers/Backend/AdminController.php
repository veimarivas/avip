<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Localidade;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function AdminLogout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function dashboard()
    {
        // Obtener todas las localidades para mostrar en el sidebar
        $localidades = Localidade::withCount('clientes')->orderBy('nombre')->get();

        // Estadísticas
        $totalClientes = Cliente::count();
        $deudaTotal = Cliente::sum('deuda');
        $pedidosHoy = Pedido::whereDate('created_at', today())->count();

        return view('admin.index', compact('localidades', 'totalClientes', 'deudaTotal', 'pedidosHoy'));
    }

    public function obtenerEventosCalendario(Request $request)
    {
        try {
            $pedidos = Pedido::with('localidad')
                ->select('created_at', 'localidade_id', 'codigo')
                ->selectRaw('COUNT(*) as total_pedidos')
                ->groupBy('created_at', 'localidade_id', 'codigo')
                ->get()
                ->map(function ($pedido) {
                    return [
                        'title' => $pedido->localidad->nombre,
                        'start' => $pedido->created_at,
                        'end' => $pedido->created_at,
                        'color' => $pedido->localidad->color, // Color de fondo
                        'textColor' => '#ffffff', // Color del texto (blanco para mejor contraste)
                        'borderColor' => $pedido->localidad->color, // Color del borde
                        'extendedProps' => [
                            'localidad_id' => $pedido->localidade_id,
                            'localidad_nombre' => $pedido->localidad->nombre,
                            'codigo' => $pedido->codigo,
                            'total_pedidos' => $pedido->total_pedidos,
                            'fecha' => $pedido->created_at->format('Y-m-d H:i:s')
                        ]
                    ];
                });

            \Log::info('Eventos del calendario enviados:', ['count' => $pedidos->count()]);

            return response()->json($pedidos);
        } catch (\Exception $e) {
            \Log::error('Error en obtenerEventosCalendario: ' . $e->getMessage());
            return response()->json([]);
        }
    }
}
