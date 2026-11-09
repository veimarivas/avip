<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pago_pedido', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pago_id');
            $table->unsignedBigInteger('pedido_id');
            $table->decimal('monto', 10, 2); // Opcional: si necesitas registrar el monto asociado a cada relación
            $table->timestamps();

            // Claves foráneas
            $table->foreign('pago_id')->references('id')->on('pagos')->onDelete('cascade');
            $table->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('cascade');

            // Evitar duplicados
            $table->unique(['pago_id', 'pedido_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pago_pedido');
    }
};
