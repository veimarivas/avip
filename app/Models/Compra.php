<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $table = 'compras';

    protected $fillable = [
        'nombre'
    ];

    public function pedidos_productos()
    {
        return $this->hasMany(PedidoProductos::class, 'compra_id');
    }
}
