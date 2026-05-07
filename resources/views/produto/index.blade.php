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

    @if ($totalVencimentoCritico > 0)
        <div class="alert alert-danger">
            {{ $totalVencimentoCritico }} produto(s) com validade critica (0 a 7 dias).
        </div>
    @endif

    @if ($totalAbaixoEstoqueMinimo > 0)
        <div class="alert alert-warning">
            {{ $totalAbaixoEstoqueMinimo }} produto(s) no limite/abaixo do estoque minimo.
        </div>
    @endif

    @if ($produtosReposicao->isNotEmpty())
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h2 class="h6 mb-3">Sugestao de reposicao (top 10)</h2>
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Lote</th>
                                <th>Qtd atual</th>
                                <th>Estoque minimo</th>
                                <th>Sugestao</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($produtosReposicao as $produtoReposicao)
                                @php
                                    $faltante = max((int) $produtoReposicao->estoque_minimo - (int) $produtoReposicao->quantidade, 0);
                                @endphp
                                <tr>
                                    <td>{{ $produtoReposicao->nome }}</td>
                                    <td>{{ $produtoReposicao->lote ?: '-' }}</td>
                                    <td>{{ $produtoReposicao->quantidade }}</td>
                                    <td>{{ $produtoReposicao->estoque_minimo }}</td>
                                    <td>
                                        @if ($faltante > 0)
                                            Repor pelo menos {{ $faltante }} unidade(s).
                                        @else
                                            Estoque no limite minimo, sugerido reforco preventivo.
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
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
                <th>Codigo de barras</th>
                <th>Quantidade</th>
                <th>Estoque minimo</th>
                <th>Alerta de estoque</th>
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
                    $faltanteEstoque = max((int) $produto->estoque_minimo - (int) $produto->quantidade, 0);
                @endphp
                <tr>
                    <td>{{ $produto->id_produto }}</td>
                    <td>{{ $produto->nome }}</td>
                    <td>{{ $produto->lote ?: '-' }}</td>
                    <td>{{ $produto->codigo_barras ?: '-' }}</td>
                    <td>
                        {{ $produto->quantidade }}
                        {{ $produto->tipo_quantidade === 'caixa' ? 'caixa(s)' : 'unidade(s)' }}
                    </td>
                    <td>{{ $produto->estoque_minimo }}</td>
                    <td>
                        @if($produto->quantidade <= 0)
                            <span class="badge bg-danger">Sem estoque</span>
                            <div class="small text-danger">Repor {{ max($produto->estoque_minimo, 1) }} unidade(s).</div>
                        @elseif($produto->quantidade <= $produto->estoque_minimo)
                            <span class="badge bg-warning text-dark">Abaixo do minimo</span>
                            @if ($faltanteEstoque > 0)
                                <div class="small text-warning">Repor {{ $faltanteEstoque }} unidade(s).</div>
                            @else
                                <div class="small text-warning">No limite minimo.</div>
                            @endif
                        @else
                            <span class="badge bg-success">Estoque ok</span>
                        @endif
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
                        @elseif($diasParaVencimento <= 7)
                            <span class="badge bg-danger">
                                Critico: vence em {{ $diasParaVencimento }} dia(s)
                            </span>
                        @elseif($diasParaVencimento <= 30)
                            <span class="badge bg-warning text-dark">
                                Atencao: vence em {{ $diasParaVencimento }} dia(s)
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
                    <td colspan="{{ $isAdmin ? 12 : 11 }}" class="text-center">Nenhum produto cadastrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
