<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. PARAMETROS FISICOS (Incluyendo color, olor, sabor)
        if (!Schema::hasTable('parametros_fisicos')) {
            Schema::create('parametros_fisicos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('muestreo_id')->constrained('muestreos')->onDelete('cascade');

                // Campos numéricos
                $table->decimal('temperatura', 8, 2)->nullable();
                $table->decimal('turbidez', 8, 2)->nullable();
                $table->decimal('conductividad_electrica', 10, 2)->nullable();
                $table->decimal('solidos_disueltos', 10, 2)->nullable();
                $table->decimal('solidos_suspension', 10, 2)->nullable();

                // Campos de texto (Los que daban problemas)
                $table->string('color')->nullable();
                $table->string('olor')->nullable();
                $table->string('sabor')->nullable();

                $table->timestamps();
            });
        }

        // 2. PARAMETROS QUIMICOS
        if (!Schema::hasTable('parametros_quimicos')) {
            Schema::create('parametros_quimicos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('muestreo_id')->constrained('muestreos')->onDelete('cascade');

                $table->decimal('ph', 5, 2)->nullable();
                $table->decimal('oxigeno_disuelto_ppm', 8, 2)->nullable();
                $table->decimal('dbo', 8, 2)->nullable();
                $table->decimal('dqo', 8, 2)->nullable();
                $table->decimal('nitratos', 8, 2)->nullable();
                $table->decimal('nitritos', 8, 2)->nullable();
                $table->decimal('fosfatos', 8, 2)->nullable();
                $table->decimal('cloro_libre', 8, 2)->nullable();

                $table->timestamps();
            });
        }

        // 3. PARAMETROS BIOLOGICOS
        if (!Schema::hasTable('parametros_biologicos')) {
            Schema::create('parametros_biologicos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('muestreo_id')->constrained('muestreos')->onDelete('cascade');

                $table->decimal('coliformes_totales', 10, 2)->nullable();
                $table->decimal('coliformes_fecales', 10, 2)->nullable();

                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('parametros_biologicos');
        Schema::dropIfExists('parametros_quimicos');
        Schema::dropIfExists('parametros_fisicos');
    }
};
