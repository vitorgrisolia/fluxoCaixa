@extends('layouts.base')

@section('conteudo')
<div class="col-12">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 mb-1">Home</h1>
            <p class="text-muted mb-0">Resumo rapido da sua movimentacao financeira.</p>
        </div>
        <a href="{{ route('lancamento.create') }}" class="btn btn-dark">
            <i class="bi bi-plus-lg"></i>
            Novo lancamento
        </a>
    </div>
</div>

<div class="col-12 col-md-6 col-xl-3">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
            <p class="text-muted mb-1">Lancamentos</p>
            <h3 class="mb-0">{{ $totalLancamentos }}</h3>
        </div>
    </div>
</div>

<div class="col-12 col-md-6 col-xl-3">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
            <p class="text-muted mb-1">Entradas</p>
            <h3 class="mb-0 text-success">R$ {{ number_format($totalEntradas, 2, ',', '.') }}</h3>
        </div>
    </div>
</div>

<div class="col-12 col-md-6 col-xl-3">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
            <p class="text-muted mb-1">Saidas</p>
            <h3 class="mb-0 text-danger">R$ {{ number_format($totalSaidas, 2, ',', '.') }}</h3>
        </div>
    </div>
</div>

<div class="col-12 col-md-6 col-xl-3">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
            <p class="text-muted mb-1">Saldo atual</p>
            <h3 class="mb-0 {{ $saldoAtual >= 0 ? 'text-success' : 'text-danger' }}">
                R$ {{ number_format($saldoAtual, 2, ',', '.') }}
            </h3>
        </div>
    </div>
</div>

<div class="col-12 mt-3">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>Ultimos lancamentos</strong>
            <a href="{{ route('lancamento.index') }}" class="btn btn-sm btn-outline-dark">Ver todos</a>
        </div>
        <div class="card-body p-0">
            @if($ultimosLancamentos->isEmpty())
                <div class="p-3">
                    <div class="alert alert-secondary mb-0">
                        Nenhum lancamento cadastrado ate o momento.
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Centro de custo</th>
                                <th>Tipo</th>
                                <th>Descricao</th>
                                <th>Valor</th>
                                <th class="text-end">Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ultimosLancamentos as $lancamento)
                                <tr>
                                    <td>{{ $lancamento->dt_faturamento->format('d/m/Y') }}</td>
                                    <td>{{ $lancamento->centroCusto->centro_custo ?? '-' }}</td>
                                    <td>{{ $lancamento->centroCusto->tipo->tipo ?? '-' }}</td>
                                    <td>{{ $lancamento->descricao ?? '-' }}</td>
                                    <td>R$ {{ number_format($lancamento->valor, 2, ',', '.') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('lancamento.show', ['id' => $lancamento->id_lancamento]) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            Ver
                                        </a>
                                        <a href="{{ route('lancamento.edit', ['id' => $lancamento->id_lancamento]) }}"
                                            class="btn btn-sm btn-outline-success">
                                            Editar
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
