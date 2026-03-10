<?php
    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use App\Models\Lancamento;
    use App\Models\Produto;
    use Carbon\Carbon;

    class ControleFinanceiroController extends Controller
    {
        public function index(Request $request)
        {
            // -- PRODUTO no periodo(por validade ou sem filtro)
            $dataInicio = $request->get('data_inicio', Carbon::now()->startOfMonth()->toDateString());
            $dataFim = $request->get('data_fim', Carbon::now()->endOfMonth()->toDateString());

            // -- PRODUTO no periodo(por validade ou sem filtro)
            $totalCompra = Produto::whereBetween('validade', [$dataInicio, $dataFim])
                ->selectRaw('SUM(preco_compra * quantidade) as total')->value('total') ?? 0;
            $totalVenda = Produto::whereBetween('validade', [$dataInicio, $dataFim])
                ->selectRaw('SUM(preco_venda * quantidade) as total')->value('total') ?? 0;

            // -- LANÇAMENTOS no periodo
            $lancamentos = Lancamento::with(['centroCusto.tipo'])
                ->whereBetween('dt_faturamento', [$dataInicio, $dataFim])
                ->orderBy('dt_faturamento', 'desc')
                ->get();

            $entrada = $lancamentos->filter(fn($l) => optional($l->centroCusto->tipo)->tipo === 'entrada')->sum('valor');
            $saida = $lancamentos->filter(fn($l) => optional($l->centroCusto->tipo)->tipo === 'saida')->sum('valor');

            $saldo = $entrada - $saida;

            $margem = $totalVenda > 0 ? (($totalVenda - $totalCompra) / $totalVenda) * 100 : 0;

            return view('controleFinanceiro.controle-financeiro', compact('lancamentos', 'dataInicio', 'dataFim', 'entrada', 'saida', 'saldo', 'totalCompra', 'totalVenda', 'margem'));
        }
    }
