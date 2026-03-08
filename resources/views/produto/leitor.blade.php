@extends('layouts.base')

@section('conteudo')
<div class="col-md-12">
    <h1>
        <i class="bi bi-upc-scan"></i>
        Leitor de Produtos
    </h1>

    <div class="alert alert-info">
        Valor total para compra: <strong>R$ {{ number_format($totalCompra, 2, ',', '.') }}</strong>
    </div>

    <table class="table table-striped table-border table-hover align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Quantidade</th>
                <th>Valor do produto</th>
                <th>Total do item</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($produtos as $produto)
                <tr>
                    <td>{{ $produto->id_produto }}</td>
                    <td>{{ $produto->nome }}</td>
                    <td>
                        {{ $produto->quantidade }}
                        {{ $produto->tipo_quantidade === 'caixa' ? 'caixa(s)' : 'unidade(s)' }}
                    </td>
                    <td>R$ {{ number_format($produto->preco_compra, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($produto->preco_compra * $produto->quantidade, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Nenhum produto cadastrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
