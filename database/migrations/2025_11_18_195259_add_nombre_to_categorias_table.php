<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Añadimos la columna 'nombre' a la tabla 'categorias'
        Schema::table('categorias', function (Blueprint $table) {
            // La columna debe ser string, única, y no puede ser nula (por tu validación 'required')
            $table->string('nombre')->unique();
        });
    }

    public function down(): void
    {
        // En caso de rollback, eliminamos la columna 'nombre'
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropColumn('nombre');
        });
    }
};
