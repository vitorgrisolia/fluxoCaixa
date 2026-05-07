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
        Schema::table('auditoria_logs', function (Blueprint $table) {
            $table->string('origem', 40)->nullable()->after('descricao');
            $table->string('entidade', 120)->nullable()->after('origem');
            $table->string('entidade_id', 120)->nullable()->after('entidade');
            $table->json('dados_antes')->nullable()->after('dados');
            $table->json('dados_depois')->nullable()->after('dados_antes');

            $table->index(['entidade', 'entidade_id'], 'idx_auditoria_entidade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('auditoria_logs', function (Blueprint $table) {
            $table->dropIndex('idx_auditoria_entidade');
            $table->dropColumn([
                'origem',
                'entidade',
                'entidade_id',
                'dados_antes',
                'dados_depois',
            ]);
        });
    }
};
