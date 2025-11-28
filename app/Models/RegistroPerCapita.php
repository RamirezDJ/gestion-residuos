<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroPerCapita extends Model
{
    use HasFactory;

    // Estos son los campos que permitimos guardar en la base de datos
    protected $fillable = [
        'instituto_id',
        'fecha',
        'visitantes',
        'trabajadores',
        'kilos_residuos',
        'per_capita',
    ];

    // Relación con el modelo Institutos (Un registro pertenece a un instituto)
    public function instituto()
    {
        return $this->belongsTo(Institutos::class);
    }
}
