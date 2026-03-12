@extends('layouts.base')

@section('conteudo')
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
