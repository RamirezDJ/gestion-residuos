<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $fillable = ['nombre'];

    // Relación: Una categoría tiene muchos subproductos
    public function subproductos()
    {
        return $this->hasMany(Subproducto::class);
    }
}
