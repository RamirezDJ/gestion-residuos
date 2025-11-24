<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('gen_subproductos', function (Blueprint $table) {
            // Primero intentamos borrar la llave foránea por si acaso se creó
            // Usamos un array con el nombre de la columna
            try {
                $table->dropForeign(['zonas_areas_id']);
            } catch (\Exception $e) {
                // Si no existe la llave foránea, no pasa nada, continuamos
            }

            // Borramos la columna que sobra
            $table->dropColumn('zonas_areas_id');
        });
    }

    public function down()
    {
        // Si revertimos, la volvemos a crear (aunque no deberíamos necesitarlo)
        Schema::table('gen_subproductos', function (Blueprint $table) {
            $table->unsignedBigInteger('zonas_areas_id')->nullable();
        });
    }
};
