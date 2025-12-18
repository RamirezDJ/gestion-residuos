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
        Schema::create('indices_calidad_agua', function (Blueprint $table) {
            $table->id();

            
            $table->string('punto_muestreo'); 
            $table->date('fecha_muestreo');
            $table->time('hora_muestreo')->nullable();
            $table->integer('numero_muestra')->default(1); 

            
            $table->foreignId('responsable_id')->constrained('users');

            
            
            $table->decimal('conductividad_electrica', 10, 2)->nullable(); 
            $table->decimal('turbidez', 8, 2)->nullable(); 
            $table->decimal('temperatura', 5, 2)->nullable(); 
            $table->decimal('oxigeno_disuelto_ppm', 8, 2)->nullable(); 
            $table->decimal('oxigeno_disuelto_porcentaje', 8, 2)->nullable(); 
            $table->decimal('solidos_disueltos', 10, 2)->nullable(); 

            
            $table->decimal('ph', 5, 2)->nullable(); 
            $table->decimal('dureza', 8, 2)->nullable(); 
            $table->decimal('nitratos', 8, 2)->nullable(); 
            $table->decimal('nitritos', 8, 2)->nullable(); 
            $table->decimal('dqo', 8, 2)->nullable(); 

            $table->text('observaciones')->nullable();

            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indice_calidad_aguas');
    }
};
