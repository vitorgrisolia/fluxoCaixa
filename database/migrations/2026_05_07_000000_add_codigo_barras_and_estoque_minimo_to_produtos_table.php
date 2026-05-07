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
        Schema::table('produtos', function (Blueprint $table) {
            $table->string('codigo_barras', 100)->nullable();
            $table->unsignedInteger('estoque_minimo')->default(0);

            $table->index('codigo_barras');
            $table->index('estoque_minimo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->dropIndex(['codigo_barras']);
            $table->dropIndex(['estoque_minimo']);
            $table->dropColumn(['codigo_barras', 'estoque_minimo']);
        });
    }
};

