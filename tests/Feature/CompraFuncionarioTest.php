<?php

namespace Tests\Feature;

use App\Models\Produto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompraFuncionarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_finalizacao_preserva_itens_e_atualiza_estoque()
    {
        $funcionario = User::factory()->create(['tipo_usuario' => 'funcionario']);
        $produto = Produto::create([
            'nome' => 'Produto de teste',
            'lote' => 'LOTE-1',
            'codigo_barras' => '7891234567890',
            'quantidade' => 10,
            'tipo_quantidade' => 'unidade',
            'validade' => now()->addYear()->toDateString(),
            'preco_compra' => 5,
            'preco_venda' => 10,
            'ncm' => '12345678',
            'cfop' => '5102',
            'cst_csosn' => '102',
            'origem_mercadoria' => '0',
            'unidade_comercial' => 'UN',
        ]);

        $response = $this->actingAs($funcionario)
            ->withSession(['leitor_produtos' => [$produto->id_produto => 2]])
            ->post(route('leitor.finalizar.store'), [
                'forma_pagamento' => 'pix',
            ]);

        $response->assertRedirect(route('leitor.produtos'));
        $this->assertDatabaseHas('compras', [
            'id_user' => $funcionario->id_user,
            'valor_total' => 20,
            'status' => 'concluida',
        ]);
        $this->assertDatabaseHas('compra_itens', [
            'id_produto' => $produto->id_produto,
            'produto_nome' => 'Produto de teste',
            'quantidade' => 2,
            'valor_unitario' => 10,
            'valor_total' => 20,
            'ncm' => '12345678',
        ]);
        $this->assertDatabaseHas('movimentacao_produtos', [
            'id_produto' => $produto->id_produto,
            'tipo_movimentacao' => 'saida',
            'quantidade' => 2,
        ]);
        $this->assertDatabaseHas('produtos', [
            'id_produto' => $produto->id_produto,
            'quantidade' => 8,
        ]);
    }
}
