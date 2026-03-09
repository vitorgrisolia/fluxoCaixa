@extends('layouts.base')

@section('conteudo')
<div class="col-md-12">
    <h1>
        <i class="bi bi-box-seam"></i>
        {{ $produto ? 'Editar produto' : 'Novo produto' }}
    </h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Erro ao salvar:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($produto)
        @php
            $diasParaVencimento = \Illuminate\Support\Carbon::today()->diffInDays($produto->validade, false);
        @endphp

        @if($diasParaVencimento < 0)
            <div class="alert alert-danger">
                Alerta: este produto esta vencido ha {{ abs($diasParaVencimento) }} dia(s).
            </div>
        @elseif($diasParaVencimento === 0)
            <div class="alert alert-danger">
                Alerta: este produto vence hoje.
            </div>
        @elseif($diasParaVencimento <= 30)
            <div class="alert alert-warning">
                Alerta: este produto vence em {{ $diasParaVencimento }} dia(s).
            </div>
        @endif
    @endif

    @if($produto)
        <form action="{{ route('produto.update', ['id' => $produto->id_produto]) }}" method="post">
    @else
        <form action="{{ route('produto.store') }}" method="post">
    @endif
        @csrf

        <div class="row g-3">
            @if($produto)
                <div class="col-md-2">
                    <label for="id_produto" class="form-label">ID:</label>
                    <input type="text" id="id_produto" class="form-control" value="{{ $produto->id_produto }}" disabled>
                </div>
            @endif

            <div class="col-md-4">
                <label for="nome" class="form-label">Nome:</label>
                <input
                    type="text"
                    name="nome"
                    id="nome"
                    class="form-control"
                    value="{{ old('nome', $produto ? $produto->nome : '') }}"
                    required
                >
            </div>

            <div class="col-md-3">
                <label for="lote" class="form-label">Lote:</label>
                <input
                    type="text"
                    name="lote"
                    id="lote"
                    class="form-control"
                    value="{{ old('lote', $produto ? $produto->lote : '') }}"
                    required
                >
            </div>

            <div class="col-md-2">
                <label for="quantidade" class="form-label">Quantidade:</label>
                <input
                    type="number"
                    name="quantidade"
                    id="quantidade"
                    class="form-control"
                    min="0"
                    step="1"
                    value="{{ old('quantidade', $produto ? $produto->quantidade : 0) }}"
                    required
                >
            </div>

            <div class="col-md-2">
                <label for="tipo_quantidade" class="form-label">Tipo quantidade:</label>
                <select name="tipo_quantidade" id="tipo_quantidade" class="form-select" required>
                    <option value="">Selecione:</option>
                    <option value="caixa" {{ old('tipo_quantidade', $produto ? $produto->tipo_quantidade : '') === 'caixa' ? 'selected' : '' }}>
                        Caixa
                    </option>
                    <option value="unidade" {{ old('tipo_quantidade', $produto ? $produto->tipo_quantidade : '') === 'unidade' ? 'selected' : '' }}>
                        Unidade
                    </option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="validade" class="form-label">Validade do produto:</label>
                <input
                    type="date"
                    name="validade"
                    id="validade"
                    class="form-control"
                    value="{{ old('validade', $produto ? $produto->validade->format('Y-m-d') : '') }}"
                    required
                >
            </div>

            <div class="col-md-3">
                <label for="preco_compra" class="form-label">Preço de compra:</label>
                <input
                    type="number"
                    name="preco_compra"
                    id="preco_compra"
                    class="form-control"
                    min="0"
                    step="0.01"
                    value="{{ old('preco_compra', $produto ? $produto->preco_compra : 0) }}"
                    required
                >
            </div>

            <div class="col-md-3">
                <label for="preco_venda" class="form-label">Preço de venda:</label>
                <input
                    type="number"
                    name="preco_venda"
                    id="preco_venda"
                    class="form-control"
                    min="0"
                    step="0.01"
                    value="{{ old('preco_venda', $produto ? $produto->preco_venda : 0) }}"
                    required
                >
            </div>
        </div>

        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-dark">
                {{ $produto ? 'Atualizar produto' : 'Cadastrar produto' }}
            </button>
            <a href="{{ route('produto.index') }}" class="btn btn-outline-secondary">
                Voltar
            </a>
        </div>
    </form>
</div>
@endsection
