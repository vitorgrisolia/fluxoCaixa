@extends('layouts.base')

@section('conteudo')
<div class="col-12">
    <h1>
        <i class="bi bi-graph-up-arrow"></i>
        Relatorios - Visao geral por periodo
    </h1>

    @php
        $secoesSelecionadas = array_keys(array_filter($mostrar));
    @endphp

    <form action="{{ route('relatorios.index') }}" method="get" class="row g-2 align-items-end mb-3">
        <div class="col-md-3">
            <label for="data_inicio" class="form-label">Data inicio</label>
            <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="{{ $dataInicio }}">
        </div>
        <div class="col-md-3">
            <label for="data_fim" class="form-label">Data fim</label>
            <input type="date" name="data_fim" id="data_fim" class="form-control" value="{{ $dataFim }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">O que deseja ver</label>
            <div class="d-flex flex-wrap gap-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="secoes[]" id="secao_resumo" value="resumo" {{ $mostrar['resumo'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="secao_resumo">Resumo</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="secoes[]" id="secao_por_centro" value="por_centro" {{ $mostrar['por_centro'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="secao_por_centro">Por centro de custo</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="secoes[]" id="secao_por_tipo" value="por_tipo" {{ $mostrar['por_tipo'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="secao_por_tipo">Por tipo</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="secoes[]" id="secao_fechamento_caixa" value="fechamento_caixa" {{ $mostrar['fechamento_caixa'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="secao_fechamento_caixa">Fechamento de caixa</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="secoes[]" id="secao_auditoria" value="auditoria" {{ $mostrar['auditoria'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="secao_auditoria">Auditoria</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="secoes[]" id="secao_lancamentos" value="lancamentos" {{ $mostrar['lancamentos'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="secao_lancamentos">Lancamentos</label>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </div>
        <div class="col-12 d-flex flex-wrap gap-2">
            <a href="{{ route('relatorios.index', ['data_inicio' => $dataInicio, 'data_fim' => $dataFim]) }}" class="btn btn-outline-secondary">
                Mostrar tudo
            </a>
            <a href="{{ route('relatorios.export.csv', array_merge(['data_inicio' => $dataInicio, 'data_fim' => $dataFim], ['secoes' => $secoesSelecionadas])) }}" class="btn btn-outline-secondary">
                Exportar CSV
            </a>
            <a href="{{ route('relatorios.export.pdf', array_merge(['data_inicio' => $dataInicio, 'data_fim' => $dataFim], ['secoes' => $secoesSelecionadas])) }}" class="btn btn-outline-dark">
                Exportar PDF
            </a>
        </div>
    </form>

    @if ($mostrar['resumo'])
        <div class="row g-3">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="text-muted">Entradas</div>
                        <div class="h5 mb-0">R$ {{ number_format($entrada, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="text-muted">Saidas</div>
                        <div class="h5 mb-0">R$ {{ number_format($saida, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="text-muted">Saldo</div>
                        <div class="h5 mb-0">R$ {{ number_format($saldo, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="text-muted">Lancamentos</div>
                        <div class="h5 mb-0">{{ $lancamentos->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($mostrar['por_centro'] || $mostrar['por_tipo'])
        <div class="row g-3 mt-3">
            @if ($mostrar['por_centro'])
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h2 class="h6 mb-3">Resumo por centro de custo</h2>
                            <div class="table-responsive">
                                <table class="table table-striped table-border table-hover mb-0">
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
                                                <td colspan="3" class="text-center text-muted">
                                                    Nenhum dado no periodo.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @if ($mostrar['por_tipo'])
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h2 class="h6 mb-3">Resumo por tipo</h2>
                            <div class="table-responsive">
                                <table class="table table-striped table-border table-hover mb-0">
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
                                                <td colspan="2" class="text-center text-muted">
                                                    Nenhum dado no periodo.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if ($mostrar['fechamento_caixa'])
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <h2 class="h6 mb-3">Fechamento de caixa</h2>
                <div class="table-responsive">
                    <table class="table table-striped table-border table-hover mb-0">
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
                                    <td colspan="9" class="text-center text-muted">
                                        Nenhum fechamento de caixa encontrado no periodo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if ($mostrar['auditoria'])
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <h2 class="h6 mb-3">Auditoria</h2>
                <div class="table-responsive">
                    <table class="table table-striped table-border table-hover mb-0">
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
                                    <td colspan="6" class="text-center text-muted">
                                        Nenhum registro encontrado no periodo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if ($mostrar['lancamentos'])
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-border table-hover mb-0">
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
                                    <td colspan="6" class="text-center text-muted">
                                        Nenhum lancamento encontrado no periodo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
