@extends('layouts.base')

@section('conteudo')
<div class="col-12">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-1">Dashboard</h1>
            <p class="text-muted mb-0">Visao geral das principais telas do sistema.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('relatorios.index') }}" class="btn btn-dark btn-sm">
                <i class="bi bi-graph-up-arrow"></i>
                Relatorios
            </a>
            <a href="{{ route('controle-financeiro.index') }}" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-bank"></i>
                Controle financeiro
            </a>
        </div>
    </div>
</div>

<div class="col-12">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h1 class="h4 mb-2">Seja bem vindo, {{ Auth::user()->nome }}.</h1>
            @php
                \Carbon\Carbon::setLocale('pt_BR');
                $dataAtual = \Carbon\Carbon::now()->translatedFormat('l, d/m/Y');
            @endphp
            <p class="mb-0 text-muted">Hoje e {{ $dataAtual }}.</p>
        </div>
    </div>
</div>

<div class="col-12 mt-3">
    <div class="alert alert-danger">
        <strong>Urgente:</strong>
        <ul class="mb-0 mt-2">
            @if($totalVencidos > 0)
                <li>
                    {{ $totalVencidos }} produto(s) vencido(s).
                    <a href="{{ route('produto.index') }}" class="alert-link">Ver produtos</a>
                </li>
            @endif
            @if($totalVencendo > 0)
                <li>
                    {{ $totalVencendo }} produto(s) vencendo nos proximos 30 dias.
                    <a href="{{ route('produto.index') }}" class="alert-link">Ver produtos</a>
                </li>
            @endif
            @if($saldoMes < 0)
                <li>
                    Saldo financeiro negativo no periodo
                    ({{ $inicioMes->format('d/m/Y') }} a {{ $fimMes->format('d/m/Y') }}):
                    <strong>R$ {{ number_format($saldoMes, 2, ',', '.') }}</strong>.
                    <a href="{{ route('controle-financeiro.index') }}" class="alert-link">Ver controle financeiro</a>
                </li>
            @endif
            @if($totalVencidos === 0 && $totalVencendo === 0 && $saldoMes >= 0)
                <li>Nenhum alerta urgente no momento.</li>
            @endif
        </ul>
    </div>
</div>

<div class="col-12 mt-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
        <h2 class="h5 mb-0">Resumo geral</h2>
        <span class="text-muted small">
            Periodo: {{ $inicioMes->format('d/m/Y') }} a {{ $fimMes->format('d/m/Y') }}
        </span>
    </div>
</div>

<div class="col-12">
    <div class="row g-3">
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Usuarios</p>
                    <h3 class="mb-0">{{ $totalUsuarios }}</h3>
                    <p class="small text-muted mb-0">
                        Admins: {{ $totalAdmins }} | Funcionarios: {{ $totalFuncionarios }}
                    </p>
                    <a href="{{ route('usuario.index') }}" class="btn btn-sm btn-outline-dark mt-3">
                        Ver usuarios
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Produtos</p>
                    <h3 class="mb-0">{{ $totalProdutos }}</h3>
                    <p class="small text-muted mb-0">
                        Itens em estoque: {{ $totalItensEstoque }} | Sem estoque: {{ $produtosSemEstoque }}
                    </p>
                    <p class="small text-muted mb-0">
                        Vencidos: {{ $totalVencidos }} | Vencendo 30 dias: {{ $totalVencendo }}
                    </p>
                    <a href="{{ route('produto.index') }}" class="btn btn-sm btn-outline-dark mt-3">
                        Ver produtos
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Estoque</p>
                    <h3 class="mb-0">R$ {{ number_format($valorTotalEstoqueVenda, 2, ',', '.') }}</h3>
                    <p class="small text-muted mb-0">
                        Movimentacoes no mes: {{ $movimentacoesMes }}
                    </p>
                    <p class="small text-muted mb-0">
                        Entradas: {{ $entradasEstoqueMes }} | Saidas: {{ $saidasEstoqueMes }}
                    </p>
                    <a href="{{ route('estoque.index') }}" class="btn btn-sm btn-outline-dark mt-3">
                        Ver controle de estoque
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Lancamentos</p>
                    <h3 class="mb-0">{{ $lancamentosMes }}</h3>
                    @if($ultimoLancamento)
                        <p class="small text-muted mb-0">
                            Ultimo: {{ $ultimoLancamento->dt_faturamento->format('d/m/Y') }}
                            - R$ {{ number_format($ultimoLancamento->valor, 2, ',', '.') }}
                        </p>
                    @else
                        <p class="small text-muted mb-0">Nenhum lancamento registrado.</p>
                    @endif
                    <a href="{{ route('lancamento.index') }}" class="btn btn-sm btn-outline-dark mt-3">
                        Ver lancamentos
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Controle financeiro</p>
                    <h3 class="mb-0 {{ $saldoMes >= 0 ? 'text-success' : 'text-danger' }}">
                        R$ {{ number_format($saldoMes, 2, ',', '.') }}
                    </h3>
                    <p class="small text-muted mb-0">
                        Entradas: R$ {{ number_format($totalEntradas, 2, ',', '.') }}
                        | Saidas: R$ {{ number_format($totalSaidas, 2, ',', '.') }}
                    </p>
                    <p class="small text-muted mb-0">
                        Compra: R$ {{ number_format($totalCompraPeriodo, 2, ',', '.') }}
                        | Venda: R$ {{ number_format($totalVendaPeriodo, 2, ',', '.') }}
                    </p>
                    <p class="small text-muted mb-0">
                        Margem estimada: {{ number_format($margemEstoque, 2, ',', '.') }}%
                    </p>
                    <a href="{{ route('controle-financeiro.index') }}" class="btn btn-sm btn-outline-dark mt-3">
                        Ver controle financeiro
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Centros e tipos</p>
                    <h3 class="mb-0">{{ $totalCentros }}</h3>
                    <p class="small text-muted mb-0">Tipos cadastrados: {{ $totalTipos }}</p>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <a href="{{ route('centro.index') }}" class="btn btn-sm btn-outline-dark">
                            Centro de custo
                        </a>
                        <a href="{{ route('tipo.index') }}" class="btn btn-sm btn-outline-dark">
                            Tipos
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Compras (historico)</p>
                    <h3 class="mb-0">{{ $comprasMes }}</h3>
                    <p class="small text-muted mb-0">
                        Total: R$ {{ number_format($totalComprasMes, 2, ',', '.') }}
                        | Ticket medio: R$ {{ number_format($ticketMedioCompras, 2, ',', '.') }}
                    </p>
                    <p class="small text-muted mb-0">
                        Dinheiro: R$ {{ number_format($comprasDinheiro, 2, ',', '.') }}
                        | Cartao: R$ {{ number_format($comprasCartao, 2, ',', '.') }}
                        | PIX: R$ {{ number_format($comprasPix, 2, ',', '.') }}
                    </p>
                    <p class="small text-muted mb-0">
                        Outros: R$ {{ number_format($comprasOutros, 2, ',', '.') }}
                    </p>
                    <span class="badge bg-light text-dark mt-3">Dados dos funcionarios</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Fechamento de caixa</p>
                    <h3 class="mb-0">{{ $fechamentosMes }}</h3>
                    @if($ultimoFechamento)
                        <p class="small text-muted mb-0">
                            Ultimo: {{ \Carbon\Carbon::parse($ultimoFechamento->data_fechamento)->format('d/m/Y') }}
                            - R$ {{ number_format($ultimoFechamento->saldo_final, 2, ',', '.') }}
                        </p>
                    @else
                        <p class="small text-muted mb-0">Nenhum fechamento registrado.</p>
                    @endif
                    <a href="{{ route('fechamento-caixa.index') }}" class="btn btn-sm btn-outline-dark mt-3">
                        Ver fechamentos
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Auditoria</p>
                    <h3 class="mb-0">{{ $auditoriasSemana }}</h3>
                    @if($ultimaAuditoria)
                        <p class="small text-muted mb-0">
                            Ultima: {{ $ultimaAuditoria->acao }}
                            - {{ optional($ultimaAuditoria->usuario)->nome ?? 'Sistema' }}
                            em {{ \Carbon\Carbon::parse($ultimaAuditoria->created_at)->format('d/m/Y H:i') }}
                        </p>
                    @else
                        <p class="small text-muted mb-0">Nenhum log registrado.</p>
                    @endif
                    <a href="{{ route('auditoria.index') }}" class="btn btn-sm btn-outline-dark mt-3">
                        Ver auditoria
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Relatorios</p>
                    <h3 class="mb-0">{{ $inicioMes->format('d/m') }} - {{ $fimMes->format('d/m') }}</h3>
                    <p class="small text-muted mb-0">
                        Resumo, centro de custo, tipo, fechamento, auditoria e lancamentos.
                    </p>
                    <a href="{{ route('relatorios.index') }}" class="btn btn-sm btn-outline-dark mt-3">
                        Gerar relatorio
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Configuracoes gerais</p>
                    @if($configuracao)
                        <h3 class="mb-0">{{ $configuracao->nome_sistema }}</h3>
                        <p class="small text-muted mb-0">
                            Empresa: {{ $configuracao->nome_empresa ?: '-' }}
                        </p>
                        <p class="small text-muted mb-0">
                            Moeda: {{ $configuracao->moeda ?? 'BRL' }}
                        </p>
                    @else
                        <h3 class="mb-0">Nao configurado</h3>
                        <p class="small text-muted mb-0">Defina os dados do sistema.</p>
                    @endif
                    <a href="{{ route('configuracoes.index') }}" class="btn btn-sm btn-outline-dark mt-3">
                        Ajustar configuracoes
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-12 mt-3">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Fechamentos de Caixa Recentes</h2>
                <a href="{{ route('fechamento-caixa.index') }}" class="btn btn-outline-secondary btn-sm">
                    Ver todos
                </a>
            </div>

            @if($fechamentosRecentes->isEmpty())
                <p class="text-muted mb-0">Nenhum fechamento registrado ainda.</p>
            @else
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
                                <th>Saldo final</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fechamentosRecentes as $fechamento)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($fechamento->data_fechamento)->format('d/m/Y') }}</td>
                                    <td>{{ optional($fechamento->usuario)->nome ?? '---' }}</td>
                                    <td>R$ {{ number_format($fechamento->saldo_inicial, 2, ',', '.') }}</td>
                                    <td>R$ {{ number_format($fechamento->valor_dinheiro, 2, ',', '.') }}</td>
                                    <td>R$ {{ number_format($fechamento->valor_cartao, 2, ',', '.') }}</td>
                                    <td>R$ {{ number_format($fechamento->valor_pix, 2, ',', '.') }}</td>
                                    <td>R$ {{ number_format($fechamento->valor_outros, 2, ',', '.') }}</td>
                                    <td>R$ {{ number_format($fechamento->saldo_final, 2, ',', '.') }}</td>
                                    <td>
                                        <a href="{{ route('fechamento-caixa.show', ['id' => $fechamento->id_fechamento]) }}" class="btn btn-dark btn-sm">
                                            Ver
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
