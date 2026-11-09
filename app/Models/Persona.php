<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $table = 'personas';

    protected $fillable = [
        'carnet',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'fotografia'
    ];

    public function usuario()
    {
        return $this->hasOne(User::class, 'persona_id');
    }
}
