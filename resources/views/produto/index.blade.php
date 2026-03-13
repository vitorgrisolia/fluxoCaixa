@extends('layouts.base')

@section('conteudo')
<div class="col-md-12">
    <h1 class="mb-4">
        <i class="h3 bi bi-box-seam">Cadastro de Produtos</i>
    </h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($totalVencidos > 0)
        <div class="alert alert-danger">
            Existem {{ $totalVencidos }} produto(s) vencido(s).
        </div>
    @endif

    @if ($totalVencendo > 0)
        <div class="alert alert-warning">
            Existem {{ $totalVencendo }} produto(s) vencendo nos proximos 30 dias.
        </div>
    @endif

    @php
        $isAdmin = Auth::user()->tipo_usuario === 'admin';
    @endphp

    @if ($isAdmin)
        <a href="{{ route('produto.create') }}" class="btn btn-dark mb-3 mt-3">
            Novo produto
        </a>
    @endif

    <hr>

    <table class="table table-striped table-border table-hover align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Lote</th>
                <th>Quantidade</th>
                <th>Validade</th>
                <th>Alerta de vencimento</th>
                <th>Preco compra</th>
                <th>Preco venda</th>
                @if($isAdmin)
                    <th>Acoes</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($produtos as $produto)
                @php
                    $diasParaVencimento = \Illuminate\Support\Carbon::today()->diffInDays($produto->validade, false);
                @endphp
                <tr>
                    <td>{{ $produto->id_produto }}</td>
                    <td>{{ $produto->nome }}</td>
                    <td>{{ $produto->lote ?: '-' }}</td>
                    <td>
                        {{ $produto->quantidade }}
                        {{ $produto->tipo_quantidade === 'caixa' ? 'caixa(s)' : 'unidade(s)' }}
                    </td>
                    <td>{{ $produto->validade->format('d/m/Y') }}</td>
                    <td>
                        @if($diasParaVencimento < 0)
                            <span class="badge bg-danger">
                                Vencido ha {{ abs($diasParaVencimento) }} dia(s)
                            </span>
                        @elseif($diasParaVencimento === 0)
                            <span class="badge bg-danger">
                                Vence hoje
                            </span>
                        @elseif($diasParaVencimento <= 30)
                            <span class="badge bg-warning text-dark">
                                Vence em {{ $diasParaVencimento }} dia(s)
                            </span>
                        @else
                            <span class="badge bg-success">
                                Validade ok
                            </span>
                        @endif
                    </td>
                    <td>R$ {{ number_format($produto->preco_compra, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}</td>
                    @if($isAdmin)
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('produto.edit', ['id' => $produto->id_produto]) }}" class="btn btn-sm btn-outline-primary">
                                    Editar
                                </a>
                                <form
                                    action="{{ route('produto.delete', ['id' => $produto->id_produto]) }}"
                                    method="post"
                                    onsubmit="return confirm('Deseja realmente excluir este produto?');"
                                >
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        Excluir
                                    </button>
                                </form>
                            </div>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $isAdmin ? 9 : 8 }}" class="text-center">Nenhum produto cadastrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
