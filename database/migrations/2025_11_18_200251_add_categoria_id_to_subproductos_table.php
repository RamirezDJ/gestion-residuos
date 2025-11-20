<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subproductos', function (Blueprint $table) {
            // Añade la columna categoria_id como clave foránea
            $table->foreignId('categoria_id')
                ->nullable() // Opcional: Permite que un subproducto no tenga categoría por ahora
                ->constrained('categorias') // Indica que la clave apunta a la tabla 'categorias'
                ->after('id'); // Colócala después del ID para orden
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subproductos', function (Blueprint $table) {
            // Elimina primero la clave foránea para evitar errores
            $table->dropConstrainedForeignId('categoria_id');
            // Luego elimina la columna
            $table->dropColumn('categoria_id');
        });
    }
};
