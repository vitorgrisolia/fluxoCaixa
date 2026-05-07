<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaLog;
use App\Models\CentroCusto;
use App\Models\Compra;
use App\Models\ConfiguracaoSistema;
use App\Models\Lancamento;
use App\Models\MovimentacaoProduto;
use App\Models\Produto;
use App\Models\FechamentoCaixa;
use App\Models\Tipo;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $hoje = Carbon::today();
        $limiteCritico = $hoje->copy()->addDays(7);
        $limiteAtencao = $hoje->copy()->addDays(30);

        $totalVencidos = Produto::whereDate('validade', '<', $hoje)->count();
        $totalVencimentoCritico = Produto::whereBetween('validade', [$hoje, $limiteCritico])->count();
        $totalVencimentoAtencao = Produto::whereBetween('validade', [$limiteCritico->copy()->addDay(), $limiteAtencao])->count();
        $totalVencendo = $totalVencimentoCritico + $totalVencimentoAtencao;
        $totalAbaixoEstoqueMinimo = Produto::whereColumn('quantidade', '<=', 'estoque_minimo')->count();

        $produtosReposicaoDashboard = Produto::query()
            ->whereColumn('quantidade', '<=', 'estoque_minimo')
            ->orderByRaw('(estoque_minimo - quantidade) DESC')
            ->orderBy('nome')
            ->take(5)
            ->get();

        $inicioMes = $hoje->copy()->startOfMonth();
        $fimMes = $hoje->copy()->endOfMonth();

        $inicioMesData = $inicioMes->copy()->startOfDay();
        $fimMesData = $fimMes->copy()->endOfDay();

        $totalUsuarios = User::count();
        $totalAdmins = User::where('tipo_usuario', 'admin')->count();
        $totalFuncionarios = User::where('tipo_usuario', 'funcionario')->count();

        $totalProdutos = Produto::count();
        $totalItensEstoque = Produto::sum('quantidade');
        $produtosSemEstoque = Produto::where('quantidade', '<=', 0)->count();
        $indiceRuptura = $totalProdutos > 0 ? ($produtosSemEstoque / $totalProdutos) * 100 : 0;
        $valorTotalEstoqueVenda = Produto::sum(DB::raw('preco_venda * quantidade'));

        $movimentacoesMesBase = MovimentacaoProduto::whereBetween('data_movimentacao', [
            $inicioMes->toDateString(),
            $fimMes->toDateString(),
        ]);

        $movimentacoesMes = (clone $movimentacoesMesBase)->count();
        $entradasEstoqueMes = (clone $movimentacoesMesBase)
            ->where('tipo_movimentacao', 'entrada')
            ->sum('quantidade');
        $saidasEstoqueMes = (clone $movimentacoesMesBase)
            ->where('tipo_movimentacao', 'saida')
            ->sum('quantidade');

        $baseLancamentos = Lancamento::whereBetween('dt_faturamento', [$inicioMes, $fimMes]);

        $totalEntradas = (clone $baseLancamentos)
            ->whereHas('centroCusto.tipo', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->whereRaw('LOWER(tipo) LIKE ?', ['%entrada%'])
                        ->orWhereRaw('LOWER(tipo) LIKE ?', ['%receita%']);
                });
            })
            ->sum('valor');

        $totalSaidas = (clone $baseLancamentos)
            ->whereHas('centroCusto.tipo', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->whereRaw('LOWER(tipo) LIKE ?', ['%saida%'])
                        ->orWhereRaw('LOWER(tipo) LIKE ?', ['%despesa%']);
                });
            })
            ->sum('valor');

        $saldoMes = $totalEntradas - $totalSaidas;
        $lancamentosMes = (clone $baseLancamentos)->count();

        $ultimoLancamento = Lancamento::with(['centroCusto.tipo', 'usuario'])
            ->orderBy('dt_faturamento', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        $totalCompraPeriodo = Produto::whereBetween('validade', [$inicioMes, $fimMes])
            ->selectRaw('SUM(preco_compra * quantidade) as total')
            ->value('total') ?? 0;
        $totalVendaPeriodo = Produto::whereBetween('validade', [$inicioMes, $fimMes])
            ->selectRaw('SUM(preco_venda * quantidade) as total')
            ->value('total') ?? 0;

        $margemEstoque = $totalVendaPeriodo > 0
            ? (($totalVendaPeriodo - $totalCompraPeriodo) / $totalVendaPeriodo) * 100
            : 0;

        $comprasMesBase = Compra::whereBetween('data_compra', [$inicioMesData, $fimMesData]);
        $comprasMes = (clone $comprasMesBase)->count();
        $totalComprasMes = (clone $comprasMesBase)->sum('valor_total');
        $ticketMedioCompras = $comprasMes > 0 ? $totalComprasMes / $comprasMes : 0;
        $comprasPorForma = (clone $comprasMesBase)
            ->select('forma_pagamento', DB::raw('SUM(valor_total) as total'))
            ->groupBy('forma_pagamento')
            ->pluck('total', 'forma_pagamento');

        $comprasDinheiro = (float) ($comprasPorForma['dinheiro'] ?? 0);
        $comprasPix = (float) ($comprasPorForma['pix'] ?? 0);
        $comprasCartao = (float) (($comprasPorForma['cartao_debito'] ?? 0) + ($comprasPorForma['cartao_credito'] ?? 0));
        $comprasOutros = (float) (($comprasPorForma['boleto'] ?? 0) + ($comprasPorForma['vale_alimentacao'] ?? 0));

        $temSubtotalCusto = Schema::hasColumn('venda_itens', 'subtotal_custo');
        $temValorUnitarioCusto = Schema::hasColumn('venda_itens', 'valor_unitario_custo');

        $expressaoCusto = 'COALESCE(p.preco_compra, 0) * vi.quantidade';
        if ($temSubtotalCusto && $temValorUnitarioCusto) {
            $expressaoCusto = "CASE WHEN vi.subtotal_custo > 0 THEN vi.subtotal_custo WHEN vi.valor_unitario_custo > 0 THEN vi.valor_unitario_custo * vi.quantidade ELSE COALESCE(p.preco_compra, 0) * vi.quantidade END";
        } elseif ($temSubtotalCusto) {
            $expressaoCusto = "CASE WHEN vi.subtotal_custo > 0 THEN vi.subtotal_custo ELSE COALESCE(p.preco_compra, 0) * vi.quantidade END";
        } elseif ($temValorUnitarioCusto) {
            $expressaoCusto = "CASE WHEN vi.valor_unitario_custo > 0 THEN vi.valor_unitario_custo * vi.quantidade ELSE COALESCE(p.preco_compra, 0) * vi.quantidade END";
        }

        $resumoVendaMes = DB::table('venda_itens as vi')
            ->join('compras as c', 'vi.id_compra', '=', 'c.id_compra')
            ->leftJoin('produtos as p', 'vi.id_produto', '=', 'p.id_produto')
            ->whereBetween('c.data_compra', [$inicioMesData, $fimMesData])
            ->selectRaw('SUM(vi.subtotal) as receita_total, SUM('.$expressaoCusto.') as custo_total, SUM(vi.quantidade) as quantidade_total')
            ->first();

        $receitaVendasMes = (float) ($resumoVendaMes->receita_total ?? 0);
        $custoVendasMes = (float) ($resumoVendaMes->custo_total ?? 0);
        $lucroBrutoVendasMes = $receitaVendasMes - $custoVendasMes;
        $margemBrutaVendasMes = $receitaVendasMes > 0 ? ($lucroBrutoVendasMes / $receitaVendasMes) * 100 : 0;

        $produtoMaisVendido = DB::table('venda_itens as vi')
            ->join('compras as c', 'vi.id_compra', '=', 'c.id_compra')
            ->whereBetween('c.data_compra', [$inicioMesData, $fimMesData])
            ->selectRaw('vi.id_produto, vi.nome_produto, SUM(vi.quantidade) as quantidade_vendida, SUM(vi.subtotal) as receita')
            ->groupBy('vi.id_produto', 'vi.nome_produto')
            ->orderByDesc('quantidade_vendida')
            ->orderByDesc('receita')
            ->first();

        $fechamentosMes = FechamentoCaixa::whereBetween('data_fechamento', [$inicioMes, $fimMes])->count();
        $ultimoFechamento = FechamentoCaixa::with('usuario')
            ->orderBy('data_fechamento', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        $fechamentosRecentes = FechamentoCaixa::with('usuario')
            ->orderBy('data_fechamento', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $inicioAuditoria = $hoje->copy()->subDays(6)->startOfDay();
        $fimAuditoria = $hoje->copy()->endOfDay();
        $auditoriasSemana = AuditoriaLog::whereBetween('created_at', [$inicioAuditoria, $fimAuditoria])->count();
        $ultimaAuditoria = AuditoriaLog::with('usuario')
            ->orderBy('created_at', 'desc')
            ->first();

        $totalTipos = Tipo::count();
        $totalCentros = CentroCusto::count();

        $configuracao = ConfiguracaoSistema::orderBy('id_configuracao', 'desc')->first();

        return view('dashboard')->with([
            'totalVencidos' => $totalVencidos,
            'totalVencendo' => $totalVencendo,
            'totalVencimentoCritico' => $totalVencimentoCritico,
            'totalVencimentoAtencao' => $totalVencimentoAtencao,
            'totalAbaixoEstoqueMinimo' => $totalAbaixoEstoqueMinimo,
            'produtosReposicaoDashboard' => $produtosReposicaoDashboard,
            'saldoMes' => $saldoMes,
            'inicioMes' => $inicioMes,
            'fimMes' => $fimMes,
            'fechamentosRecentes' => $fechamentosRecentes,
            'totalUsuarios' => $totalUsuarios,
            'totalAdmins' => $totalAdmins,
            'totalFuncionarios' => $totalFuncionarios,
            'totalProdutos' => $totalProdutos,
            'totalItensEstoque' => $totalItensEstoque,
            'produtosSemEstoque' => $produtosSemEstoque,
            'indiceRuptura' => $indiceRuptura,
            'valorTotalEstoqueVenda' => $valorTotalEstoqueVenda,
            'movimentacoesMes' => $movimentacoesMes,
            'entradasEstoqueMes' => $entradasEstoqueMes,
            'saidasEstoqueMes' => $saidasEstoqueMes,
            'totalEntradas' => $totalEntradas,
            'totalSaidas' => $totalSaidas,
            'lancamentosMes' => $lancamentosMes,
            'ultimoLancamento' => $ultimoLancamento,
            'totalCompraPeriodo' => $totalCompraPeriodo,
            'totalVendaPeriodo' => $totalVendaPeriodo,
            'margemEstoque' => $margemEstoque,
            'comprasMes' => $comprasMes,
            'totalComprasMes' => $totalComprasMes,
            'ticketMedioCompras' => $ticketMedioCompras,
            'comprasDinheiro' => $comprasDinheiro,
            'comprasPix' => $comprasPix,
            'comprasCartao' => $comprasCartao,
            'comprasOutros' => $comprasOutros,
            'receitaVendasMes' => $receitaVendasMes,
            'custoVendasMes' => $custoVendasMes,
            'lucroBrutoVendasMes' => $lucroBrutoVendasMes,
            'margemBrutaVendasMes' => $margemBrutaVendasMes,
            'produtoMaisVendido' => $produtoMaisVendido,
            'fechamentosMes' => $fechamentosMes,
            'ultimoFechamento' => $ultimoFechamento,
            'auditoriasSemana' => $auditoriasSemana,
            'ultimaAuditoria' => $ultimaAuditoria,
            'totalTipos' => $totalTipos,
            'totalCentros' => $totalCentros,
            'configuracao' => $configuracao,
        ]);
    }
}
