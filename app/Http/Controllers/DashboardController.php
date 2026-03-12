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

class DashboardController extends Controller
{
    public function index()
    {
        $hoje = Carbon::today();

        $totalVencidos = Produto::whereDate('validade', '<', $hoje)->count();
        $totalVencendo = Produto::whereBetween('validade', [$hoje, $hoje->copy()->addDays(30)])->count();

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
