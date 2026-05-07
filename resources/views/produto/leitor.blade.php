@extends('layouts.base')

@section('conteudo')
@php
    $podeDefinirPrecoRapido = auth()->user() && auth()->user()->possuiPermissao('produto.definir_preco');
@endphp
<div class="col-md-12">
    <h1>
        <i class="h3 bi bi-upc-scan">Leitor de Produtos</i>
    </h1>

    @if (session('success'))
        <div class="alert alert-success mt-3">
            {{ session('success') }}
        </div>
    @endif

    @if (session('danger'))
        <div class="alert alert-danger mt-3">
            {{ session('danger') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <strong>Confira os campos informados:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="alert alert-info mt-3">
        Valor total selecionado: <strong>R$ {{ number_format($totalCompra, 2, ',', '.') }}</strong>
        <br>
        Itens no leitor: <strong>{{ $quantidadeItensSelecionados }}</strong>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">Leitura rapida por codigo</h5>
            <form id="form-leitor-codigo" action="{{ route('leitor.produtos.adicionar') }}" method="post" class="row g-2 align-items-end">
                @csrf
                <input type="hidden" name="filtro_retorno" value="{{ $filtro }}">
                <div class="col-md-6">
                    <label for="codigo_leitor" class="form-label">Codigo do produto (ID, lote ou codigo de barras)</label>
                    <input
                        type="text"
                        name="codigo"
                        id="codigo_leitor"
                        class="form-control"
                        autocomplete="off"
                        placeholder="Bipe o codigo no leitor"
                    >
                </div>
                <div class="col-md-2">
                    <label for="quantidade_codigo" class="form-label">Quantidade</label>
                    <input
                        type="number"
                        name="quantidade"
                        id="quantidade_codigo"
                        class="form-control"
                        min="1"
                        value="1"
                        required
                    >
                </div>
                <div class="col-md-4 d-grid d-md-flex gap-2">
                    <button type="submit" class="btn btn-dark">
                        Adicionar por codigo
                    </button>
                    <a href="{{ route('leitor.finalizar') }}" class="btn btn-success {{ $totalCompra <= 0 ? 'disabled' : '' }}">
                        Finalizar compra
                    </a>
                    <button type="submit" form="form-zerar-leitor" class="btn btn-outline-danger">
                        Zerar leitor
                    </button>
                </div>
            </form>
            <form id="form-zerar-leitor" action="{{ route('leitor.produtos.zerar') }}" method="post">
                @csrf
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">Cadastro rapido de produto no PDV</h5>
            <p class="text-muted small mb-3">
                Use esta opcao quando o codigo lido nao estiver cadastrado. O produto ja pode entrar no leitor apos salvar.
            </p>
            <form action="{{ route('leitor.produtos.cadastro-rapido') }}" method="post" class="row g-2 align-items-end">
                @csrf
                <input type="hidden" name="filtro_retorno" value="{{ $filtro }}">
                <div class="col-md-3">
                    <label for="codigo_barras_rapido" class="form-label">Codigo de barras*</label>
                    <input
                        type="text"
                        name="codigo_barras"
                        id="codigo_barras_rapido"
                        class="form-control"
                        value="{{ old('codigo_barras', $filtro) }}"
                        required
                    >
                </div>
                <div class="col-md-3">
                    <label for="nome_rapido" class="form-label">Nome*</label>
                    <input
                        type="text"
                        name="nome"
                        id="nome_rapido"
                        class="form-control"
                        value="{{ old('nome') }}"
                        required
                    >
                </div>
                <div class="col-md-2">
                    <label for="lote_rapido" class="form-label">Lote</label>
                    <input
                        type="text"
                        name="lote"
                        id="lote_rapido"
                        class="form-control"
                        value="{{ old('lote') }}"
                    >
                </div>
                <div class="col-md-2">
                    <label for="tipo_quantidade_rapido" class="form-label">Tipo</label>
                    <select name="tipo_quantidade" id="tipo_quantidade_rapido" class="form-select" required>
                        <option value="unidade" {{ old('tipo_quantidade', 'unidade') === 'unidade' ? 'selected' : '' }}>Unidade</option>
                        <option value="caixa" {{ old('tipo_quantidade') === 'caixa' ? 'selected' : '' }}>Caixa</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="quantidade_inicial_rapido" class="form-label">Qtd inicial*</label>
                    <input
                        type="number"
                        name="quantidade_inicial"
                        id="quantidade_inicial_rapido"
                        class="form-control"
                        min="1"
                        value="{{ old('quantidade_inicial', 1) }}"
                        required
                    >
                </div>
                <div class="col-md-2">
                    <label for="estoque_minimo_rapido" class="form-label">Estoque minimo*</label>
                    <input
                        type="number"
                        name="estoque_minimo"
                        id="estoque_minimo_rapido"
                        class="form-control"
                        min="0"
                        value="{{ old('estoque_minimo', 0) }}"
                        required
                    >
                </div>
                <div class="col-md-2">
                    <label for="validade_rapido" class="form-label">Validade*</label>
                    <input
                        type="date"
                        name="validade"
                        id="validade_rapido"
                        class="form-control"
                        value="{{ old('validade', now()->addYear()->format('Y-m-d')) }}"
                        required
                    >
                </div>
                <div class="col-md-2">
                    <label for="preco_compra_rapido" class="form-label">Preco compra*</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="preco_compra"
                        id="preco_compra_rapido"
                        class="form-control"
                        value="{{ old('preco_compra', 0) }}"
                        required
                    >
                </div>
                <div class="col-md-2">
                    <label for="preco_venda_rapido" class="form-label">Preco venda</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="preco_venda"
                        id="preco_venda_rapido"
                        class="form-control"
                        value="{{ old('preco_venda', 0) }}"
                        {{ $podeDefinirPrecoRapido ? '' : 'readonly' }}
                    >
                    @if (! $podeDefinirPrecoRapido)
                        <small class="text-muted">Sem permissao para editar preco. O sistema usara o preco de compra.</small>
                    @endif
                </div>
                <div class="col-md-2">
                    <label for="adicionar_ao_leitor_rapido" class="form-label">Adicionar no leitor</label>
                    <select name="adicionar_ao_leitor" id="adicionar_ao_leitor_rapido" class="form-select">
                        <option value="sim" {{ old('adicionar_ao_leitor', 'sim') === 'sim' ? 'selected' : '' }}>Sim</option>
                        <option value="nao" {{ old('adicionar_ao_leitor') === 'nao' ? 'selected' : '' }}>Nao</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="quantidade_leitor_rapido" class="form-label">Qtd no leitor</label>
                    <input
                        type="number"
                        name="quantidade_leitor"
                        id="quantidade_leitor_rapido"
                        class="form-control"
                        min="1"
                        value="{{ old('quantidade_leitor', 1) }}"
                    >
                </div>
                <div class="col-md-2 d-grid d-md-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        Cadastrar rapido
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">Pesquisar produto</h5>
            <form method="get" action="{{ route('leitor.produtos') }}" class="row g-2 align-items-end">
                <div class="col-md-8">
                    <label for="filtro" class="form-label">Filtro por nome, lote, codigo de barras ou ID</label>
                    <input
                        type="text"
                        name="filtro"
                        id="filtro"
                        class="form-control"
                        value="{{ $filtro }}"
                        placeholder="Digite ou use o leitor de codigo"
                    >
                </div>
                <div class="col-md-4 d-grid d-md-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        Pesquisar
                    </button>
                    <a href="{{ route('leitor.produtos') }}" class="btn btn-outline-secondary">
                        Limpar filtro
                    </a>
                </div>
            </form>

            @if ($filtro === '')
                <p class="text-muted mt-3 mb-0">Nenhuma lista completa e exibida. Use a pesquisa para localizar o produto.</p>
            @elseif ($resultadosBusca->isEmpty())
                <p class="text-danger mt-3 mb-0">Nenhum produto encontrado para "{{ $filtro }}".</p>
            @else
                <div class="table-responsive mt-3">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Produto</th>
                                <th>Lote</th>
                                <th>Cod. barras</th>
                                <th>Estoque</th>
                                <th>Valor de venda</th>
                                <th>Adicionar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($resultadosBusca as $produto)
                                <tr>
                                    <td>{{ $produto->id_produto }}</td>
                                    <td>{{ $produto->nome }}</td>
                                    <td>{{ $produto->lote ?: '-' }}</td>
                                    <td>{{ $produto->codigo_barras ?: '-' }}</td>
                                    <td>{{ $produto->quantidade }}</td>
                                    <td>R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}</td>
                                    <td>
                                        <form action="{{ route('leitor.produtos.adicionar') }}" method="post" class="d-flex gap-2">
                                            @csrf
                                            <input type="hidden" name="id_produto" value="{{ $produto->id_produto }}">
                                            <input type="hidden" name="filtro_retorno" value="{{ $filtro }}">
                                            <input type="number" name="quantidade" class="form-control form-control-sm" style="max-width: 90px;" min="1" max="{{ $produto->quantidade }}" value="1" required>
                                            <button type="submit" class="btn btn-sm btn-outline-dark">Adicionar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">Produtos no leitor</h5>
            @if ($produtosSelecionados->isEmpty())
                <p class="text-muted mb-0">Nenhum produto selecionado.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Qtd</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($produtosSelecionados as $produtoSelecionado)
                                <tr>
                                    <td>{{ $produtoSelecionado->nome }}</td>
                                    <td>{{ $produtoSelecionado->quantidade_selecionada }}</td>
                                    <td>R$ {{ number_format($produtoSelecionado->total_item_selecionado, 2, ',', '.') }}</td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            <form action="{{ route('leitor.produtos.decrementar', ['idProduto' => $produtoSelecionado->id_produto]) }}" method="post">
                                                @csrf
                                                <input type="hidden" name="filtro_retorno" value="{{ $filtro }}">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary">-</button>
                                            </form>
                                            <form action="{{ route('leitor.produtos.incrementar', ['idProduto' => $produtoSelecionado->id_produto]) }}" method="post">
                                                @csrf
                                                <input type="hidden" name="filtro_retorno" value="{{ $filtro }}">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary">+</button>
                                            </form>
                                            <form action="{{ route('leitor.produtos.remover', ['idProduto' => $produtoSelecionado->id_produto]) }}" method="post">
                                                @csrf
                                                <input type="hidden" name="filtro_retorno" value="{{ $filtro }}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Remover</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    (function () {
        const campoCodigo = document.getElementById('codigo_leitor');
        if (!campoCodigo) {
            return;
        }

        campoCodigo.focus();

        campoCodigo.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                document.getElementById('form-leitor-codigo').submit();
            }
        });
    })();
</script>
@endsection
