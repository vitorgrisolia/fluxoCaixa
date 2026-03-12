<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fechamento_caixas', function (Blueprint $table) {
            $table->decimal('valor_dinheiro', 10, 2)->default(0)->after('saldo_inicial');
            $table->decimal('valor_cartao', 10, 2)->default(0)->after('valor_dinheiro');
            $table->decimal('valor_pix', 10, 2)->default(0)->after('valor_cartao');
            $table->decimal('valor_outros', 10, 2)->default(0)->after('valor_pix');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fechamento_caixas', function (Blueprint $table) {
            $table->dropColumn(['valor_dinheiro', 'valor_cartao', 'valor_pix', 'valor_outros']);
        });
    }
};
