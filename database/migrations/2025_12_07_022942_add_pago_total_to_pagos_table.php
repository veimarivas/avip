<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPagoTotalToPagosTable extends Migration
{
    public function up()
    {
        Schema::table('pagos', function (Blueprint $table) {
            if (!Schema::hasColumn('pagos', 'pago_total')) {
                $table->decimal('pago_total', 10, 2)->default(0)->after('descuento_bs');
            }
            if (!Schema::hasColumn('pagos', 'cobrador')) {
                $table->string('cobrador')->nullable()->after('user_id');
            }
        });
    }

    public function down()
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn(['pago_total', 'cobrador']);
        });
    }
}
