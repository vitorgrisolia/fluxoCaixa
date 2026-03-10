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
@endsection
