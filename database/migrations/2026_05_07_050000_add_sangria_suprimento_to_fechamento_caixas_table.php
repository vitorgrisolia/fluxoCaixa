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
        Schema::table('fechamento_caixas', function (Blueprint $table) {
            $table->decimal('total_sangria', 10, 2)->default(0);
            $table->decimal('total_suprimento', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fechamento_caixas', function (Blueprint $table) {
            $table->dropColumn(['total_sangria', 'total_suprimento']);
        });
    }
};

