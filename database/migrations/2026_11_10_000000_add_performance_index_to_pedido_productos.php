<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX = 'pedido_productos_localidad_producto_fecha_index';

    /**
     * Índice compuesto para que la actualización masiva de precios no
     * recorra la tabla completa de pedido_productos.
     */
    public function up(): void
    {
        if (!Schema::hasTable('pedido_productos')) {
            return;
        }

        $existe = collect(Schema::getIndexes('pedido_productos'))
            ->contains(fn ($indice) => $indice['name'] === self::INDEX);

        if ($existe) {
            return;
        }

        DB::statement(
            'ALTER TABLE pedido_productos ADD INDEX ' . self::INDEX
            . ' (localidade_id, producto_id, created_at), ALGORITHM=INPLACE, LOCK=NONE'
        );
    }

    public function down(): void
    {
        if (!Schema::hasTable('pedido_productos')) {
            return;
        }

        $existe = collect(Schema::getIndexes('pedido_productos'))
            ->contains(fn ($indice) => $indice['name'] === self::INDEX);

        if (!$existe) {
            return;
        }

        Schema::table('pedido_productos', function (Blueprint $table) {
            $table->dropIndex(self::INDEX);
        });
    }
};
