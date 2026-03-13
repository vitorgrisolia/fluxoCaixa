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

    <a href="{{ route('leitor.historico.create') }}" class="btn btn-dark mb-3 mt-3">
        Novo
    </a>

    <div class="table-responsive">
        <table class="table table-striped table-border table-hover">
            <thead>
                <tr>
                    <th>Acoes</th>
                    <th>Data</th>
                    <th>Forma de pagamento</th>
                    <th>Total</th>
                    <th>Parcelas</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($compras as $compra)
                    <tr>
                        <td class="d-flex flex-wrap gap-2">
                            <a href="{{ route('leitor.historico.show', ['id' => $compra->id_compra]) }}" class="btn btn-dark btn-sm">
                                Ver
                            </a>
                            <a href="{{ route('leitor.historico.edit', ['id' => $compra->id_compra]) }}" class="btn btn-success btn-sm">
                                Editar
                            </a>
                            <form action="{{ route('leitor.historico.destroy', ['id' => $compra->id_compra]) }}" method="post">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">
                                    Excluir
                                </button>
                            </form>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($compra->data_compra)->format('d/m/Y H:i') }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $compra->forma_pagamento)) }}</td>
                        <td>R$ {{ number_format($compra->valor_total, 2, ',', '.') }}</td>
                        <td>{{ $compra->dividir_valor === 'sim' ? ($compra->parcelas . 'x') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            Nenhuma compra registrada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
