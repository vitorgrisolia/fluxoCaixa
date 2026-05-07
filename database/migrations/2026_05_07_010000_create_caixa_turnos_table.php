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
        Schema::create('caixa_turnos', function (Blueprint $table) {
            $table->bigIncrements('id_turno');
            $table->bigInteger('id_user');
            $table->dateTime('data_abertura');
            $table->decimal('saldo_inicial', 10, 2)->default(0);
            $table->string('status', 20)->default('aberto');
            $table->dateTime('data_fechamento')->nullable();
            $table->decimal('saldo_final', 10, 2)->nullable();
            $table->text('observacoes_fechamento')->nullable();
            $table->timestamps();

            $table->index('id_user');
            $table->index('status');
            $table->index('data_abertura');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('caixa_turnos');
    }
};

