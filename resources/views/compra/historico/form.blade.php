@extends('layouts.base')

@section('conteudo')
<div class="col-12">
    <h1>
        @if($compra)
            Atualizar compra
        @else
            Nova compra
        @endif
    </h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Confira os campos:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($compra)
        <form action="{{ route('leitor.historico.update', ['id' => $compra->id_compra]) }}" method="post">
    @else
        <form action="{{ route('leitor.historico.store') }}" method="post">
    @endif
        @csrf

        <div class="row">
            <div class="form-group col-md-4">
                <label for="data_compra" class="form-label">Data da compra*</label>
                <input
                    type="datetime-local"
                    name="data_compra"
                    id="data_compra"
                    class="form-control"
                    value="{{ old('data_compra', $compra ? $compra->data_compra->format('Y-m-d\\TH:i') : now()->format('Y-m-d\\TH:i')) }}"
                    required
                >
            </div>
            <div class="form-group col-md-4">
                <label for="valor_total" class="form-label">Valor total*</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="valor_total"
                    id="valor_total"
                    class="form-control"
                    value="{{ old('valor_total', $compra ? $compra->valor_total : 0) }}"
                    required
                >
            </div>
            <div class="form-group col-md-4">
                <label for="forma_pagamento" class="form-label">Forma de pagamento*</label>
                <select name="forma_pagamento" id="forma_pagamento" class="form-select" required>
                    <option value="">Selecione</option>
                    <option value="pix" {{ old('forma_pagamento', $compra->forma_pagamento ?? '') === 'pix' ? 'selected' : '' }}>PIX</option>
                    <option value="dinheiro" {{ old('forma_pagamento', $compra->forma_pagamento ?? '') === 'dinheiro' ? 'selected' : '' }}>Dinheiro</option>
                    <option value="cartao_debito" {{ old('forma_pagamento', $compra->forma_pagamento ?? '') === 'cartao_debito' ? 'selected' : '' }}>Cartao de debito</option>
                    <option value="cartao_credito" {{ old('forma_pagamento', $compra->forma_pagamento ?? '') === 'cartao_credito' ? 'selected' : '' }}>Cartao de credito</option>
                    <option value="boleto" {{ old('forma_pagamento', $compra->forma_pagamento ?? '') === 'boleto' ? 'selected' : '' }}>Boleto</option>
                    <option value="vale_alimentacao" {{ old('forma_pagamento', $compra->forma_pagamento ?? '') === 'vale_alimentacao' ? 'selected' : '' }}>Vale alimentacao</option>
                </select>
            </div>
        </div>

        <div class="row mt-3">
            <div class="form-group col-md-3">
                <label for="dividir_valor" class="form-label">Quer dividir valor?</label>
                <select name="dividir_valor" id="dividir_valor" class="form-select" required>
                    <option value="nao" {{ old('dividir_valor', $compra->dividir_valor ?? 'nao') === 'nao' ? 'selected' : '' }}>Nao</option>
                    <option value="sim" {{ old('dividir_valor', $compra->dividir_valor ?? '') === 'sim' ? 'selected' : '' }}>Sim</option>
                </select>
            </div>
            <div class="form-group col-md-3" id="parcelas-container" style="display: none;">
                <label for="parcelas" class="form-label">Parcelas (1x a 12x)</label>
                <input
                    type="number"
                    name="parcelas"
                    id="parcelas"
                    class="form-control"
                    min="1"
                    max="12"
                    value="{{ old('parcelas', $compra->parcelas ?? 1) }}"
                >
            </div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <input type="submit" value="{{ $compra ? 'Atualizar' : 'Cadastrar' }}" class="btn btn-primary">
            <a href="{{ route('leitor.historico.index') }}" class="btn btn-outline-secondary">
                Voltar
            </a>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
    (function () {
        const dividirValor = document.getElementById('dividir_valor');
        const parcelasContainer = document.getElementById('parcelas-container');
        const parcelasInput = document.getElementById('parcelas');

        function alternarParcelas() {
            const mostrarParcelas = dividirValor.value === 'sim';
            parcelasContainer.style.display = mostrarParcelas ? 'block' : 'none';
            parcelasInput.required = mostrarParcelas;

            if (!mostrarParcelas) {
                parcelasInput.value = 1;
            }
        }

        dividirValor.addEventListener('change', alternarParcelas);
        alternarParcelas();
    })();
</script>
@endsection
