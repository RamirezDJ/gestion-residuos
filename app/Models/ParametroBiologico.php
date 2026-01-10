<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParametroBiologico extends Model
{
    use HasFactory;

    protected $table = 'parametros_biologicos';

    protected $fillable = [
        'muestreo_id',
        'coliformes_totales',
        'coliformes_fecales',
    ];

    public function muestreo()
    {
        return $this->belongsTo(Muestreo::class);
    }
}