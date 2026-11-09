<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropForeign(['pedido_id']); // Elimina la clave foránea primero
            $table->dropColumn('pedido_id'); // Luego elimina la columna
        });
    }

    public function down()
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->unsignedBigInteger('pedido_id')->nullable();
            $table->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('set null');
        });
    }
};
