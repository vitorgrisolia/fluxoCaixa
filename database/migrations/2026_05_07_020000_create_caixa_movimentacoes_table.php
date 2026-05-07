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
        Schema::create('caixa_movimentacoes', function (Blueprint $table) {
            $table->bigIncrements('id_movimentacao_caixa');
            $table->unsignedBigInteger('id_turno');
            $table->bigInteger('id_user');
            $table->enum('tipo_movimentacao', ['sangria', 'suprimento']);
            $table->decimal('valor', 10, 2);
            $table->string('observacao', 500)->nullable();
            $table->dateTime('data_movimentacao');
            $table->timestamps();

            $table->index('id_turno');
            $table->index('id_user');
            $table->index('tipo_movimentacao');
            $table->index('data_movimentacao');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('caixa_movimentacoes');
    }
};

