<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('muestreos', function (Blueprint $table) {
            $table->id();
            $table->string('punto_muestreo'); 
            $table->date('fecha_muestreo');
            $table->time('hora_muestreo')->nullable();
            $table->integer('numero_muestra')->default(1); 
            $table->foreignId('responsable_id'); 
            $table->text('observaciones')->nullable();
            $table->timestamps(); 
        });

        Schema::create('parametros_fisicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('muestreo_id')->constrained('muestreos')->onDelete('cascade');
            $table->decimal('conductividad_electrica', 10, 2)->nullable(); 
            $table->decimal('turbidez', 8, 2)->nullable(); 
            $table->decimal('temperatura', 5, 2)->nullable(); 
            $table->decimal('solidos_disueltos', 10, 2)->nullable(); 
            $table->timestamps();
        });

        Schema::create('parametros_quimicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('muestreo_id')->constrained('muestreos')->onDelete('cascade');
            $table->decimal('ph', 5, 2)->nullable(); 
            $table->decimal('dureza', 8, 2)->nullable(); 
            $table->decimal('nitratos', 8, 2)->nullable(); 
            $table->decimal('nitritos', 8, 2)->nullable(); 
            $table->decimal('dqo', 8, 2)->nullable(); 
            $table->decimal('oxigeno_disuelto_ppm', 8, 2)->nullable(); 
            $table->decimal('oxigeno_disuelto_porcentaje', 8, 2)->nullable(); 
            $table->timestamps();
        });

        if (Schema::hasTable('indices_calidad_agua')) {
            $oldData = DB::table('indices_calidad_agua')->get();

            foreach ($oldData as $row) {
                $newMuestreoId = DB::table('muestreos')->insertGetId([
                    'punto_muestreo' => $row->punto_muestreo,
                    'fecha_muestreo' => $row->fecha_muestreo,
                    'hora_muestreo'  => $row->hora_muestreo,
                    'numero_muestra' => $row->numero_muestra,
                    'responsable_id' => $row->responsable_id,
                    'observaciones'  => $row->observaciones,
                    'created_at'     => $row->created_at,
                    'updated_at'     => $row->updated_at,
                ]);

                DB::table('parametros_fisicos')->insert([
                    'muestreo_id'             => $newMuestreoId,
                    'conductividad_electrica' => $row->conductividad_electrica,
                    'turbidez'                => $row->turbidez,
                    'temperatura'             => $row->temperatura,
                    'solidos_disueltos'       => $row->solidos_disueltos,
                    'created_at'              => $row->created_at,
                    'updated_at'              => $row->updated_at,
                ]);

                DB::table('parametros_quimicos')->insert([
                    'muestreo_id'                 => $newMuestreoId,
                    'ph'                          => $row->ph,
                    'dureza'                      => $row->dureza,
                    'nitratos'                    => $row->nitratos,
                    'nitritos'                    => $row->nitritos,
                    'dqo'                         => $row->dqo,
                    'oxigeno_disuelto_ppm'        => $row->oxigeno_disuelto_ppm,
                    'oxigeno_disuelto_porcentaje' => $row->oxigeno_disuelto_porcentaje,
                    'created_at'                  => $row->created_at,
                    'updated_at'                  => $row->updated_at,
                ]);
            }
            
            Schema::rename('indices_calidad_agua', 'indices_calidad_agua_backup');

        }
    }

    public function down(): void
    {
        Schema::dropIfExists('parametros_quimicos');
        Schema::dropIfExists('parametros_fisicos');
        Schema::dropIfExists('muestreos');
    }
};