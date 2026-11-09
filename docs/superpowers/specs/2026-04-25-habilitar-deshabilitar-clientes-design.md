# Diseño: Habilitar/Deshabilitar Clientes

**Fecha:** 2026-04-25

---

## 1. Resumen

Agregar funcionalidad para habilitar/deshabilitar clientes en el sistema. Un cliente deshabilitado no será considerado en la generación automática de pedidos diarios. Solo se permite deshabilitar si el cliente tiene deuda = 0. Un cliente deshabilitado puede volver a habilitarse.

---

## 2. Cambios en Base de Datos

### 2.1 Migración

Crear migración para agregar columna `habilitado` a tabla `clientes`:

| Campo | Tipo | Default | Notas |
|-------|------|--------|-------|
| habilitado | boolean | true | No nullable |

```php
$table->boolean('habilitado')->default(true);
```

### 2.2 Modelo (Cliente.php)

Agregar `habilitado` al array `$fillable`:

```php
protected $fillable = [
    'ci',
    'nombres',
    'apellidos',
    'deuda_anterior',
    'deuda',
    'orden',
    'localidade_id',
    'habilitado'  // agregar
];
```

---

## 3. Vista (admin.clientes.listar.blade.php)

### 3.1 Nueva Columna en Tabla

Agregar columna después de "Localidad":

```blade
<th>Estado</th>
```

### 3.2 Renderizar Estado en parcial/table-body.blade.php

Después de la columna de localidad, agregar celda:

```blade
<td class="text-center">
    @if($cliente->habilitado)
        <span class="badge bg-success">Activo</span>
    @else
        <span class="badge bg-secondary">Inactivo</span>
    @endif
</td>
```

### 3.3 Botón de Acción en table-body.blade.php

Agregar botón para cambiar estado en el grupo de botones de acciones:

```blade
@if(Auth::guard('web')->user()->can('clientes.editar'))
    @if($cliente->habilitado)
        {{-- Botón para deshabilitar (solo si deuda == 0) --}}
        @if($deuda == 0)
            <button type="button" title="Deshabilitar Cliente" 
                class="btn btn-outline-secondary btn-sm toggleHabilitadoBtn"
                data-id="{{ $cliente->id }}" 
                data-habilitado="{{ $cliente->habilitado }}">
                <i class="ri-toggle-off-line"></i>
            </button>
        @else
            <button type="button" title="No se puede deshabilitar: tiene deuda" 
                class="btn btn-outline-secondary btn-sm" disabled>
                <i class="ri-toggle-off-line"></i>
            </button>
        @endif
    @else
        {{-- Botón para habilitar --}}
        <button type="button" title="Habilitar Cliente" 
            class="btn btn-outline-success btn-sm toggleHabilitadoBtn"
            data-id="{{ $cliente->id }}" 
            data-habilitado="{{ $cliente->habilitado }}">
            <i class="ri-toggle-on-line"></i>
        </button>
    @endif
@endif
```

### 3.4 JavaScript para Cambio de Estado

En el script principal de listar.blade.php agregar:

```javascript
// Toggle habilitar/deshabilitar cliente
$(document).on('click', '.toggleHabilitadoBtn', function() {
    const btn = $(this);
    const clienteId = btn.data('id');
    const habilitado = btn.data('habilitado');
    const nuevoEstado = habilitado ? 0 : 1;
    
    // Si se intenta deshabilitar (habilitado=1 -> 0), verificar deuda primero
    if (habilitado === 1) {
        // El controller validará la deuda
    }
    
    $.ajax({
        url: "{{ route('admin.clientes.cambiar-estado') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            id: clienteId,
            habilitado: nuevoEstado
        },
        success: function(res) {
            if (res.success) {
                showNotification('success', res.msg);
                loadResults($('#searchInput').val().trim(), currentLocalidadId);
            } else {
                showNotification('error', res.msg);
            }
        },
        error: function(xhr) {
            let errorMsg = 'Error al cambiar estado del cliente.';
            if (xhr.responseJSON?.msg) {
                errorMsg = xhr.responseJSON.msg;
            }
            showNotification('error', errorMsg);
        }
    });
});
```

---

## 4. Controlador (ClientesController.php)

### 4.1 Agregar método cambiarEstado

```php
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
```

### 4.2 Modificar clientesListar para incluir habilitado

En la consulta base ya está el select *, así que ya traerá el campo.

### 4.3 Modificar clientesPorLocalidad para filtrar solo habilitados

Depende de dónde se generen los pedidos automáticos. Buscar donde se listan clientes por localidad y agregar:

```php
->where('habilitado', 1)
```

---

## 5. Rutas

Agregar ruta en routes/web.php:

```php
Route::post('/clientes/cambiar-estado', [ClientesController::class, 'clienteCambiarEstado'])
    ->name('admin.clientes.cambiar-estado')
    ->middleware('auth');
```

---

## 6. Permisos

Agregar permiso en la seed de permisos:
- `clientes.cambiar.estado` - Para cambiar estado habilitado/deshabilitado

Actualizar el middleware de permisos si es necesario.

---

## 7. Generación Automática de Pedidos

En el código donde se generan pedidos automáticamente (buscar en PedidosController o donde se procesen pedidos por locality), agregar filtro:

```php
$clientes = Cliente::where('localidade_id', $localidadId)
    ->where('habilitado', 1)  // Solo clientes habilitados
    ->get();
```

---

## 8. Notas Adicionales

- Los clientes deshabilitados siguen appearing en el listado de clientes (para que el admin pueda verlos y habilitarlos nuevamente)
- La deuda se calcula igual que antes - no cambia la lógica
- Al habilitar nuevamente, no hay restricción de deuda (puede habilitarse aunque deba dinero)