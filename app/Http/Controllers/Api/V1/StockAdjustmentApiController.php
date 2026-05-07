<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\MovimentacaoProduto;
use App\Models\Produto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentApiController extends Controller
{
    public function store(Request $request, int $idProduto): JsonResponse
    {
        $dados = $request->validate([
            'tipo_movimentacao' => ['required', 'in:entrada,saida'],
            'quantidade' => ['required', 'integer', 'min:1'],
            'motivo_ajuste' => ['required', 'string', 'max:120'],
            'observacao' => ['nullable', 'string', 'max:500'],
            'valor_unitario_venda' => ['nullable', 'numeric', 'min:0'],
            'data_movimentacao' => ['nullable', 'date'],
        ]);

        $movimentacao = null;
        $produtoAtualizado = null;

        DB::transaction(function () use ($idProduto, $dados, &$movimentacao, &$produtoAtualizado) {
            $produto = Produto::where('id_produto', $idProduto)->lockForUpdate()->firstOrFail();

            if ($dados['tipo_movimentacao'] === 'saida' && (int) $dados['quantidade'] > (int) $produto->quantidade) {
                abort(422, "Estoque insuficiente para {$produto->nome}.");
            }

            $movimentacao = MovimentacaoProduto::create([
                'id_produto' => $produto->id_produto,
                'tipo_movimentacao' => $dados['tipo_movimentacao'],
                'motivo_ajuste' => $dados['motivo_ajuste'],
                'quantidade' => (int) $dados['quantidade'],
                'valor_unitario_venda' => isset($dados['valor_unitario_venda'])
                    ? (float) $dados['valor_unitario_venda']
                    : (float) $produto->preco_venda,
                'data_movimentacao' => $dados['data_movimentacao'] ?? now()->toDateString(),
                'observacao' => $dados['observacao'] ?? 'Ajuste via API de integracao.',
            ]);

            if ($dados['tipo_movimentacao'] === 'entrada') {
                $produto->quantidade = (int) $produto->quantidade + (int) $dados['quantidade'];
            } else {
                $produto->quantidade = (int) $produto->quantidade - (int) $dados['quantidade'];
            }

            $produto->save();
            $produtoAtualizado = $produto->fresh();
        });

        return response()->json([
            'message' => 'Estoque ajustado com sucesso.',
            'movimentacao_id' => $movimentacao->id_movimentacao,
            'produto' => (new ProductResource($produtoAtualizado))->resolve(),
        ], 201);
    }
}
