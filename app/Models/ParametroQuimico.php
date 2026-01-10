<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParametroQuimico extends Model
{
    use HasFactory;

    protected $table = 'parametros_quimicos';

    protected $fillable = [
        'muestreo_id',
        'ph',
        'oxigeno_disuelto_ppm',
        'dbo',
        'dqo',
        'nitratos',
        'nitritos',
        'fosfatos',
        'cloro_libre'
    ];

    public function muestreo()
    {
        return $this->belongsTo(Muestreo::class);
    }
}
