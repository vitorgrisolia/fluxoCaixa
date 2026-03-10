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
        Schema::create('movimentacao_produtos', function (Blueprint $table) {
            $table->bigIncrements('id_movimentacao');
            $table->unsignedBigInteger('id_produto');
            $table->enum('tipo_movimentacao', ['entrada', 'saida']);
            $table->unsignedInteger('quantidade');
            $table->decimal('valor_unitario_venda', 10, 2)->nullable();
            $table->date('data_movimentacao');
            $table->string('observacao', 500)->nullable();
            $table->timestamps();

            $table->index('id_produto');
            $table->index('data_movimentacao');
            $table->index('tipo_movimentacao');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movimentacao_produtos');
    }
};
