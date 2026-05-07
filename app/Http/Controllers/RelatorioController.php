<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaLog;
use App\Models\Compra;
use App\Models\FechamentoCaixa;
use App\Models\Lancamento;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RelatorioController extends Controller
{
    private const SECOES_DISPONIVEIS = [
        'resumo',
        'por_centro',
        'por_tipo',
        'fechamento_caixa',
        'auditoria',
        'lancamentos',
        'margem_produto',
        'dre_mensal',
        'conciliacao_recebimentos',
    ];

    public function index(Request $request)
    {
        [$dataInicio, $dataFim] = $this->resolverPeriodo($request);
        $mostrar = $this->resolverSecoes($request);
        $dados = $this->montarDadosRelatorio($dataInicio, $dataFim, $mostrar);

        return view('relatorios.index')->with(array_merge([
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'mostrar' => $mostrar,
        ], $dados));
    }

    public function exportCsv(Request $request)
    {
        [$dataInicio, $dataFim] = $this->resolverPeriodo($request);
        $mostrar = $this->resolverSecoes($request);
        $dados = $this->montarDadosRelatorio($dataInicio, $dataFim, $mostrar);

        $nomeArquivo = "relatorio_{$dataInicio}_a_{$dataFim}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nomeArquivo}\"",
        ];

        $callback = function () use ($dados, $dataInicio, $dataFim, $mostrar) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Periodo', $dataInicio, $dataFim], ';');
            fputcsv($handle, [], ';');

            if ($mostrar['resumo']) {
                fputcsv($handle, ['Entradas', number_format($dados['entrada'], 2, ',', '.')], ';');
                fputcsv($handle, ['Saidas', number_format($dados['saida'], 2, ',', '.')], ';');
                fputcsv($handle, ['Saldo', number_format($dados['saldo'], 2, ',', '.')], ';');
                fputcsv($handle, [], ';');
            }

            if ($mostrar['margem_produto']) {
                fputcsv($handle, ['Margem por produto e lucro do periodo'], ';');
                fputcsv($handle, ['Receita total', number_format($dados['resumoMargem']['receita_total'], 2, ',', '.')], ';');
                fputcsv($handle, ['Custo total', number_format($dados['resumoMargem']['custo_total'], 2, ',', '.')], ';');
                fputcsv($handle, ['Lucro bruto', number_format($dados['resumoMargem']['lucro_bruto'], 2, ',', '.')], ';');
                fputcsv($handle, ['Margem bruta (%)', number_format($dados['resumoMargem']['margem_bruta_percentual'], 2, ',', '.')], ';');
                fputcsv($handle, [], ';');

                fputcsv($handle, ['Produto', 'Codigo barras', 'Qtd vendida', 'Receita', 'Custo', 'Lucro bruto', 'Margem (%)'], ';');
                foreach ($dados['margemProdutos'] as $item) {
                    fputcsv($handle, [
                        $item->nome_produto,
                        $item->codigo_barras ?? '-',
                        $item->quantidade_total,
                        number_format($item->receita_total, 2, ',', '.'),
                        number_format($item->custo_total, 2, ',', '.'),
                        number_format($item->lucro_bruto, 2, ',', '.'),
                        number_format($item->margem_percentual, 2, ',', '.'),
                    ], ';');
                }
                fputcsv($handle, [], ';');
            }

            if ($mostrar['dre_mensal']) {
                fputcsv($handle, ['DRE simplificada mensal'], ';');
                fputcsv($handle, ['Mes', 'Receita', 'Custos (CMV)', 'Despesas', 'Resultado', 'Margem resultado (%)'], ';');
                foreach ($dados['dreMensal'] as $item) {
                    fputcsv($handle, [
                        $item['mes'],
                        number_format($item['receita'], 2, ',', '.'),
                        number_format($item['custos'], 2, ',', '.'),
                        number_format($item['despesas'], 2, ',', '.'),
                        number_format($item['resultado'], 2, ',', '.'),
                        number_format($item['margem_resultado_percentual'], 2, ',', '.'),
                    ], ';');
                }
                fputcsv($handle, [], ';');
            }

            if ($mostrar['conciliacao_recebimentos']) {
                fputcsv($handle, ['Conciliacao de recebimentos'], ';');
                fputcsv($handle, ['Metodo', 'Previsto (vendas)', 'Declarado (fechamentos)', 'Diferenca (declarado - previsto)', 'Status'], ';');
                foreach ($dados['conciliacaoRecebimentos']['linhas'] as $linha) {
                    fputcsv($handle, [
                        $linha['metodo'],
                        number_format($linha['previsto'], 2, ',', '.'),
                        number_format($linha['declarado'], 2, ',', '.'),
                        number_format($linha['diferenca'], 2, ',', '.'),
                        $linha['status'],
                    ], ';');
                }
                fputcsv($handle, [
                    'TOTAL',
                    number_format($dados['conciliacaoRecebimentos']['totais']['previsto'], 2, ',', '.'),
                    number_format($dados['conciliacaoRecebimentos']['totais']['declarado'], 2, ',', '.'),
                    number_format($dados['conciliacaoRecebimentos']['totais']['diferenca'], 2, ',', '.'),
                    $dados['conciliacaoRecebimentos']['totais']['status'],
                ], ';');
                fputcsv($handle, [], ';');
            }

            if ($mostrar['por_centro']) {
                fputcsv($handle, ['Resumo por centro de custo'], ';');
                fputcsv($handle, ['Centro de custo', 'Tipo', 'Total'], ';');
                foreach ($dados['porCentro'] as $item) {
                    fputcsv($handle, [
                        $item->centro_custo,
                        $item->tipo,
                        number_format($item->total, 2, ',', '.'),
                    ], ';');
                }
                fputcsv($handle, [], ';');
            }

            if ($mostrar['por_tipo']) {
                fputcsv($handle, ['Resumo por tipo'], ';');
                fputcsv($handle, ['Tipo', 'Total'], ';');
                foreach ($dados['porTipo'] as $item) {
                    fputcsv($handle, [
                        $item->tipo,
                        number_format($item->total, 2, ',', '.'),
                    ], ';');
                }
                fputcsv($handle, [], ';');
            }

            if ($mostrar['fechamento_caixa']) {
                fputcsv($handle, ['Fechamento de caixa'], ';');
                fputcsv($handle, ['Data', 'Funcionario', 'Fundo de caixa', 'Dinheiro', 'Cartao', 'PIX', 'Outros', 'Outras saidas', 'Saldo final'], ';');
                foreach ($dados['fechamentos'] as $fechamento) {
                    fputcsv($handle, [
                        Carbon::parse($fechamento->data_fechamento)->format('d/m/Y'),
                        optional($fechamento->usuario)->nome ?? '-',
                        number_format($fechamento->saldo_inicial, 2, ',', '.'),
                        number_format($fechamento->valor_dinheiro, 2, ',', '.'),
                        number_format($fechamento->valor_cartao, 2, ',', '.'),
                        number_format($fechamento->valor_pix, 2, ',', '.'),
                        number_format($fechamento->valor_outros, 2, ',', '.'),
                        number_format($fechamento->total_saidas, 2, ',', '.'),
                        number_format($fechamento->saldo_final, 2, ',', '.'),
                    ], ';');
                }
                fputcsv($handle, [], ';');
            }

            if ($mostrar['auditoria']) {
                fputcsv($handle, ['Auditoria'], ';');
                fputcsv($handle, ['Data', 'Usuario', 'Acao', 'Descricao', 'Rota', 'IP'], ';');
                foreach ($dados['auditorias'] as $log) {
                    fputcsv($handle, [
                        Carbon::parse($log->created_at)->format('d/m/Y H:i'),
                        optional($log->usuario)->nome ?? '-',
                        $log->acao,
                        $log->descricao ?? '-',
                        $log->rota ?? $log->url,
                        $log->ip ?? '-',
                    ], ';');
                }
                fputcsv($handle, [], ';');
            }

            if ($mostrar['lancamentos']) {
                fputcsv($handle, ['Lancamentos'], ';');
                fputcsv($handle, ['Data', 'Descricao', 'Centro de custo', 'Tipo', 'Responsavel', 'Valor'], ';');
                foreach ($dados['lancamentos'] as $lancamento) {
                    fputcsv($handle, [
                        Carbon::parse($lancamento->dt_faturamento)->format('d/m/Y'),
                        $lancamento->descricao ?? '-',
                        optional($lancamento->centroCusto)->centro_custo ?? '-',
                        optional(optional($lancamento->centroCusto)->tipo)->tipo ?? '-',
                        optional($lancamento->usuario)->nome ?? '-',
                        number_format($lancamento->valor, 2, ',', '.'),
                    ], ';');
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        [$dataInicio, $dataFim] = $this->resolverPeriodo($request);
        $mostrar = $this->resolverSecoes($request);
        $dados = $this->montarDadosRelatorio($dataInicio, $dataFim, $mostrar);

        $pdf = Pdf::loadView('relatorios.pdf', array_merge([
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'mostrar' => $mostrar,
        ], $dados))->setPaper('a4', 'portrait');

        $nomeArquivo = "relatorio_{$dataInicio}_a_{$dataFim}.pdf";

        return $pdf->download($nomeArquivo);
    }

    private function montarDadosRelatorio(string $dataInicio, string $dataFim, array $mostrar): array
    {
        $lancamentos = collect();
        if ($mostrar['resumo'] || $mostrar['lancamentos']) {
            $lancamentos = $this->carregarLancamentos($dataInicio, $dataFim);
        }

        [$entrada, $saida, $saldo] = $mostrar['resumo']
            ? $this->calcularTotais($lancamentos)
            : [0, 0, 0];

        $margemProdutos = $mostrar['margem_produto']
            ? $this->carregarMargemPorProduto($dataInicio, $dataFim)
            : collect();

        return [
            'lancamentos' => $lancamentos,
            'entrada' => $entrada,
            'saida' => $saida,
            'saldo' => $saldo,
            'porCentro' => $mostrar['por_centro'] ? $this->resumoPorCentro($dataInicio, $dataFim) : collect(),
            'porTipo' => $mostrar['por_tipo'] ? $this->resumoPorTipo($dataInicio, $dataFim) : collect(),
            'fechamentos' => $mostrar['fechamento_caixa'] ? $this->carregarFechamentos($dataInicio, $dataFim) : collect(),
            'auditorias' => $mostrar['auditoria'] ? $this->carregarAuditorias($dataInicio, $dataFim) : collect(),
            'margemProdutos' => $margemProdutos,
            'resumoMargem' => $mostrar['margem_produto'] ? $this->calcularResumoMargem($margemProdutos) : $this->resumoMargemVazio(),
            'dreMensal' => $mostrar['dre_mensal'] ? $this->calcularDreMensal($dataInicio, $dataFim) : collect(),
            'conciliacaoRecebimentos' => $mostrar['conciliacao_recebimentos'] ? $this->calcularConciliacaoRecebimentos($dataInicio, $dataFim) : $this->conciliacaoVazia(),
        ];
    }

    private function resolverPeriodo(Request $request): array
    {
        $dataInicio = $request->get('data_inicio', Carbon::now()->startOfMonth()->toDateString());
        $dataFim = $request->get('data_fim', Carbon::now()->endOfMonth()->toDateString());

        return [$dataInicio, $dataFim];
    }

    private function resolverSecoes(Request $request): array
    {
        $secoes = $request->get('secoes');

        if (empty($secoes)) {
            return array_fill_keys(self::SECOES_DISPONIVEIS, true);
        }

        $secoes = is_array($secoes) ? $secoes : [$secoes];
        $secoes = array_intersect(self::SECOES_DISPONIVEIS, $secoes);

        $mostrar = array_fill_keys(self::SECOES_DISPONIVEIS, false);
        foreach ($secoes as $secao) {
            $mostrar[$secao] = true;
        }

        return $mostrar;
    }

    private function carregarLancamentos(string $dataInicio, string $dataFim): Collection
    {
        return Lancamento::with(['centroCusto.tipo', 'usuario'])
            ->whereBetween('dt_faturamento', [$dataInicio, $dataFim])
            ->orderBy('dt_faturamento', 'desc')
            ->get();
    }

    private function carregarFechamentos(string $dataInicio, string $dataFim): Collection
    {
        return FechamentoCaixa::with('usuario')
            ->whereBetween('data_fechamento', [$dataInicio, $dataFim])
            ->orderBy('data_fechamento', 'desc')
            ->get();
    }

    private function carregarAuditorias(string $dataInicio, string $dataFim): Collection
    {
        $inicio = Carbon::parse($dataInicio)->startOfDay();
        $fim = Carbon::parse($dataFim)->endOfDay();

        return AuditoriaLog::with('usuario')
            ->whereBetween('created_at', [$inicio, $fim])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function calcularTotais(Collection $lancamentos): array
    {
        $entrada = $lancamentos->filter(function ($lancamento) {
            $tipo = strtolower((string) optional($lancamento->centroCusto->tipo)->tipo);
            return str_contains($tipo, 'entrada') || str_contains($tipo, 'receita');
        })->sum('valor');

        $saida = $lancamentos->filter(function ($lancamento) {
            $tipo = strtolower((string) optional($lancamento->centroCusto->tipo)->tipo);
            return str_contains($tipo, 'saida') || str_contains($tipo, 'despesa');
        })->sum('valor');

        $saldo = $entrada - $saida;

        return [$entrada, $saida, $saldo];
    }

    private function resumoPorCentro(string $dataInicio, string $dataFim): Collection
    {
        return DB::table('lancamentos')
            ->join('centro_custos', 'lancamentos.id_centro_custo', '=', 'centro_custos.id_centro_custo')
            ->join('tipos', 'centro_custos.id_tipo', '=', 'tipos.id_tipo')
            ->selectRaw('centro_custos.centro_custo as centro_custo, tipos.tipo as tipo, SUM(lancamentos.valor) as total')
            ->whereBetween('lancamentos.dt_faturamento', [$dataInicio, $dataFim])
            ->groupBy('centro_custos.centro_custo', 'tipos.tipo')
            ->orderByDesc('total')
            ->get();
    }

    private function resumoPorTipo(string $dataInicio, string $dataFim): Collection
    {
        return DB::table('lancamentos')
            ->join('centro_custos', 'lancamentos.id_centro_custo', '=', 'centro_custos.id_centro_custo')
            ->join('tipos', 'centro_custos.id_tipo', '=', 'tipos.id_tipo')
            ->selectRaw('tipos.tipo as tipo, SUM(lancamentos.valor) as total')
            ->whereBetween('lancamentos.dt_faturamento', [$dataInicio, $dataFim])
            ->groupBy('tipos.tipo')
            ->orderByDesc('total')
            ->get();
    }

    private function carregarMargemPorProduto(string $dataInicio, string $dataFim): Collection
    {
        $inicio = Carbon::parse($dataInicio)->startOfDay();
        $fim = Carbon::parse($dataFim)->endOfDay();
        $expressaoCusto = $this->expressaoCustoItens();

        $dados = DB::table('venda_itens as vi')
            ->join('compras as c', 'vi.id_compra', '=', 'c.id_compra')
            ->leftJoin('produtos as p', 'vi.id_produto', '=', 'p.id_produto')
            ->selectRaw('vi.id_produto, vi.nome_produto, vi.codigo_barras, SUM(vi.quantidade) as quantidade_total, SUM(vi.subtotal) as receita_total, SUM('.$expressaoCusto.') as custo_total')
            ->whereBetween('c.data_compra', [$inicio, $fim])
            ->groupBy('vi.id_produto', 'vi.nome_produto', 'vi.codigo_barras')
            ->orderByDesc('receita_total')
            ->get();

        return $dados->map(function ($item) {
            $receita = (float) $item->receita_total;
            $custo = (float) $item->custo_total;
            $lucro = $receita - $custo;
            $margemPercentual = $receita > 0 ? ($lucro / $receita) * 100 : 0;

            $item->receita_total = $receita;
            $item->custo_total = $custo;
            $item->lucro_bruto = $lucro;
            $item->margem_percentual = $margemPercentual;

            return $item;
        });
    }

    private function calcularResumoMargem(Collection $margemProdutos): array
    {
        $receitaTotal = (float) $margemProdutos->sum('receita_total');
        $custoTotal = (float) $margemProdutos->sum('custo_total');
        $lucroBruto = $receitaTotal - $custoTotal;
        $margemBrutaPercentual = $receitaTotal > 0 ? ($lucroBruto / $receitaTotal) * 100 : 0;

        return [
            'receita_total' => $receitaTotal,
            'custo_total' => $custoTotal,
            'lucro_bruto' => $lucroBruto,
            'margem_bruta_percentual' => $margemBrutaPercentual,
        ];
    }

    private function resumoMargemVazio(): array
    {
        return [
            'receita_total' => 0.0,
            'custo_total' => 0.0,
            'lucro_bruto' => 0.0,
            'margem_bruta_percentual' => 0.0,
        ];
    }

    private function calcularDreMensal(string $dataInicio, string $dataFim): Collection
    {
        $inicio = Carbon::parse($dataInicio)->startOfMonth();
        $fim = Carbon::parse($dataFim)->endOfMonth();
        $fimDataHora = $fim->copy()->endOfDay();
        $expressaoCusto = $this->expressaoCustoItens();

        $meses = [];
        $cursor = $inicio->copy();
        while ($cursor->lte($fim)) {
            $chave = $cursor->format('Y-m');
            $meses[$chave] = [
                'periodo' => $chave,
                'mes' => $cursor->format('m/Y'),
                'receita' => 0.0,
                'custos' => 0.0,
                'despesas' => 0.0,
                'resultado' => 0.0,
                'margem_resultado_percentual' => 0.0,
            ];
            $cursor->addMonthNoOverflow();
        }

        $receitas = Compra::query()
            ->selectRaw("DATE_FORMAT(data_compra, '%Y-%m') as periodo, SUM(valor_total) as total")
            ->whereBetween('data_compra', [$inicio, $fimDataHora])
            ->groupBy('periodo')
            ->pluck('total', 'periodo');

        $custos = DB::table('venda_itens as vi')
            ->join('compras as c', 'vi.id_compra', '=', 'c.id_compra')
            ->leftJoin('produtos as p', 'vi.id_produto', '=', 'p.id_produto')
            ->selectRaw("DATE_FORMAT(c.data_compra, '%Y-%m') as periodo, SUM(".$expressaoCusto.") as total")
            ->whereBetween('c.data_compra', [$inicio, $fimDataHora])
            ->groupBy('periodo')
            ->pluck('total', 'periodo');

        $despesas = DB::table('lancamentos')
            ->join('centro_custos', 'lancamentos.id_centro_custo', '=', 'centro_custos.id_centro_custo')
            ->join('tipos', 'centro_custos.id_tipo', '=', 'tipos.id_tipo')
            ->selectRaw("DATE_FORMAT(lancamentos.dt_faturamento, '%Y-%m') as periodo, SUM(lancamentos.valor) as total")
            ->whereBetween('lancamentos.dt_faturamento', [$inicio->toDateString(), $fim->toDateString()])
            ->where(function ($query) {
                $query->whereRaw('LOWER(tipos.tipo) LIKE ?', ['%saida%'])
                    ->orWhereRaw('LOWER(tipos.tipo) LIKE ?', ['%despesa%']);
            })
            ->groupBy('periodo')
            ->pluck('total', 'periodo');

        foreach ($meses as $periodo => &$linha) {
            $linha['receita'] = (float) ($receitas[$periodo] ?? 0);
            $linha['custos'] = (float) ($custos[$periodo] ?? 0);
            $linha['despesas'] = (float) ($despesas[$periodo] ?? 0);
            $linha['resultado'] = $linha['receita'] - $linha['custos'] - $linha['despesas'];
            $linha['margem_resultado_percentual'] = $linha['receita'] > 0
                ? ($linha['resultado'] / $linha['receita']) * 100
                : 0;
        }
        unset($linha);

        return collect(array_values($meses));
    }

    private function calcularConciliacaoRecebimentos(string $dataInicio, string $dataFim): array
    {
        $inicio = Carbon::parse($dataInicio)->startOfDay();
        $fim = Carbon::parse($dataFim)->endOfDay();

        $previstos = Compra::query()
            ->selectRaw("
                SUM(CASE WHEN forma_pagamento = 'dinheiro' THEN valor_total ELSE 0 END) as dinheiro,
                SUM(CASE WHEN forma_pagamento = 'pix' THEN valor_total ELSE 0 END) as pix,
                SUM(CASE WHEN forma_pagamento IN ('cartao_credito','cartao_debito') THEN valor_total ELSE 0 END) as cartao
            ")
            ->whereBetween('data_compra', [$inicio, $fim])
            ->first();

        $declarados = FechamentoCaixa::query()
            ->selectRaw('SUM(valor_dinheiro) as dinheiro, SUM(valor_pix) as pix, SUM(valor_cartao) as cartao')
            ->whereBetween('data_fechamento', [$inicio->toDateString(), $fim->toDateString()])
            ->first();

        $linhas = collect([
            $this->montarLinhaConciliacao('Dinheiro', (float) ($previstos->dinheiro ?? 0), (float) ($declarados->dinheiro ?? 0)),
            $this->montarLinhaConciliacao('PIX', (float) ($previstos->pix ?? 0), (float) ($declarados->pix ?? 0)),
            $this->montarLinhaConciliacao('Cartao', (float) ($previstos->cartao ?? 0), (float) ($declarados->cartao ?? 0)),
        ]);

        $totalPrevisto = (float) $linhas->sum('previsto');
        $totalDeclarado = (float) $linhas->sum('declarado');
        $totalDiferenca = $totalDeclarado - $totalPrevisto;

        return [
            'linhas' => $linhas,
            'totais' => [
                'previsto' => $totalPrevisto,
                'declarado' => $totalDeclarado,
                'diferenca' => $totalDiferenca,
                'status' => abs($totalDiferenca) < 0.01 ? 'Conciliado' : 'Divergente',
            ],
        ];
    }

    private function conciliacaoVazia(): array
    {
        return [
            'linhas' => collect(),
            'totais' => [
                'previsto' => 0.0,
                'declarado' => 0.0,
                'diferenca' => 0.0,
                'status' => 'Sem dados',
            ],
        ];
    }

    private function montarLinhaConciliacao(string $metodo, float $previsto, float $declarado): array
    {
        $diferenca = $declarado - $previsto;

        return [
            'metodo' => $metodo,
            'previsto' => $previsto,
            'declarado' => $declarado,
            'diferenca' => $diferenca,
            'status' => abs($diferenca) < 0.01 ? 'Conciliado' : 'Divergente',
        ];
    }

    private function expressaoCustoItens(): string
    {
        $temSubtotalCusto = Schema::hasColumn('venda_itens', 'subtotal_custo');
        $temValorUnitarioCusto = Schema::hasColumn('venda_itens', 'valor_unitario_custo');

        if ($temSubtotalCusto && $temValorUnitarioCusto) {
            return "CASE
                WHEN vi.subtotal_custo > 0 THEN vi.subtotal_custo
                WHEN vi.valor_unitario_custo > 0 THEN vi.valor_unitario_custo * vi.quantidade
                ELSE COALESCE(p.preco_compra, 0) * vi.quantidade
            END";
        }

        if ($temSubtotalCusto) {
            return "CASE
                WHEN vi.subtotal_custo > 0 THEN vi.subtotal_custo
                ELSE COALESCE(p.preco_compra, 0) * vi.quantidade
            END";
        }

        if ($temValorUnitarioCusto) {
            return "CASE
                WHEN vi.valor_unitario_custo > 0 THEN vi.valor_unitario_custo * vi.quantidade
                ELSE COALESCE(p.preco_compra, 0) * vi.quantidade
            END";
        }

        return 'COALESCE(p.preco_compra, 0) * vi.quantidade';
    }
}

