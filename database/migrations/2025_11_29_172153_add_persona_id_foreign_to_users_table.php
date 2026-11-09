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
        Schema::table('users', function (Blueprint $table) {
            // Agrega la columna persona_id si no existe
            $table->unsignedBigInteger('persona_id')->nullable()->after('id');

            // Agrega la llave foránea
            $table->foreign('persona_id')
                ->references('id')
                ->on('personas')
                ->onDelete('set null'); // Opcional: define qué pasa si se elimina el registro en 'personas'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Elimina la llave foránea primero
            $table->dropForeign(['persona_id']);
            // Luego elimina la columna
            $table->dropColumn('persona_id');
        });
    }
};
