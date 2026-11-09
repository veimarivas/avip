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
        Schema::create('pedido_productos', function (Blueprint $table) {
            $table->id();
            $table->integer('n_cajas')->default(0);
            $table->decimal('peso', 10, 2)->default(0);
            $table->integer('peso_pedido')->default(0);
            $table->integer('tara')->default(0);
            $table->decimal('peso_real', 10, 2)->default(0);
            $table->decimal('precio', 10, 2)->default(0);
            $table->decimal('costo_total', 10, 2)->default(0);
            $table->enum('realizado', [0, 1])->default(0);
            $table->enum('procesado', [0, 1])->default(0);

            $table->unsignedBigInteger('pedido_id')->nullable();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('localidade_id')->nullable();
            $table->unsignedBigInteger('producto_id')->nullable();
            $table->unsignedBigInteger('compra_id')->nullable();

            $table->integer('pedido_dia')->default(0);
            $table->integer('codigo_pedido')->default(0);
            $table->decimal('peso_compra', 10, 2)->default(0);
            $table->decimal('pv', 10, 2)->default(0);
            $table->decimal('descuento_compra', 10, 2)->default(0);
            $table->decimal('precio_compra', 10, 2)->default(0);
            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('clientes');
            $table->foreign('localidade_id')->references('id')->on('localidades');
            $table->foreign('producto_id')->references('id')->on('productos');
            $table->foreign('compra_id')->references('id')->on('compras');
            $table->foreign('pedido_id')->references('id')->on('pedidos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_productos');
    }
};
