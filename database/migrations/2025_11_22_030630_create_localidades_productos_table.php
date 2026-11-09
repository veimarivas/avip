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
        Schema::create('localidades_productos', function (Blueprint $table) {
            $table->id();
            $table->decimal('precio', 10, 2);
            $table->unsignedBigInteger('localidade_id')->nullable();
            $table->unsignedBigInteger('producto_id')->nullable();
            $table->timestamps();

            $table->foreign('localidade_id')->references('id')->on('localidades');
            $table->foreign('producto_id')->references('id')->on('productos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('localidades_productos');
    }
};
