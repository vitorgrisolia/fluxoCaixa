<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\FechamentoCaixa;
use App\Models\AuditoriaLog;
use App\Models\Lancamento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    public function index(Request $request)
    {
        [$dataInicio, $dataFim] = $this->resolverPeriodo($request);
        $mostrar = $this->resolverSecoes($request);

        $lancamentos = collect();
        if ($mostrar['resumo'] || $mostrar['lancamentos']) {
            $lancamentos = $this->carregarLancamentos($dataInicio, $dataFim);
        }

        [$entrada, $saida, $saldo] = $mostrar['resumo']
            ? $this->calcularTotais($lancamentos)
            : [0, 0, 0];

        $porCentro = $mostrar['por_centro']
            ? $this->resumoPorCentro($dataInicio, $dataFim)
            : collect();
        $porTipo = $mostrar['por_tipo']
            ? $this->resumoPorTipo($dataInicio, $dataFim)
            : collect();
        $fechamentos = $mostrar['fechamento_caixa']
            ? $this->carregarFechamentos($dataInicio, $dataFim)
            : collect();
        $auditorias = $mostrar['auditoria']
            ? $this->carregarAuditorias($dataInicio, $dataFim)
            : collect();

        return view('relatorios.index')->with([
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'lancamentos' => $lancamentos,
            'entrada' => $entrada,
            'saida' => $saida,
            'saldo' => $saldo,
            'porCentro' => $porCentro,
            'porTipo' => $porTipo,
            'fechamentos' => $fechamentos,
            'auditorias' => $auditorias,
            'mostrar' => $mostrar,
        ]);
    }

    public function exportCsv(Request $request)
    {
        [$dataInicio, $dataFim] = $this->resolverPeriodo($request);
        $mostrar = $this->resolverSecoes($request);

        $lancamentos = collect();
        if ($mostrar['resumo'] || $mostrar['lancamentos']) {
            $lancamentos = $this->carregarLancamentos($dataInicio, $dataFim);
        }

        [$entrada, $saida, $saldo] = $mostrar['resumo']
            ? $this->calcularTotais($lancamentos)
            : [0, 0, 0];

        $porCentro = $mostrar['por_centro']
            ? $this->resumoPorCentro($dataInicio, $dataFim)
            : collect();
        $porTipo = $mostrar['por_tipo']
            ? $this->resumoPorTipo($dataInicio, $dataFim)
            : collect();
        $fechamentos = $mostrar['fechamento_caixa']
            ? $this->carregarFechamentos($dataInicio, $dataFim)
            : collect();
        $auditorias = $mostrar['auditoria']
            ? $this->carregarAuditorias($dataInicio, $dataFim)
            : collect();

        $nomeArquivo = "relatorio_{$dataInicio}_a_{$dataFim}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nomeArquivo}\"",
        ];

        $callback = function () use ($lancamentos, $dataInicio, $dataFim, $entrada, $saida, $saldo, $porCentro, $porTipo, $fechamentos, $auditorias, $mostrar) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Periodo', $dataInicio, $dataFim], ';');
            fputcsv($handle, [], ';');

            if ($mostrar['resumo']) {
                fputcsv($handle, ['Entradas', number_format($entrada, 2, ',', '.')], ';');
                fputcsv($handle, ['Saidas', number_format($saida, 2, ',', '.')], ';');
                fputcsv($handle, ['Saldo', number_format($saldo, 2, ',', '.')], ';');
                fputcsv($handle, [], ';');
            }

            if ($mostrar['por_centro']) {
                fputcsv($handle, ['Resumo por centro de custo'], ';');
                fputcsv($handle, ['Centro de custo', 'Tipo', 'Total'], ';');
                foreach ($porCentro as $item) {
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
                foreach ($porTipo as $item) {
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
                foreach ($fechamentos as $fechamento) {
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
                foreach ($auditorias as $log) {
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
                foreach ($lancamentos as $lancamento) {
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

        $lancamentos = collect();
        if ($mostrar['resumo'] || $mostrar['lancamentos']) {
            $lancamentos = $this->carregarLancamentos($dataInicio, $dataFim);
        }

        [$entrada, $saida, $saldo] = $mostrar['resumo']
            ? $this->calcularTotais($lancamentos)
            : [0, 0, 0];

        $porCentro = $mostrar['por_centro']
            ? $this->resumoPorCentro($dataInicio, $dataFim)
            : collect();
        $porTipo = $mostrar['por_tipo']
            ? $this->resumoPorTipo($dataInicio, $dataFim)
            : collect();
        $fechamentos = $mostrar['fechamento_caixa']
            ? $this->carregarFechamentos($dataInicio, $dataFim)
            : collect();
        $auditorias = $mostrar['auditoria']
            ? $this->carregarAuditorias($dataInicio, $dataFim)
            : collect();

        $pdf = Pdf::loadView('relatorios.pdf', [
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'lancamentos' => $lancamentos,
            'entrada' => $entrada,
            'saida' => $saida,
            'saldo' => $saldo,
            'porCentro' => $porCentro,
            'porTipo' => $porTipo,
            'fechamentos' => $fechamentos,
            'auditorias' => $auditorias,
            'mostrar' => $mostrar,
        ])->setPaper('a4', 'portrait');

        $nomeArquivo = "relatorio_{$dataInicio}_a_{$dataFim}.pdf";

        return $pdf->download($nomeArquivo);
    }

    private function resolverPeriodo(Request $request): array
    {
        $dataInicio = $request->get('data_inicio', Carbon::now()->startOfMonth()->toDateString());
        $dataFim = $request->get('data_fim', Carbon::now()->endOfMonth()->toDateString());

        return [$dataInicio, $dataFim];
    }

    private function resolverSecoes(Request $request): array
    {
        $todas = ['resumo', 'por_centro', 'por_tipo', 'fechamento_caixa', 'auditoria', 'lancamentos'];
        $secoes = $request->get('secoes');

        if (empty($secoes)) {
            return array_fill_keys($todas, true);
        }

        $secoes = is_array($secoes) ? $secoes : [$secoes];
        $secoes = array_intersect($todas, $secoes);

        $mostrar = array_fill_keys($todas, false);
        foreach ($secoes as $secao) {
            $mostrar[$secao] = true;
        }

        return $mostrar;
    }

    private function carregarLancamentos(string $dataInicio, string $dataFim)
    {
        return Lancamento::with(['centroCusto.tipo', 'usuario'])
            ->whereBetween('dt_faturamento', [$dataInicio, $dataFim])
            ->orderBy('dt_faturamento', 'desc')
            ->get();
    }

    private function carregarFechamentos(string $dataInicio, string $dataFim)
    {
        return FechamentoCaixa::with('usuario')
            ->whereBetween('data_fechamento', [$dataInicio, $dataFim])
            ->orderBy('data_fechamento', 'desc')
            ->get();
    }

    private function carregarAuditorias(string $dataInicio, string $dataFim)
    {
        $inicio = Carbon::parse($dataInicio)->startOfDay();
        $fim = Carbon::parse($dataFim)->endOfDay();

        return AuditoriaLog::with('usuario')
            ->whereBetween('created_at', [$inicio, $fim])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function calcularTotais($lancamentos): array
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

    private function resumoPorCentro(string $dataInicio, string $dataFim)
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

    private function resumoPorTipo(string $dataInicio, string $dataFim)
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
}
