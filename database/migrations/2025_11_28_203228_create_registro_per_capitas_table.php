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
        Schema::create('registro_per_capitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instituto_id')->constrained('institutos')->onDelete('cascade');
            $table->date('fecha');
            $table->integer('visitantes');
            $table->integer('trabajadores');
            $table->decimal('kilos_residuos', 8, 2); // Hasta 999,999.99
            $table->decimal('per_capita', 8, 4); // Guardamos el cálculo con 4 decimales
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registro_per_capitas');
    }
};
