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
        Schema::table('venda_itens', function (Blueprint $table) {
            $table->decimal('valor_unitario_custo', 10, 2)->default(0)->after('valor_unitario_venda');
            $table->decimal('subtotal_custo', 12, 2)->default(0)->after('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('venda_itens', function (Blueprint $table) {
            $table->dropColumn(['valor_unitario_custo', 'subtotal_custo']);
        });
    }
};

