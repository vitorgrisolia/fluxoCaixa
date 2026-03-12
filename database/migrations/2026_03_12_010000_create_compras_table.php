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
        Schema::create('compras', function (Blueprint $table) {
            $table->bigIncrements('id_compra');
            $table->bigInteger('id_user');
            $table->dateTime('data_compra');
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->string('forma_pagamento', 30);
            $table->string('dividir_valor', 3)->default('nao');
            $table->unsignedTinyInteger('parcelas')->nullable();
            $table->timestamps();

            $table->index('id_user');
            $table->index('data_compra');
            $table->index('forma_pagamento');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('compras');
    }
};
