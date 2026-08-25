<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('clientes')) {
            Schema::create('clientes', function (Blueprint $table) {
            $table->bigIncrements('id_cliente');
            $table->string('nome');
            $table->string('cpf_cnpj', 14)->nullable()->unique();
            $table->string('inscricao_estadual', 20)->nullable();
            $table->string('indicador_ie', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('telefone', 30)->nullable();
            $table->string('logradouro')->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('codigo_municipio', 7)->nullable();
            $table->string('municipio')->nullable();
            $table->char('uf', 2)->nullable();
            $table->string('cep', 8)->nullable();
            $table->timestamps();
            $table->softDeletes();
            });
        }

        $this->addColumn('compras', 'id_cliente', function (Blueprint $table) {
            $table->unsignedBigInteger('id_cliente')->nullable()->after('id_user')->index();
        });
        $this->addColumn('compras', 'status', function (Blueprint $table) {
            $table->string('status', 20)->default('concluida')->after('parcelas')->index();
        });
        $this->addColumn('compras', 'cancelada_em', function (Blueprint $table) {
            $table->timestamp('cancelada_em')->nullable()->after('status');
        });
        $this->addColumn('compras', 'motivo_cancelamento', function (Blueprint $table) {
            $table->string('motivo_cancelamento', 500)->nullable()->after('cancelada_em');
        });

        if (! Schema::hasTable('compra_itens')) {
            Schema::create('compra_itens', function (Blueprint $table) {
            $table->bigIncrements('id_compra_item');
            $table->unsignedBigInteger('id_compra');
            $table->unsignedBigInteger('id_produto')->nullable();
            $table->string('produto_nome');
            $table->string('produto_codigo', 60)->nullable();
            $table->string('lote', 100)->nullable();
            $table->decimal('quantidade', 12, 3);
            $table->string('unidade', 10)->default('UN');
            $table->decimal('valor_unitario', 12, 2);
            $table->decimal('desconto', 12, 2)->default(0);
            $table->decimal('valor_total', 12, 2);
            $table->string('ncm', 8)->nullable();
            $table->string('cest', 7)->nullable();
            $table->string('cfop', 4)->nullable();
            $table->string('cst_csosn', 4)->nullable();
            $table->string('origem_mercadoria', 1)->nullable();
            $table->string('gtin', 14)->nullable();
            $table->timestamps();

            $table->index('id_compra');
            $table->index('id_produto');
            });
        }

        $this->addColumn('produtos', 'codigo_barras', function (Blueprint $table) {
            $table->string('codigo_barras', 14)->nullable()->unique()->after('lote');
        });
        $this->addColumn('produtos', 'ncm', fn (Blueprint $table) => $table->string('ncm', 8)->nullable()->after('preco_venda'));
        $this->addColumn('produtos', 'cest', fn (Blueprint $table) => $table->string('cest', 7)->nullable()->after('ncm'));
        $this->addColumn('produtos', 'cfop', fn (Blueprint $table) => $table->string('cfop', 4)->nullable()->after('cest'));
        $this->addColumn('produtos', 'cst_csosn', fn (Blueprint $table) => $table->string('cst_csosn', 4)->nullable()->after('cfop'));
        $this->addColumn('produtos', 'origem_mercadoria', fn (Blueprint $table) => $table->string('origem_mercadoria', 1)->nullable()->after('cst_csosn'));
        $this->addColumn('produtos', 'unidade_comercial', fn (Blueprint $table) => $table->string('unidade_comercial', 10)->default('UN')->after('origem_mercadoria'));

        $columns = [
            'razao_social' => fn (Blueprint $table) => $table->string('razao_social')->nullable(),
            'cnpj' => fn (Blueprint $table) => $table->string('cnpj', 14)->nullable(),
            'inscricao_estadual' => fn (Blueprint $table) => $table->string('inscricao_estadual', 20)->nullable(),
            'regime_tributario' => fn (Blueprint $table) => $table->string('regime_tributario', 2)->nullable(),
            'cnae' => fn (Blueprint $table) => $table->string('cnae', 7)->nullable(),
            'codigo_municipio' => fn (Blueprint $table) => $table->string('codigo_municipio', 7)->nullable(),
            'municipio' => fn (Blueprint $table) => $table->string('municipio')->nullable(),
            'uf' => fn (Blueprint $table) => $table->char('uf', 2)->nullable(),
            'cep' => fn (Blueprint $table) => $table->string('cep', 8)->nullable(),
            'ambiente_fiscal' => fn (Blueprint $table) => $table->string('ambiente_fiscal', 15)->default('homologacao'),
            'serie_nfe' => fn (Blueprint $table) => $table->unsignedInteger('serie_nfe')->default(1),
            'proximo_numero_nfe' => fn (Blueprint $table) => $table->unsignedInteger('proximo_numero_nfe')->default(1),
            'serie_nfce' => fn (Blueprint $table) => $table->unsignedInteger('serie_nfce')->default(1),
            'proximo_numero_nfce' => fn (Blueprint $table) => $table->unsignedInteger('proximo_numero_nfce')->default(1),
            'csc_id' => fn (Blueprint $table) => $table->string('csc_id')->nullable(),
            'csc_token' => fn (Blueprint $table) => $table->text('csc_token')->nullable(),
        ];

        foreach ($columns as $name => $definition) {
            $this->addColumn('configuracoes', $name, $definition);
        }

        if (! Schema::hasTable('documentos_fiscais')) {
            Schema::create('documentos_fiscais', function (Blueprint $table) {
            $table->bigIncrements('id_documento_fiscal');
            $table->unsignedBigInteger('id_compra');
            $table->string('modelo', 2);
            $table->unsignedInteger('serie');
            $table->unsignedInteger('numero');
            $table->string('ambiente', 15)->default('homologacao');
            $table->string('status', 30)->default('pendente');
            $table->string('chave_acesso', 44)->nullable()->unique();
            $table->string('protocolo', 30)->nullable();
            $table->string('codigo_status', 10)->nullable();
            $table->text('motivo_status')->nullable();
            $table->longText('xml_envio')->nullable();
            $table->longText('xml_autorizado')->nullable();
            $table->timestamp('autorizado_em')->nullable();
            $table->timestamp('cancelado_em')->nullable();
            $table->timestamps();

            $table->unique(['modelo', 'serie', 'numero', 'ambiente'], 'documento_fiscal_numeracao_unique');
            $table->index('id_compra');
            $table->index('status');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('documentos_fiscais');

        Schema::table('configuracoes', function (Blueprint $table) {
            $table->dropColumn([
                'razao_social', 'cnpj', 'inscricao_estadual', 'regime_tributario', 'cnae',
                'codigo_municipio', 'municipio', 'uf', 'cep', 'ambiente_fiscal', 'serie_nfe',
                'proximo_numero_nfe', 'serie_nfce', 'proximo_numero_nfce', 'csc_id', 'csc_token',
            ]);
        });

        Schema::table('produtos', function (Blueprint $table) {
            $table->dropUnique(['codigo_barras']);
            $table->dropColumn([
                'codigo_barras', 'ncm', 'cest', 'cfop', 'cst_csosn',
                'origem_mercadoria', 'unidade_comercial',
            ]);
        });

        Schema::dropIfExists('compra_itens');

        Schema::table('compras', function (Blueprint $table) {
            $table->dropIndex(['id_cliente']);
            $table->dropIndex(['status']);
            $table->dropColumn(['id_cliente', 'status', 'cancelada_em', 'motivo_cancelamento']);
        });

        Schema::dropIfExists('clientes');
    }

    private function addColumn(string $tableName, string $columnName, callable $definition): void
    {
        if (! Schema::hasColumn($tableName, $columnName)) {
            Schema::table($tableName, $definition);
        }
    }
};
