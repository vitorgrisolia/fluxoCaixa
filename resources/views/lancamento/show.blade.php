@extends('layouts.base')

@section('conteudo')
<div class="col-12">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 mb-1">Detalhes do lancamento</h1>
            <p class="text-muted mb-0">Resumo completo do registro selecionado.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('lancamento.edit', ['id' => $lancamento->id_lancamento]) }}" class="btn btn-outline-primary">
                Editar
            </a>
            <a href="{{ route('lancamento.index') }}" class="btn btn-outline-secondary">
                Voltar
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1">Lancamento #{{ $lancamento->id_lancamento }}</p>
                            <h2 class="h5 mb-0">{{ $lancamento->descricao ?? 'Sem descricao' }}</h2>
                        </div>
                        <span class="badge {{ ($lancamento->centroCusto->tipo->tipo ?? '') === 'entrada' ? 'bg-success' : 'bg-danger' }}">
                            {{ ucfirst($lancamento->centroCusto->tipo->tipo ?? 'N/A') }}
                        </span>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <small class="text-muted">Data do faturamento</small>
                            <p class="mb-0 fw-semibold">{{ $lancamento->dt_faturamento->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted">Centro de custo</small>
                            <p class="mb-0 fw-semibold">{{ $lancamento->centroCusto->centro_custo ?? '-' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted">Valor</small>
                            <p class="mb-0 fw-semibold">R$ {{ number_format($lancamento->valor, 2, ',', '.') }}</p>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted">Tipo</small>
                            <p class="mb-0 fw-semibold">{{ ucfirst($lancamento->centroCusto->tipo->tipo ?? '-') }}</p>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Observacoes</small>
                            <p class="mb-0">{{ $lancamento->observacoes ?: 'Nenhuma observacao registrada.' }}</p>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Arquivo</small>
                            @if (!empty($lancamento->arquivo))
                                <div>
                                    <a href="{{ Storage::url($lancamento->arquivo) }}" target="_blank" class="btn btn-sm btn-outline-dark mt-1">
                                        Abrir arquivo
                                    </a>
                                </div>
                            @else
                                <p class="mb-0">Nenhum arquivo anexado.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <p class="text-muted mb-1">Criado em</p>
                    <h3 class="h5 mb-0">{{ $lancamento->created_at->format('d/m/Y') }}</h3>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Atualizado em</p>
                    <h3 class="h5 mb-0">{{ $lancamento->updated_at->format('d/m/Y') }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
