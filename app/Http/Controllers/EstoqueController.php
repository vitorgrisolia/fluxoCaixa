<?php

namespace App\Http\Controllers;

use App\Models\MovimentacaoProduto;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EstoqueController extends Controller
{
    private const MOTIVOS_AJUSTE = [
        'reposicao_fornecedor' => 'Reposicao de fornecedor',
        'ajuste_inventario' => 'Ajuste de inventario',
        'avaria_perda' => 'Avaria / perda',
        'vencimento_descarte' => 'Vencimento / descarte',
        'transferencia_estoque' => 'Transferencia de estoque',
        'devolucao_cliente' => 'Devolucao de cliente',
        'venda_externa' => 'Venda externa / baixa manual',
        'venda_pdv' => 'Venda no PDV',
    ];

    public function index(Request $request)
    {
        $periodo = $request->input('periodo', 'dia');
        if (!in_array($periodo, ['dia', 'semana', 'mes'], true)) {
            $periodo = 'dia';
        }

        $dataSelecionada = $request->input('data', Carbon::today()->format('Y-m-d'));

        try {
            $dataBase = Carbon::parse($dataSelecionada);
        } catch (\Exception $e) {
            $dataBase = Carbon::today();
            $dataSelecionada = $dataBase->format('Y-m-d');
        }

        [$inicioPeriodo, $fimPeriodo] = $this->resolverPeriodo($periodo, $dataBase);

        $consultaMovimentacoes = MovimentacaoProduto::with('produto')
            ->whereBetween('data_movimentacao', [
                $inicioPeriodo->toDateString(),
                $fimPeriodo->toDateString(),
            ]);

        $movimentacoes = (clone $consultaMovimentacoes)
            ->orderBy('data_movimentacao', 'desc')
            ->orderBy('id_movimentacao', 'desc')
            ->get();

        $totalEntradasQuantidade = (clone $consultaMovimentacoes)
            ->where('tipo_movimentacao', 'entrada')
            ->sum('quantidade');

        $totalSaidasQuantidade = (clone $consultaMovimentacoes)
            ->where('tipo_movimentacao', 'saida')
            ->sum('quantidade');

        $resumoValorVenda = (clone $consultaMovimentacoes)
            ->where('tipo_movimentacao', 'saida')
            ->sum(DB::raw('quantidade * valor_unitario_venda'));

        $produtos = Produto::orderBy('nome')->get();
        $valorTotalEstoqueVenda = $produtos->sum(function ($produto) {
            return $produto->quantidade * $produto->preco_venda;
        });

        $rotulosPeriodo = [
            'dia' => 'Dia',
            'semana' => 'Semana',
            'mes' => 'Mes',
        ];

        return view('estoque.index')->with([
            'periodo' => $periodo,
            'dataSelecionada' => $dataSelecionada,
            'inicioPeriodo' => $inicioPeriodo,
            'fimPeriodo' => $fimPeriodo,
            'periodoLabel' => $rotulosPeriodo[$periodo],
            'produtos' => $produtos,
            'movimentacoes' => $movimentacoes,
            'totalEntradasQuantidade' => $totalEntradasQuantidade,
            'totalSaidasQuantidade' => $totalSaidasQuantidade,
            'resumoValorVenda' => $resumoValorVenda,
            'valorTotalEstoqueVenda' => $valorTotalEstoqueVenda,
            'motivosAjuste' => self::MOTIVOS_AJUSTE,
        ]);
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'id_produto' => ['required', 'integer'],
            'tipo_movimentacao' => ['required', 'in:entrada,saida'],
            'motivo_ajuste' => ['required', 'in:'.implode(',', array_keys(self::MOTIVOS_AJUSTE))],
            'quantidade' => ['required', 'integer', 'min:1'],
            'data_movimentacao' => ['required', 'date'],
            'observacao' => ['required', 'string', 'max:500'],
            'periodo_atual' => ['nullable', 'in:dia,semana,mes'],
            'data_atual' => ['nullable', 'date'],
        ]);

        $produto = Produto::findOrFail($dados['id_produto']);

        if ($dados['tipo_movimentacao'] === 'saida' && $dados['quantidade'] > $produto->quantidade) {
            return redirect()->route('estoque.index', [
                'periodo' => $dados['periodo_atual'] ?? 'dia',
                'data' => $dados['data_atual'] ?? Carbon::today()->format('Y-m-d'),
            ])->with('danger', 'Saida maior que o estoque disponivel para o produto selecionado.');
        }

        DB::transaction(function () use ($dados, $produto) {
            MovimentacaoProduto::create([
                'id_produto' => $produto->id_produto,
                'tipo_movimentacao' => $dados['tipo_movimentacao'],
                'motivo_ajuste' => $dados['motivo_ajuste'],
                'quantidade' => $dados['quantidade'],
                'valor_unitario_venda' => $dados['tipo_movimentacao'] === 'saida'
                    ? $produto->preco_venda
                    : null,
                'data_movimentacao' => $dados['data_movimentacao'],
                'observacao' => $dados['observacao'],
            ]);

            if ($dados['tipo_movimentacao'] === 'entrada') {
                $produto->quantidade += $dados['quantidade'];
            } else {
                $produto->quantidade -= $dados['quantidade'];
            }

            $produto->save();
        });

        return redirect()->route('estoque.index', [
            'periodo' => $dados['periodo_atual'] ?? 'dia',
            'data' => $dados['data_atual'] ?? Carbon::today()->format('Y-m-d'),
        ])->with('success', 'Movimentacao registrada com sucesso.');
    }

    private function resolverPeriodo(string $periodo, Carbon $dataBase): array
    {
        if ($periodo === 'semana') {
            return [
                $dataBase->copy()->startOfWeek(Carbon::MONDAY),
                $dataBase->copy()->endOfWeek(Carbon::SUNDAY),
            ];
        }

        if ($periodo === 'mes') {
            return [
                $dataBase->copy()->startOfMonth(),
                $dataBase->copy()->endOfMonth(),
            ];
        }

        return [
            $dataBase->copy()->startOfDay(),
            $dataBase->copy()->endOfDay(),
        ];
    }
}
