@extends('layouts.base')

@section('conteudo')
<div class="col-12">
    <h1>
        <i class="bi bi-receipt"></i>
        Detalhe da compra
    </h1>

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <strong>Data</strong>
                    <div>{{ \Carbon\Carbon::parse($compra->data_compra)->format('d/m/Y H:i') }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Valor total</strong>
                    <div>R$ {{ number_format($compra->valor_total, 2, ',', '.') }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Forma de pagamento</strong>
                    <div>{{ ucfirst(str_replace('_', ' ', $compra->forma_pagamento)) }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Parcelamento</strong>
                    <div>{{ $compra->dividir_valor === 'sim' ? ($compra->parcelas . 'x') : 'Nao' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <a href="{{ route('leitor.historico.edit', ['id' => $compra->id_compra]) }}" class="btn btn-success">
            Editar
        </a>
        <a href="{{ route('leitor.historico.index') }}" class="btn btn-outline-secondary">
            Voltar
        </a>
    </div>
</div>
@endsection
