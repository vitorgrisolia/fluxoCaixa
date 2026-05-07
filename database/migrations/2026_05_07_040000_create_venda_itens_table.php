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
        Schema::create('venda_itens', function (Blueprint $table) {
            $table->bigIncrements('id_venda_item');
            $table->bigInteger('id_compra');
            $table->bigInteger('id_produto')->nullable();
            $table->string('nome_produto');
            $table->string('lote', 100)->nullable();
            $table->string('codigo_barras', 100)->nullable();
            $table->unsignedInteger('quantidade');
            $table->decimal('valor_unitario_venda', 10, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->index('id_compra');
            $table->index('id_produto');
            $table->index('codigo_barras');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('venda_itens');
    }
};

