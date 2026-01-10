<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Muestreo extends Model
{
    use HasFactory;

    protected $fillable = [
        'instituto_id', 
        'punto_muestreo',
        'fecha_muestreo',
        'hora_muestreo',
        'numero_muestra',
        'responsable_id',
        'observaciones',
    ];

    public function fisicos()
    {
        return $this->hasOne(ParametroFisico::class);
    }
    public function quimicos()
    {
        return $this->hasOne(ParametroQuimico::class);
    }
    public function biologicos()
    {
        return $this->hasOne(ParametroBiologico::class);
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }
}
