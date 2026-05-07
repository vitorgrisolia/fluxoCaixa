<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SaleResource;
use App\Models\Compra;
use App\Models\MovimentacaoProduto;
use App\Models\Produto;
use App\Models\VendaItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Compra::with('itens')->orderBy('data_compra', 'desc')->orderBy('id_compra', 'desc');

        if ($request->filled('data_inicio')) {
            $query->whereDate('data_compra', '>=', $request->query('data_inicio'));
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('data_compra', '<=', $request->query('data_fim'));
        }

        if ($request->filled('forma_pagamento')) {
            $query->where('forma_pagamento', $request->query('forma_pagamento'));
        }

        if ($request->filled('id_turno')) {
            $query->where('id_turno', (int) $request->query('id_turno'));
        }

        $perPage = max(1, min((int) $request->query('per_page', 20), 100));

        return SaleResource::collection(
            $query->paginate($perPage)->appends($request->query())
        );
    }

    public function summary(Request $request): JsonResponse
    {
        $query = Compra::query();

        if ($request->filled('data_inicio')) {
            $query->whereDate('data_compra', '>=', $request->query('data_inicio'));
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('data_compra', '<=', $request->query('data_fim'));
        }

        $totais = (clone $query)
            ->selectRaw('COUNT(*) as total_vendas, COALESCE(SUM(valor_total),0) as receita_total')
            ->first();

        $itens = VendaItem::query()
            ->join('compras', 'compras.id_compra', '=', 'venda_itens.id_compra');

        if ($request->filled('data_inicio')) {
            $itens->whereDate('compras.data_compra', '>=', $request->query('data_inicio'));
        }

        if ($request->filled('data_fim')) {
            $itens->whereDate('compras.data_compra', '<=', $request->query('data_fim'));
        }

        $totaisItens = (clone $itens)
            ->selectRaw('COALESCE(SUM(venda_itens.quantidade),0) as total_itens, COALESCE(SUM(venda_itens.subtotal - venda_itens.subtotal_custo),0) as lucro_bruto')
            ->first();

        return response()->json([
            'total_vendas' => (int) ($totais->total_vendas ?? 0),
            'receita_total' => (float) ($totais->receita_total ?? 0),
            'total_itens' => (int) ($totaisItens->total_itens ?? 0),
            'lucro_bruto' => (float) ($totaisItens->lucro_bruto ?? 0),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'data_compra' => ['nullable', 'date'],
            'forma_pagamento' => ['required', 'in:pix,dinheiro,cartao_debito,cartao_credito,boleto,vale_alimentacao'],
            'dividir_valor' => ['nullable', 'in:sim,nao'],
            'parcelas' => ['nullable', 'integer', 'min:1', 'max:12'],
            'id_turno' => ['nullable', 'integer'],
            'itens' => ['required', 'array', 'min:1'],
            'itens.*.id_produto' => ['required', 'integer'],
            'itens.*.quantidade' => ['required', 'integer', 'min:1'],
            'itens.*.valor_unitario_venda' => ['nullable', 'numeric', 'min:0'],
        ]);

        $user = $request->user();
        $podeDefinirPreco = $user && method_exists($user, 'possuiPermissao')
            ? $user->possuiPermissao('produto.definir_preco')
            : false;
        $totalCompra = 0;
        $compra = null;

        DB::transaction(function () use ($dados, $user, $podeDefinirPreco, &$totalCompra, &$compra) {
            $idsProdutos = collect($dados['itens'])->pluck('id_produto')->map(function ($id) {
                return (int) $id;
            })->unique()->values()->all();

            $produtos = Produto::whereIn('id_produto', $idsProdutos)
                ->lockForUpdate()
                ->get()
                ->keyBy('id_produto');

            if ($produtos->count() !== count($idsProdutos)) {
                abort(422, 'Um ou mais produtos nao foram encontrados.');
            }

            foreach ($dados['itens'] as $item) {
                $produto = $produtos->get((int) $item['id_produto']);
                $quantidade = (int) $item['quantidade'];

                if ($quantidade > (int) $produto->quantidade) {
                    abort(422, "Estoque insuficiente para {$produto->nome}.");
                }

                $valorUnitario = $podeDefinirPreco && isset($item['valor_unitario_venda'])
                    ? (float) $item['valor_unitario_venda']
                    : (float) $produto->preco_venda;
                $totalCompra += $valorUnitario * $quantidade;
            }

            $compra = new Compra();
            $compra->fill([
                'id_turno' => $dados['id_turno'] ?? null,
                'data_compra' => $dados['data_compra'] ?? now(),
                'valor_total' => $totalCompra,
                'forma_pagamento' => $dados['forma_pagamento'],
                'dividir_valor' => $dados['dividir_valor'] ?? 'nao',
                'parcelas' => $dados['parcelas'] ?? null,
            ]);
            $compra->id_user = $user->id_user;
            $compra->save();

            foreach ($dados['itens'] as $item) {
                $produto = $produtos->get((int) $item['id_produto']);
                $quantidade = (int) $item['quantidade'];
                $valorUnitarioVenda = $podeDefinirPreco && isset($item['valor_unitario_venda'])
                    ? (float) $item['valor_unitario_venda']
                    : (float) $produto->preco_venda;
                $valorUnitarioCusto = (float) $produto->preco_compra;

                VendaItem::create([
                    'id_compra' => $compra->id_compra,
                    'id_produto' => $produto->id_produto,
                    'nome_produto' => $produto->nome,
                    'lote' => $produto->lote,
                    'codigo_barras' => $produto->codigo_barras,
                    'quantidade' => $quantidade,
                    'valor_unitario_venda' => $valorUnitarioVenda,
                    'valor_unitario_custo' => $valorUnitarioCusto,
                    'subtotal' => $valorUnitarioVenda * $quantidade,
                    'subtotal_custo' => $valorUnitarioCusto * $quantidade,
                ]);

                MovimentacaoProduto::create([
                    'id_produto' => $produto->id_produto,
                    'tipo_movimentacao' => 'saida',
                    'motivo_ajuste' => 'venda_pdv',
                    'quantidade' => $quantidade,
                    'valor_unitario_venda' => $valorUnitarioVenda,
                    'data_movimentacao' => now()->toDateString(),
                    'observacao' => "Saida automatica da venda API #{$compra->id_compra}.",
                ]);

                $produto->quantidade = (int) $produto->quantidade - $quantidade;
                $produto->save();
            }
        });

        $compra->load('itens');

        return (new SaleResource($compra))
            ->response()
            ->setStatusCode(201);
    }
}
