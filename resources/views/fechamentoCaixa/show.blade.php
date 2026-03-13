@extends('layouts.base')

@section('conteudo')
<div class="col-12">
    <h1>
        <i class="bi bi-cash-stack"></i>
        Fechamento de Caixa
    </h1>

    @php
        $isAdmin = Auth::user()->tipo_usuario === 'admin';
    @endphp

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <strong>Data</strong>
                    <div>{{ \Carbon\Carbon::parse($fechamento->data_fechamento)->format('d/m/Y') }}</div>
                </div>
                @if ($isAdmin)
                    <div class="col-md-4">
                        <strong>Funcionario</strong>
                        <div>{{ optional($fechamento->usuario)->nome ?? '---' }}</div>
                    </div>
                @endif
                <div class="col-md-3">
                    <strong>Fundo de caixa</strong>
                    <div>R$ {{ number_format($fechamento->saldo_inicial, 2, ',', '.') }}</div>
                </div>
                <div class="col-md-3">
                    <strong>Dinheiro</strong>
                    <div>R$ {{ number_format($fechamento->valor_dinheiro, 2, ',', '.') }}</div>
                </div>
                <div class="col-md-3">
                    <strong>Cartao</strong>
                    <div>R$ {{ number_format($fechamento->valor_cartao, 2, ',', '.') }}</div>
                </div>
                <div class="col-md-3">
                    <strong>PIX</strong>
                    <div>R$ {{ number_format($fechamento->valor_pix, 2, ',', '.') }}</div>
                </div>
                <div class="col-md-3">
                    <strong>Outros</strong>
                    <div>R$ {{ number_format($fechamento->valor_outros, 2, ',', '.') }}</div>
                </div>
                <div class="col-md-3">
                    <strong>Outras saidas</strong>
                    <div>R$ {{ number_format($fechamento->total_saidas, 2, ',', '.') }}</div>
                </div>
                <div class="col-md-3">
                    <strong>Saldo final</strong>
                    <div>R$ {{ number_format($fechamento->saldo_final, 2, ',', '.') }}</div>
                </div>
                <div class="col-12">
                    <strong>Observacoes</strong>
                    <div>{{ $fechamento->observacoes ?: 'Nenhuma observacao registrada.' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <a href="{{ route('fechamento-caixa.edit', ['id' => $fechamento->id_fechamento]) }}" class="btn btn-success">
            Editar
        </a>
        <a href="{{ route('fechamento-caixa.index') }}" class="btn btn-outline-secondary">
            Voltar
        </a>
    </div>
</div>
@endsection
