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
        Schema::create('fechamento_caixas', function (Blueprint $table) {
            $table->bigIncrements('id_fechamento');
            $table->bigInteger('id_user');
            $table->date('data_fechamento');
            $table->decimal('saldo_inicial', 10, 2)->default(0);
            $table->decimal('total_entradas', 10, 2)->default(0);
            $table->decimal('total_saidas', 10, 2)->default(0);
            $table->decimal('saldo_final', 10, 2)->default(0);
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->index('id_user');
            $table->index('data_fechamento');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fechamento_caixas');
    }
};
