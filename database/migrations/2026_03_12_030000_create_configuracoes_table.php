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
        Schema::create('configuracoes', function (Blueprint $table) {
            $table->bigIncrements('id_configuracao');
            $table->string('nome_sistema');
            $table->string('nome_empresa')->nullable();
            $table->string('email_contato')->nullable();
            $table->string('telefone_contato')->nullable();
            $table->string('endereco')->nullable();
            $table->string('moeda', 10)->default('BRL');
            $table->string('mensagem_rodape')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configuracoes');
    }
};
