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
        Schema::create('produtos', function (Blueprint $table) {
            $table->bigIncrements('id_produto');
            $table->string('nome');
            $table->unsignedInteger('quantidade')->default(0);
            $table->enum('tipo_quantidade', ['caixa', 'unidade'])->default('unidade');
            $table->date('validade');
            $table->decimal('preco_compra', 10, 2)->default(0);
            $table->decimal('preco_venda', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('produtos');
    }
};
