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
@endsection
