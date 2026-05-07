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
        Schema::create('user_permissoes', function (Blueprint $table) {
            $table->bigIncrements('id_permissao');
            $table->unsignedBigInteger('id_user');
            $table->string('chave_permissao', 120);
            $table->boolean('permitido')->default(true);
            $table->timestamps();

            $table->unique(['id_user', 'chave_permissao'], 'uk_user_permissoes_usuario_chave');
            $table->index('chave_permissao');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_permissoes');
    }
};
