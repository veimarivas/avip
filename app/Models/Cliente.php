<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';

    protected $fillable = [
        'ci',
        'nombres',
        'apellidos',
        'deuda_anterior',
        'deuda',
        'orden',
        'localidade_id',
        'habilitado'
    ];

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'cliente_id');
    }

    public function localidad()
    {
        return $this->belongsTo(Localidade::class, 'localidade_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'cliente_id');
    }

    public function pedidosPendientes()
    {
        return $this->hasMany(Pedido::class, 'cliente_id')
            ->where('pendiente', '>', 0)
            ->orderBy('created_at', 'desc');
    }

    public function pedidos_productos()
    {
        return $this->hasMany(PedidoProductos::class, 'cliente_id');
    }

    public function getDeudaTotalAttribute()
    {
        // Si ya cargamos la relación, usamos esos datos
        if ($this->relationLoaded('pedidos')) {
            return $this->pedidos->where('pagado', 0)->sum('subtotal');
        }

        // Si no, hacemos una consulta
        return $this->pedidos()->where('pagado', 0)->where('subtotal', '>', 0)->sum('subtotal');
    }
}
