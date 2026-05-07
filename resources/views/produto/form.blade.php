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

            <div class="col-md-3">
                <label for="codigo_barras" class="form-label">Codigo de barras (EAN)</label>
                <input
                    type="text"
                    name="codigo_barras"
                    id="codigo_barras"
                    class="form-control"
                    value="{{ old('codigo_barras', $produto ? $produto->codigo_barras : '') }}"
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
                <label for="estoque_minimo" class="form-label">Estoque minimo:</label>
                <input
                    type="number"
                    name="estoque_minimo"
                    id="estoque_minimo"
                    class="form-control"
                    min="0"
                    step="1"
                    value="{{ old('estoque_minimo', $produto ? $produto->estoque_minimo : 0) }}"
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

            <div class="col-12">
                <div class="card border-0 shadow-sm mt-2">
                    <div class="card-body">
                        <h2 class="h6 mb-2">Motor de precificacao</h2>
                        <p class="text-muted small mb-3">
                            Formula aplicada: <strong>Preco sugerido = Custo / (1 - margem - taxa - imposto)</strong>.
                            Use percentuais em %.
                        </p>

                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label for="motor_margem" class="form-label">Margem desejada (%)</label>
                                <input type="number" step="0.01" min="0" max="99.99" id="motor_margem" class="form-control" value="{{ old('motor_margem', 30) }}">
                            </div>
                            <div class="col-md-3">
                                <label for="motor_taxa" class="form-label">Taxa operacional (%)</label>
                                <input type="number" step="0.01" min="0" max="99.99" id="motor_taxa" class="form-control" value="{{ old('motor_taxa', 2.5) }}">
                            </div>
                            <div class="col-md-3">
                                <label for="motor_imposto" class="form-label">Imposto (%)</label>
                                <input type="number" step="0.01" min="0" max="99.99" id="motor_imposto" class="form-control" value="{{ old('motor_imposto', 8) }}">
                            </div>
                            <div class="col-md-3 d-grid d-md-flex gap-2">
                                <button type="button" id="motor_calcular" class="btn btn-outline-dark">Calcular</button>
                                <button type="button" id="motor_aplicar" class="btn btn-dark">Aplicar no preco</button>
                            </div>
                            <div class="col-md-4">
                                <label for="motor_preco_sugerido" class="form-label">Preco sugerido</label>
                                <input type="text" id="motor_preco_sugerido" class="form-control" readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="motor_lucro_estimado" class="form-label">Lucro unitario estimado</label>
                                <input type="text" id="motor_lucro_estimado" class="form-control" readonly>
                            </div>
                            <div class="col-md-4">
                                <label for="motor_status" class="form-label">Status</label>
                                <input type="text" id="motor_status" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                </div>
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

@section('script')
<script>
    (function () {
        const precoCompraInput = document.getElementById('preco_compra');
        const precoVendaInput = document.getElementById('preco_venda');
        const margemInput = document.getElementById('motor_margem');
        const taxaInput = document.getElementById('motor_taxa');
        const impostoInput = document.getElementById('motor_imposto');
        const calcularBtn = document.getElementById('motor_calcular');
        const aplicarBtn = document.getElementById('motor_aplicar');
        const precoSugeridoInput = document.getElementById('motor_preco_sugerido');
        const lucroEstimadoInput = document.getElementById('motor_lucro_estimado');
        const statusInput = document.getElementById('motor_status');

        function formatarBRL(valor) {
            return 'R$ ' + valor.toFixed(2).replace('.', ',');
        }

        function calcularPrecoSugerido() {
            const custo = parseFloat(precoCompraInput.value || '0');
            const margem = parseFloat(margemInput.value || '0') / 100;
            const taxa = parseFloat(taxaInput.value || '0') / 100;
            const imposto = parseFloat(impostoInput.value || '0') / 100;
            const pesoTotal = margem + taxa + imposto;
            const divisor = 1 - pesoTotal;

            if (custo <= 0) {
                statusInput.value = 'Informe um custo de compra maior que zero.';
                precoSugeridoInput.value = '';
                lucroEstimadoInput.value = '';
                return null;
            }

            if (divisor <= 0) {
                statusInput.value = 'Percentuais invalidos. A soma deve ser menor que 100%.';
                precoSugeridoInput.value = '';
                lucroEstimadoInput.value = '';
                return null;
            }

            const precoSugerido = custo / divisor;
            const custoTaxaImposto = precoSugerido * (taxa + imposto);
            const lucroUnitario = precoSugerido - custo - custoTaxaImposto;

            precoSugeridoInput.value = formatarBRL(precoSugerido);
            lucroEstimadoInput.value = formatarBRL(lucroUnitario);
            statusInput.value = 'Preco calculado com sucesso.';

            return precoSugerido;
        }

        calcularBtn.addEventListener('click', calcularPrecoSugerido);

        aplicarBtn.addEventListener('click', function () {
            const precoSugerido = calcularPrecoSugerido();
            if (precoSugerido === null) {
                return;
            }

            precoVendaInput.value = precoSugerido.toFixed(2);
            statusInput.value = 'Preco sugerido aplicado no campo de venda.';
        });

        [precoCompraInput, margemInput, taxaInput, impostoInput].forEach(function (input) {
            input.addEventListener('input', calcularPrecoSugerido);
        });

        calcularPrecoSugerido();
    })();
</script>
@endsection
