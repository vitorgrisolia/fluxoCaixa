<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->decimal('aliquota_icms', 7, 4)->default(0);
            $table->decimal('aliquota_pis', 7, 4)->default(0);
            $table->decimal('aliquota_cofins', 7, 4)->default(0);
            $table->decimal('aliquota_ipi', 7, 4)->default(0);
            $table->decimal('aliquota_fcp', 7, 4)->default(0);
            $table->string('cst_pis', 2)->nullable();
            $table->string('cst_cofins', 2)->nullable();
            $table->string('cst_ipi', 2)->nullable();
            $table->string('unidade_tributavel', 10)->default('UN');
        });

        Schema::table('configuracoes', function (Blueprint $table) {
            $table->string('responsavel_tecnico_nome')->nullable();
            $table->string('responsavel_tecnico_cnpj', 14)->nullable();
            $table->string('responsavel_tecnico_email')->nullable();
            $table->string('responsavel_tecnico_telefone', 30)->nullable();
            $table->string('provedor_fiscal')->default('homologacao_local');
        });

        Schema::table('fechamento_caixas', function (Blueprint $table) {
            $table->string('status', 20)->default('fechado')->index();
            $table->unsignedBigInteger('reaberto_por')->nullable();
            $table->timestamp('reaberto_em')->nullable();
            $table->string('motivo_reabertura', 500)->nullable();
        });

        Schema::create('documento_fiscal_eventos', function (Blueprint $table) {
            $table->bigIncrements('id_evento');
            $table->unsignedBigInteger('id_documento_fiscal');
            $table->string('tipo', 30);
            $table->string('status', 30)->default('pendente');
            $table->string('protocolo', 50)->nullable();
            $table->string('codigo_status', 10)->nullable();
            $table->text('motivo')->nullable();
            $table->longText('xml')->nullable();
            $table->json('resposta')->nullable();
            $table->timestamps();
            $table->index(['id_documento_fiscal', 'tipo']);
        });

        Schema::create('sequencias_fiscais', function (Blueprint $table) {
            $table->bigIncrements('id_sequencia');
            $table->string('modelo', 2);
            $table->unsignedInteger('serie');
            $table->string('ambiente', 15);
            $table->unsignedInteger('proximo_numero')->default(1);
            $table->timestamps();
            $table->unique(['modelo', 'serie', 'ambiente']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sequencias_fiscais');
        Schema::dropIfExists('documento_fiscal_eventos');

        Schema::table('fechamento_caixas', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'reaberto_por', 'reaberto_em', 'motivo_reabertura']);
        });

        Schema::table('configuracoes', function (Blueprint $table) {
            $table->dropColumn(['responsavel_tecnico_nome', 'responsavel_tecnico_cnpj', 'responsavel_tecnico_email', 'responsavel_tecnico_telefone', 'provedor_fiscal']);
        });

        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn(['aliquota_icms', 'aliquota_pis', 'aliquota_cofins', 'aliquota_ipi', 'aliquota_fcp', 'cst_pis', 'cst_cofins', 'cst_ipi', 'unidade_tributavel']);
        });
    }
};
