@extends('layouts.base')
@section('conteudo')
<div>
<h1 class="mb-4">Controle Financeiro</h1>

<form action="{{ route('controle-financeiro.index') }}" method="GET">
    <div class="form-group">
        <label for="data_inicio" class="mb-1">Data Início:</label>
        <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="{{ $dataInicio }}">
    </div>
    <div class="form-group">
        <label for="data_fim" class="mb-1 mt-2">Data Fim:</label>
        <input type="date" name="data_fim" id="data_fim" class="form-control" value="{{ $dataFim }}">
    </div>
    <button type="submit" class="btn btn-primary mt-3 mb-3">Filtrar</button>
</form>

{{--ALERTA DE SALDO--}}
@include('controleFinanceiro.partials.alerta')
{{--CARDS--}}
@include('controleFinanceiro.partials.cards')
{{--TABELA--}}
@include('controleFinanceiro.partials.tabela')

</div>
@endsection
