<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pago_pedido', function (Blueprint $table) {
            // Si la tabla no tiene timestamps, agrégalos también
            if (!Schema::hasColumn('pago_pedido', 'created_at')) {
                $table->timestamps();
            }

            // Agregar las columnas que faltan
            $table->decimal('monto_asignado', 10, 2)->default(0)->after('pago_id');
            $table->decimal('descuento_asignado', 10, 2)->default(0)->after('monto_asignado');

            // Opcional: índice para mejorar rendimiento
            $table->index(['pedido_id', 'pago_id']);
        });
    }

    public function down()
    {
        Schema::table('pago_pedido', function (Blueprint $table) {
            $table->dropColumn(['monto_asignado', 'descuento_asignado']);
            $table->dropTimestamps();
        });
    }
};
