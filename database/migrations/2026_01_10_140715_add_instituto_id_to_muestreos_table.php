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
        Schema::table('muestreos', function (Blueprint $table) {
            $table->foreignId('instituto_id')
                ->nullable() 
                ->after('id')
                ->constrained('institutos') 
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('muestreos', function (Blueprint $table) {
            //
        });
    }
};
