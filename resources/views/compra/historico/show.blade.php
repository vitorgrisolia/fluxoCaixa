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
                    <strong>Turno</strong>
                    <div>{{ $compra->id_turno ? '#'.$compra->id_turno : '-' }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Parcelamento</strong>
                    <div>{{ $compra->dividir_valor === 'sim' ? ($compra->parcelas . 'x') : 'Nao' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h2 class="h5">Itens da venda</h2>

            @if($compra->itens->isEmpty())
                <p class="text-muted mb-0">Esta compra nao possui itens detalhados.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Lote</th>
                                <th>Codigo de barras</th>
                                <th>Qtd</th>
                                <th>Unitario venda</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($compra->itens as $item)
                                <tr>
                                    <td>{{ $item->nome_produto }}</td>
                                    <td>{{ $item->lote ?: '-' }}</td>
                                    <td>{{ $item->codigo_barras ?: '-' }}</td>
                                    <td>{{ $item->quantidade }}</td>
                                    <td>R$ {{ number_format($item->valor_unitario_venda, 2, ',', '.') }}</td>
                                    <td>R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
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
