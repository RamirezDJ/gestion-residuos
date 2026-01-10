<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParametroFisico extends Model
{
    use HasFactory;

    protected $table = 'parametros_fisicos'; 

    protected $fillable = [
        'muestreo_id',
        'temperatura',
        'turbidez',
        'conductividad_electrica',
        'solidos_disueltos',
        'color',
        'olor',
        'sabor',
        'solidos_suspension', 
    ];

    public function muestreo()
    {
        return $this->belongsTo(Muestreo::class);
    }
}