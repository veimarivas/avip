<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'recibo',
        'pago_bs',
        'descuento_bs',
        'tipo',
        'cliente_id',
        'user_id'
    ];

    // Y agrega un accessor para calcular pago_total si no existe en la base de datos:
    public function getPagoTotalAttribute()
    {
        return $this->pago_bs - $this->descuento_bs;
    }

    // Función para generar el número de recibo automáticamente
    public static function generarRecibo()
    {
        $config = [
            'table' => 'pagos',
            'field' => 'recibo',
            'length' => 10,
            'prefix' => 'PA-',
            'reset_on_prefix_change' => true
        ];

        return IdGenerator::generate($config);
    }

    // Hook para generar recibo automáticamente antes de crear
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->recibo)) {
                $model->recibo = self::generarRecibo();
            }
        });
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    // Un pago puede estar asociado a múltiples pedidos
    public function pedidos()
    {
        return $this->belongsToMany(Pedido::class, 'pago_pedido', 'pago_id', 'pedido_id')
            ->withPivot('monto_asignado', 'descuento_asignado')
            ->withTimestamps();
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles()
    {
        return $this->hasMany(Detalle::class, 'pago_id');
    }
}
