<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndiceCalidadAgua extends Model
{
    use HasFactory;

    
    protected $table = 'indices_calidad_agua';

    protected $fillable = [
        'punto_muestreo',
        'fecha_muestreo',
        'hora_muestreo',
        'numero_muestra',
        'responsable_id',
        'conductividad_electrica',
        'turbidez',
        'temperatura',
        'oxigeno_disuelto_ppm',
        'oxigeno_disuelto_porcentaje',
        'solidos_disueltos',
        'ph',
        'dureza',
        'nitratos',
        'nitritos',
        'dqo',
        'observaciones',
    ];

    
    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }
}
