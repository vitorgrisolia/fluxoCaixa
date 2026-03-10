@extends('layouts.base')

@section('conteudo')
<div class="col-md-12">
    <h1>
        <i class="bi bi-clipboard2-data"></i>
        Controle de Entradas e Saidas de Produtos
    </h1>
    <p class="text-muted">
        Filtro por dia, semana ou mes, com estoque atual e resumo de valor de venda.
    </p>

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

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Erro ao salvar movimentacao:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('estoque.index') }}" method="get" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="periodo" class="form-label">Periodo</label>
                    <select name="periodo" id="periodo" class="form-select">
                        <option value="dia" {{ $periodo === 'dia' ? 'selected' : '' }}>Dia</option>
                        <option value="semana" {{ $periodo === 'semana' ? 'selected' : '' }}>Semana</option>
                        <option value="mes" {{ $periodo === 'mes' ? 'selected' : '' }}>Mes</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="data" class="form-label">Data de referencia</label>
                    <input type="date" name="data" id="data" class="form-control" value="{{ $dataSelecionada }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-dark">
                        Filtrar periodo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Periodo selecionado</p>
                    <h5 class="mb-0">{{ $periodoLabel }}</h5>
                    <small class="text-muted">
                        {{ $inicioPeriodo->format('d/m/Y') }} ate {{ $fimPeriodo->format('d/m/Y') }}
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Entradas no periodo</p>
                    <h4 class="mb-0 text-success">{{ $totalEntradasQuantidade }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Saidas no periodo</p>
                    <h4 class="mb-0 text-danger">{{ $totalSaidasQuantidade }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Resumo valor de venda</p>
                    <h4 class="mb-0">R$ {{ number_format($resumoValorVenda, 2, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            Registrar movimentacao
        </div>
        <div class="card-body">
            <form action="{{ route('estoque.store') }}" method="post" class="row g-3 align-items-end">
                @csrf
                <input type="hidden" name="periodo_atual" value="{{ $periodo }}">
                <input type="hidden" name="data_atual" value="{{ $dataSelecionada }}">

                <div class="col-md-4">
                    <label for="id_produto" class="form-label">Produto</label>
                    <select name="id_produto" id="id_produto" class="form-select" required>
                        <option value="">Selecione</option>
                        @foreach ($produtos as $produto)
                            <option value="{{ $produto->id_produto }}" {{ old('id_produto') == $produto->id_produto ? 'selected' : '' }}>
                                #{{ $produto->id_produto }} - {{ $produto->nome }} (estoque: {{ $produto->quantidade }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="tipo_movimentacao" class="form-label">Tipo</label>
                    <select name="tipo_movimentacao" id="tipo_movimentacao" class="form-select" required>
                        <option value="entrada" {{ old('tipo_movimentacao') === 'entrada' ? 'selected' : '' }}>Entrada</option>
                        <option value="saida" {{ old('tipo_movimentacao') === 'saida' ? 'selected' : '' }}>Saida</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="quantidade" class="form-label">Quantidade</label>
                    <input type="number" name="quantidade" id="quantidade" min="1" step="1" class="form-control" value="{{ old('quantidade', 1) }}" required>
                </div>

                <div class="col-md-2">
                    <label for="data_movimentacao" class="form-label">Data</label>
                    <input type="date" name="data_movimentacao" id="data_movimentacao" class="form-control" value="{{ old('data_movimentacao', now()->format('Y-m-d')) }}" required>
                </div>

                <div class="col-md-8">
                    <label for="observacao" class="form-label">Observacao</label>
                    <input type="text" name="observacao" id="observacao" maxlength="500" class="form-control" value="{{ old('observacao') }}">
                </div>

                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">
                        Salvar movimentacao
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Estoque atual por produto</span>
            <strong>Total em estoque (valor de venda): R$ {{ number_format($valorTotalEstoqueVenda, 2, ',', '.') }}</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Lote</th>
                            <th>Quantidade</th>
                            <th>Validade</th>
                            <th>Preco venda</th>
                            <th>Valor em estoque</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($produtos as $produto)
                            <tr>
                                <td>{{ $produto->id_produto }}</td>
                                <td>{{ $produto->nome }}</td>
                                <td>{{ $produto->lote ?: '-' }}</td>
                                <td>
                                    {{ $produto->quantidade }}
                                    {{ $produto->tipo_quantidade === 'caixa' ? 'caixa(s)' : 'unidade(s)' }}
                                </td>
                                <td>{{ $produto->validade->format('d/m/Y') }}</td>
                                <td>R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}</td>
                                <td>R$ {{ number_format($produto->quantidade * $produto->preco_venda, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Nenhum produto cadastrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Movimentacoes do periodo
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Produto</th>
                            <th>Tipo</th>
                            <th>Quantidade</th>
                            <th>Valor unitario venda</th>
                            <th>Total venda</th>
                            <th>Observacao</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($movimentacoes as $movimentacao)
                            <tr>
                                <td>{{ $movimentacao->data_movimentacao->format('d/m/Y') }}</td>
                                <td>{{ $movimentacao->produto?->nome ?? 'Produto removido' }}</td>
                                <td>
                                    <span class="badge {{ $movimentacao->tipo_movimentacao === 'entrada' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($movimentacao->tipo_movimentacao) }}
                                    </span>
                                </td>
                                <td>{{ $movimentacao->quantidade }}</td>
                                <td>
                                    @if ($movimentacao->tipo_movimentacao === 'saida')
                                        R$ {{ number_format((float) $movimentacao->valor_unitario_venda, 2, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if ($movimentacao->tipo_movimentacao === 'saida')
                                        R$ {{ number_format($movimentacao->quantidade * (float) $movimentacao->valor_unitario_venda, 2, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $movimentacao->observacao ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Nenhuma movimentacao registrada no periodo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
