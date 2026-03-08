@extends('layouts.base')

@section('conteudo')
<div class="col-md-10">
    <h1>
        <i class="bi bi-cart-check-fill"></i>
        Finalizar compra
    </h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Erro ao finalizar:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('leitor.finalizar.store') }}" method="post">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Valor total da compra</label>
                    <input
                        id="total_compra"
                        data-total="{{ number_format($totalCompra, 2, '.', '') }}"
                        type="text"
                        class="form-control"
                        value="R$ {{ number_format($totalCompra, 2, ',', '.') }}"
                        readonly
                    >
                </div>

                <div class="mb-3">
                    <label for="forma_pagamento" class="form-label">Forma de pagamento</label>
                    <select name="forma_pagamento" id="forma_pagamento" class="form-select" required>
                        <option value="">Selecione</option>
                        <option value="pix" {{ old('forma_pagamento') === 'pix' ? 'selected' : '' }}>PIX</option>
                        <option value="dinheiro" {{ old('forma_pagamento') === 'dinheiro' ? 'selected' : '' }}>Dinheiro</option>
                        <option value="cartao_debito" {{ old('forma_pagamento') === 'cartao_debito' ? 'selected' : '' }}>Cartao de debito</option>
                        <option value="cartao_credito" {{ old('forma_pagamento') === 'cartao_credito' ? 'selected' : '' }}>Cartao de credito</option>
                        <option value="boleto" {{ old('forma_pagamento') === 'boleto' ? 'selected' : '' }}>Boleto</option>
                        <option value="vale_alimentacao" {{ old('forma_pagamento') === 'vale_alimentacao' ? 'selected' : '' }}>Vale alimentacao</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="dividir_valor" class="form-label">Quer dividir valor?</label>
                    <select name="dividir_valor" id="dividir_valor" class="form-select" required>
                        <option value="nao" {{ old('dividir_valor', 'nao') === 'nao' ? 'selected' : '' }}>Nao</option>
                        <option value="sim" {{ old('dividir_valor') === 'sim' ? 'selected' : '' }}>Sim</option>
                    </select>
                </div>

                <div class="mb-3" id="parcelas-container" style="display: none;">
                    <label for="parcelas" class="form-label">Parcelas (1x a 12x)</label>
                    <input
                        type="number"
                        name="parcelas"
                        id="parcelas"
                        class="form-control"
                        min="1"
                        max="12"
                        value="{{ old('parcelas', 1) }}"
                    >
                </div>

                <div class="mb-3" id="valor-parcela-container" style="display: none;">
                    <label class="form-label">Valor por parcela</label>
                    <input type="text" id="valor_parcela" class="form-control" readonly>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        Confirmar compra
                    </button>
                    <a href="{{ route('leitor.produtos') }}" class="btn btn-outline-secondary">
                        Voltar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    (function () {
        const formaPagamento = document.getElementById('forma_pagamento');
        const dividirValor = document.getElementById('dividir_valor');
        const parcelasContainer = document.getElementById('parcelas-container');
        const parcelasInput = document.getElementById('parcelas');
        const valorParcelaContainer = document.getElementById('valor-parcela-container');
        const valorParcelaInput = document.getElementById('valor_parcela');
        const totalCompraInput = document.getElementById('total_compra');
        const totalCompra = parseFloat(totalCompraInput.dataset.total || '0');

        function formatarBRL(valor) {
            return 'R$ ' + valor.toFixed(2).replace('.', ',');
        }

        function atualizarValorParcela() {
            const dividirSelecionado = dividirValor.value === 'sim';
            const mostrarParcelas = dividirSelecionado;

            if (mostrarParcelas) {
                const parcelas = parseInt(parcelasInput.value || '0', 10);
                if (parcelas >= 1) {
                    const valorParcela = totalCompra / parcelas;
                    valorParcelaInput.value = formatarBRL(valorParcela);
                    valorParcelaContainer.style.display = 'block';
                    return;
                }
            }

            valorParcelaContainer.style.display = 'none';
            valorParcelaInput.value = '';
        }

        function alternarParcelas() {
            const dividirSelecionado = dividirValor.value === 'sim';
            const mostrarParcelas = dividirSelecionado;

            parcelasContainer.style.display = mostrarParcelas ? 'block' : 'none';
            parcelasInput.required = mostrarParcelas;

            if (!mostrarParcelas) {
                parcelasInput.value = 1;
            }

            atualizarValorParcela();
        }

        formaPagamento.addEventListener('change', alternarParcelas);
        dividirValor.addEventListener('change', alternarParcelas);
        parcelasInput.addEventListener('input', atualizarValorParcela);

        alternarParcelas();
    })();
</script>
@endsection
