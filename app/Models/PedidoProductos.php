<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoProductos extends Model
{
    protected $table = 'pedido_productos';

    protected $fillable = [
        'n_cajas',
        'peso',
        'peso_pedido',
        'tara',
        'peso_real',
        'precio',
        'costo_total',
        'realizado',
        'procesado',
        'pedido_id',
        'localidade_id',
        'cliente_id',
        'producto_id',
        'pedido_dia',
        'codigo_pedido',
        'compra_id',
        'peso_compra',
        'pv',
        'descuento_compra',
        'precio_compra',
        'created_at',
        'updated_at'
    ];

    public function localidad()
    {
        return $this->belongsTo(Localidade::class, 'localidade_id');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }
}
