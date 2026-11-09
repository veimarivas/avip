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
        Schema::create('detalles', function (Blueprint $table) {
            $table->id();
            $table->decimal('pago_bs', 10, 2)->default(0);
            $table->decimal('descuentos_bs', 10, 2)->default(0);
            $table->unsignedBigInteger('pago_id')->nullable();
            $table->string('tipo');
            $table->timestamps();

            $table->foreign('pago_id')->references('id')->on('pagos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles');
    }
};
