<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Localidade extends Model
{
    protected $table = 'localidades';

    protected $fillable = [
        'nombre',
        'color'
    ];

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'localidade_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'localidade_id');
    }

    public function localidades_productos()
    {
        return $this->hasMany(LocalidadesProductos::class, 'localidade_id');
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'localidades_productos', 'localidade_id', 'producto_id')
            ->withPivot('precio')
            ->withTimestamps();
    }

    public function pedidos_productos()
    {
        return $this->hasMany(PedidoProductos::class, 'localidade_id');
    }
}
