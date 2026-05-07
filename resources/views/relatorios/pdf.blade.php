<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relatorio - Visao geral</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #111827;
            font-size: 12px;
        }
        h1 {
            font-size: 18px;
            margin-bottom: 4px;
        }
        .muted {
            color: #6b7280;
        }
        .section {
            margin-top: 18px;
        }
        .summary {
            width: 100%;
            border-collapse: collapse;
        }
        .summary td {
            padding: 6px 8px;
            border: 1px solid #e5e7eb;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th,
        .table td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            text-align: left;
        }
        .table th {
            background: #f3f4f6;
        }
    </style>
</head>
<body>
    <h1>Relatorio - Visao geral por periodo</h1>
    <div class="muted">Periodo: {{ $dataInicio }} a {{ $dataFim }}</div>

    @if ($mostrar['resumo'])
        <div class="section">
            <table class="summary">
                <tr>
                    <td><strong>Entradas</strong></td>
                    <td>R$ {{ number_format($entrada, 2, ',', '.') }}</td>
                    <td><strong>Saidas</strong></td>
                    <td>R$ {{ number_format($saida, 2, ',', '.') }}</td>
                    <td><strong>Saldo</strong></td>
                    <td>R$ {{ number_format($saldo, 2, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    @endif

    @if ($mostrar['margem_produto'])
        <div class="section">
            <h2>Margem por produto e lucro do periodo</h2>
            <table class="summary">
                <tr>
                    <td><strong>Receita total</strong></td>
                    <td>R$ {{ number_format($resumoMargem['receita_total'], 2, ',', '.') }}</td>
                    <td><strong>Custo total</strong></td>
                    <td>R$ {{ number_format($resumoMargem['custo_total'], 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td><strong>Lucro bruto</strong></td>
                    <td>R$ {{ number_format($resumoMargem['lucro_bruto'], 2, ',', '.') }}</td>
                    <td><strong>Margem bruta (%)</strong></td>
                    <td>{{ number_format($resumoMargem['margem_bruta_percentual'], 2, ',', '.') }}%</td>
                </tr>
            </table>
            <table class="table" style="margin-top: 8px;">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Codigo barras</th>
                        <th>Qtd vendida</th>
                        <th>Receita</th>
                        <th>Custo</th>
                        <th>Lucro bruto</th>
                        <th>Margem (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($margemProdutos as $item)
                        <tr>
                            <td>{{ $item->nome_produto }}</td>
                            <td>{{ $item->codigo_barras ?: '-' }}</td>
                            <td>{{ $item->quantidade_total }}</td>
                            <td>R$ {{ number_format($item->receita_total, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($item->custo_total, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($item->lucro_bruto, 2, ',', '.') }}</td>
                            <td>{{ number_format($item->margem_percentual, 2, ',', '.') }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="muted">Nenhuma venda com itens detalhados no periodo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if ($mostrar['dre_mensal'])
        <div class="section">
            <h2>DRE simplificada mensal</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Mes</th>
                        <th>Receita</th>
                        <th>Custos (CMV)</th>
                        <th>Despesas</th>
                        <th>Resultado</th>
                        <th>Margem resultado (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dreMensal as $item)
                        <tr>
                            <td>{{ $item['mes'] }}</td>
                            <td>R$ {{ number_format($item['receita'], 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($item['custos'], 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($item['despesas'], 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($item['resultado'], 2, ',', '.') }}</td>
                            <td>{{ number_format($item['margem_resultado_percentual'], 2, ',', '.') }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="muted">Nenhum dado para montar a DRE no periodo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if ($mostrar['conciliacao_recebimentos'])
        <div class="section">
            <h2>Conciliacao de recebimentos</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Metodo</th>
                        <th>Previsto (vendas)</th>
                        <th>Declarado (fechamentos)</th>
                        <th>Diferenca</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($conciliacaoRecebimentos['linhas'] as $linha)
                        <tr>
                            <td>{{ $linha['metodo'] }}</td>
                            <td>R$ {{ number_format($linha['previsto'], 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($linha['declarado'], 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($linha['diferenca'], 2, ',', '.') }}</td>
                            <td>{{ $linha['status'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="muted">Nenhum dado encontrado para conciliacao no periodo.</td>
                        </tr>
                    @endforelse
                    <tr>
                        <th>TOTAL</th>
                        <th>R$ {{ number_format($conciliacaoRecebimentos['totais']['previsto'], 2, ',', '.') }}</th>
                        <th>R$ {{ number_format($conciliacaoRecebimentos['totais']['declarado'], 2, ',', '.') }}</th>
                        <th>R$ {{ number_format($conciliacaoRecebimentos['totais']['diferenca'], 2, ',', '.') }}</th>
                        <th>{{ $conciliacaoRecebimentos['totais']['status'] }}</th>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    @if ($mostrar['por_centro'])
        <div class="section">
            <h2>Resumo por centro de custo</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Centro de custo</th>
                        <th>Tipo</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($porCentro as $item)
                        <tr>
                            <td>{{ $item->centro_custo }}</td>
                            <td>{{ $item->tipo }}</td>
                            <td>R$ {{ number_format($item->total, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="muted">Nenhum dado no periodo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if ($mostrar['por_tipo'])
        <div class="section">
            <h2>Resumo por tipo</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($porTipo as $item)
                        <tr>
                            <td>{{ $item->tipo }}</td>
                            <td>R$ {{ number_format($item->total, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="muted">Nenhum dado no periodo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if ($mostrar['fechamento_caixa'])
        <div class="section">
            <h2>Fechamento de caixa</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Funcionario</th>
                        <th>Fundo de caixa</th>
                        <th>Dinheiro</th>
                        <th>Cartao</th>
                        <th>PIX</th>
                        <th>Outros</th>
                        <th>Outras saidas</th>
                        <th>Saldo final</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fechamentos as $fechamento)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($fechamento->data_fechamento)->format('d/m/Y') }}</td>
                            <td>{{ optional($fechamento->usuario)->nome ?? '-' }}</td>
                            <td>R$ {{ number_format($fechamento->saldo_inicial, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($fechamento->valor_dinheiro, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($fechamento->valor_cartao, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($fechamento->valor_pix, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($fechamento->valor_outros, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($fechamento->total_saidas, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($fechamento->saldo_final, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="muted">Nenhum fechamento de caixa encontrado no periodo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if ($mostrar['auditoria'])
        <div class="section">
            <h2>Auditoria</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Usuario</th>
                        <th>Acao</th>
                        <th>Descricao</th>
                        <th>Rota</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($auditorias as $log)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}</td>
                            <td>{{ optional($log->usuario)->nome ?? '-' }}</td>
                            <td>{{ $log->acao }}</td>
                            <td>{{ $log->descricao ?? '-' }}</td>
                            <td>{{ $log->rota ?? $log->url }}</td>
                            <td>{{ $log->ip ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="muted">Nenhum registro encontrado no periodo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if ($mostrar['lancamentos'])
        <div class="section">
            <h2>Lancamentos</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Descricao</th>
                        <th>Centro de custo</th>
                        <th>Tipo</th>
                        <th>Responsavel</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lancamentos as $lancamento)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($lancamento->dt_faturamento)->format('d/m/Y') }}</td>
                            <td>{{ $lancamento->descricao ?? '-' }}</td>
                            <td>{{ optional($lancamento->centroCusto)->centro_custo ?? '-' }}</td>
                            <td>{{ optional(optional($lancamento->centroCusto)->tipo)->tipo ?? '-' }}</td>
                            <td>{{ optional($lancamento->usuario)->nome ?? '-' }}</td>
                            <td>R$ {{ number_format($lancamento->valor, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="muted">Nenhum lancamento encontrado no periodo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</body>
</html>
