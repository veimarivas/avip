<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Detalle extends Model
{
    protected $table = 'detalles';

    protected $fillable = [
        'pago_bs',
        'descuentos_bs',
        'pago_id',
        'tipo'
    ];

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'pago_id');
    }
}
