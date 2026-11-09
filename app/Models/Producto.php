<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'n_caja',
    ];

    public function localidades_productos()
    {
        return $this->hasMany(LocalidadesProductos::class, 'producto_id');
    }

    public function pedidos_productos()
    {
        return $this->hasMany(PedidoProductos::class, 'producto_id');
    }
}
