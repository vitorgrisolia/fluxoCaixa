<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('documentos_fiscais', function (Blueprint $table) {
            $table->uuid('idempotencia')->nullable()->unique()->after('id_compra');
            $table->unsignedBigInteger('solicitado_por')->nullable()->after('idempotencia');
            $table->timestamp('solicitado_em')->nullable()->after('status');
            $table->unsignedInteger('tentativas')->default(0)->after('solicitado_em');
            $table->timestamp('proxima_tentativa_em')->nullable()->after('tentativas');
            $table->unique(['id_compra', 'modelo', 'ambiente'], 'documento_compra_modelo_unique');
        });
    }

    public function down()
    {
        Schema::table('documentos_fiscais', function (Blueprint $table) {
            $table->dropUnique('documento_compra_modelo_unique');
            $table->dropUnique(['idempotencia']);
            $table->dropColumn(['idempotencia', 'solicitado_por', 'solicitado_em', 'tentativas', 'proxima_tentativa_em']);
        });
    }
};
