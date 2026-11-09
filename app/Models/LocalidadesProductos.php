<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocalidadesProductos extends Model
{
    protected $table = 'localidades_productos';

    protected $fillable = [
        'precio',
        'localidade_id',
        'producto_id'
    ];

    public function localidad()
    {
        return $this->belongsTo(Localidade::class, 'localidade_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
