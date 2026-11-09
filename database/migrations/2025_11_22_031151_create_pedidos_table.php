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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->integer('platillo')->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('descuento_bs', 10, 2)->default(0);
            $table->decimal('pendiente', 10, 2)->default(0);
            $table->enum('pagado', [0, 1])->default(0);
            $table->enum('estado', [0, 1])->default(0);
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('localidade_id')->nullable();
            $table->integer('pedido_dia')->default(0);
            $table->integer('codigo_semana');
            $table->integer('codigo_dia');
            $table->integer('dia');
            $table->integer('codigo');
            $table->decimal('total_ingreso', 10, 2)->default(0);
            $table->integer('generado')->default(0);
            $table->integer('deshabilitado')->default(0);
            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('clientes');
            $table->foreign('localidade_id')->references('id')->on('localidades');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
