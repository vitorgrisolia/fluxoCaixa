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
        Schema::table('compras', function (Blueprint $table) {
            $table->unsignedBigInteger('id_turno')->nullable();
            $table->index('id_turno');
        });

        Schema::table('fechamento_caixas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_turno')->nullable();
            $table->index('id_turno');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->dropIndex(['id_turno']);
            $table->dropColumn('id_turno');
        });

        Schema::table('fechamento_caixas', function (Blueprint $table) {
            $table->dropIndex(['id_turno']);
            $table->dropColumn('id_turno');
        });
    }
};

