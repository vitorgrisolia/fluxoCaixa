<?php

namespace Tests\Feature;

use App\Models\Compra;
use App\Models\CompraItem;
use App\Models\ConfiguracaoSistema;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperacaoFiscalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_estorna_venda_e_devolve_estoque()
    {
        [$admin, $produto, $compra] = $this->cenarioVenda();

        $this->actingAs($admin)->post(route('compras.estornar', $compra->id_compra), [
            'motivo' => 'Cancelamento solicitado pelo cliente.',
        ])->assertRedirect();

        $this->assertDatabaseHas('compras', ['id_compra' => $compra->id_compra, 'status' => 'estornada']);
        $this->assertDatabaseHas('produtos', ['id_produto' => $produto->id_produto, 'quantidade' => 10]);
        $this->assertDatabaseHas('movimentacao_produtos', ['id_produto' => $produto->id_produto, 'tipo_movimentacao' => 'entrada', 'quantidade' => 2]);
    }

    public function test_solicitacao_fiscal_e_idempotente()
    {
        [$admin, , $compra] = $this->cenarioVenda();
        ConfiguracaoSistema::create([
            'nome_sistema' => 'Caixa', 'nome_empresa' => 'Empresa Teste', 'moeda' => 'BRL',
            'cnpj' => '12345678000199', 'regime_tributario' => '1', 'uf' => 'SP',
            'ambiente_fiscal' => 'homologacao', 'serie_nfce' => 1, 'proximo_numero_nfce' => 1,
        ]);

        $url = route('documentos-fiscais.solicitar', $compra->id_compra);
        $this->actingAs($admin)->post($url, ['modelo' => '65'])->assertRedirect();
        $this->actingAs($admin)->post($url, ['modelo' => '65'])->assertRedirect();

        $this->assertDatabaseCount('documentos_fiscais', 1);
        $this->assertDatabaseHas('documentos_fiscais', ['id_compra' => $compra->id_compra, 'modelo' => '65', 'status' => 'aguardando_integracao']);
        $this->assertDatabaseHas('sequencias_fiscais', ['modelo' => '65', 'serie' => 1, 'proximo_numero' => 2]);
    }

    private function cenarioVenda(): array
    {
        $admin = User::factory()->create(['tipo_usuario' => 'admin']);
        $produto = Produto::create([
            'nome' => 'Produto fiscal', 'quantidade' => 8, 'tipo_quantidade' => 'unidade',
            'validade' => now()->addYear(), 'preco_compra' => 5, 'preco_venda' => 10,
            'unidade_comercial' => 'UN', 'unidade_tributavel' => 'UN',
        ]);
        $compra = Compra::create([
            'id_cliente' => null, 'data_compra' => now(), 'valor_total' => 20,
            'forma_pagamento' => 'pix', 'dividir_valor' => 'nao', 'status' => 'concluida',
        ]);
        $compra->id_user = $admin->id_user;
        $compra->save();
        CompraItem::create([
            'id_compra' => $compra->id_compra, 'id_produto' => $produto->id_produto,
            'produto_nome' => $produto->nome, 'quantidade' => 2, 'unidade' => 'UN',
            'valor_unitario' => 10, 'desconto' => 0, 'valor_total' => 20,
        ]);
        return [$admin, $produto, $compra];
    }
}
