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
        Schema::table('gen_semanals', function (Blueprint $table) {
            // 1. COMENTAMOS O BORRAMOS ESTA LÍNEA (Porque la columna ya no existe en tu BD)
            // $table->dropColumn('subproducto_id'); 

            // 2. DEJAMOS ESTA LÍNEA (Para crear la que falta)
            $table->foreignId('categoria_id')->constrained('categorias');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
