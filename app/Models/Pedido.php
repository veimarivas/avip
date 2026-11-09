<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'platillo',
        'subtotal',
        'pagado_bs',
        'descuento_bs',
        'pendiente',
        'pagado',
        'estado',
        'cliente_id',
        'localidade_id',
        'pedido_dia',
        'codigo_semana',
        'codigo_dia',
        'dia',
        'codigo',
        'total_ingreso',
        'generado',
        'deshabilitado',
        'created_at',
        'updated_at'
    ];

    public function localidad()
    {
        return $this->belongsTo(Localidade::class, 'localidade_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    // Un pedido puede tener múltiples pagos
    public function pagos()
    {
        return $this->belongsToMany(Pago::class, 'pago_pedido', 'pedido_id', 'pago_id')
            ->withPivot('monto_asignado', 'descuento_asignado')
            ->withTimestamps();
    }

    // Método para obtener el pago relacionado (primer pago)
    public function pago()
    {
        return $this->pagos()->first();
    }

    public function pedidos_productos()
    {
        return $this->hasMany(PedidoProductos::class, 'pedido_id');
    }
}
