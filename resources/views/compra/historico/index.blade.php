@extends('layouts.base')

@section('conteudo')
<div class="col-12">
    <h1>
        <i class="h3 bi bi-clock-history">Historico de compras</i>
    </h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('danger'))
        <div class="alert alert-danger">
            {{ session('danger') }}
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-striped table-border table-hover">
            <thead>
                <tr>
                    <th>Acoes</th>
                    <th>Data</th>
                    <th>Forma de pagamento</th>
                    <th>Total</th>
                    <th>Itens</th>
                    <th>Parcelas</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($compras as $compra)
                    <tr>
                        <td>
                            <a href="{{ route('leitor.historico.show', ['id' => $compra->id_compra]) }}" class="btn btn-dark btn-sm">
                                Ver
                            </a>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($compra->data_compra)->format('d/m/Y H:i') }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $compra->forma_pagamento)) }}</td>
                        <td>R$ {{ number_format($compra->valor_total, 2, ',', '.') }}</td>
                        <td>{{ $compra->itens_count }}</td>
                        <td>{{ $compra->dividir_valor === 'sim' ? ($compra->parcelas . 'x') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            Nenhuma compra registrada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
