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
        // Fíjate que aquí diga 'gen_subproductos' (con guion bajo)
        Schema::table('gen_subproductos', function (Blueprint $table) {
            $table->unsignedBigInteger('zona_id')->nullable()->after('instituto_id');
            $table->foreign('zona_id')->references('id')->on('zonas');
        });
    }

    public function down()
    {
        // Aquí también corrige el nombre
        Schema::table('gen_subproductos', function (Blueprint $table) {
            $table->dropForeign(['zona_id']);
            $table->dropColumn('zona_id');
        });
    }
};
