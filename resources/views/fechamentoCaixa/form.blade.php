@extends('layouts.base')

@section('conteudo')
<div class="col-12">
    @php
        $isAdmin = Auth::user()->tipo_usuario === 'admin';
        $readonlyTotais = $fechamento ? ! $isAdmin : true;
        $valorDinheiro = old('valor_dinheiro', $fechamento ? $fechamento->valor_dinheiro : ($totaisPagamento['valor_dinheiro'] ?? 0));
        $valorCartao = old('valor_cartao', $fechamento ? $fechamento->valor_cartao : ($totaisPagamento['valor_cartao'] ?? 0));
        $valorPix = old('valor_pix', $fechamento ? $fechamento->valor_pix : ($totaisPagamento['valor_pix'] ?? 0));
        $valorOutros = old('valor_outros', $fechamento ? $fechamento->valor_outros : ($totaisPagamento['valor_outros'] ?? 0));
        $saldoInicial = old('saldo_inicial', $fechamento ? $fechamento->saldo_inicial : 0);
        $totalSaidas = old('total_saidas', $fechamento ? $fechamento->total_saidas : 0);
    @endphp

    <h1>
        @if($fechamento)
            Atualizar Fechamento de Caixa
        @else
            Novo Fechamento de Caixa
        @endif
    </h1>

    @if (! $fechamento && isset($inicio, $fim))
        <div class="alert alert-info">
            Periodo do login: {{ $inicio->format('d/m/Y H:i') }} ate {{ $fim->format('d/m/Y H:i') }}.
        </div>
    @endif

    @if (! $fechamento)
        <p class="text-muted">
            Valores de pagamento sao preenchidos automaticamente com base nas compras finalizadas no periodo do login.
        </p>
    @endif

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

    @if ($fechamento)
        <form action="{{ route('fechamento-caixa.update', ['id' => $fechamento->id_fechamento]) }}" method="post">
    @else
        <form action="{{ route('fechamento-caixa.store') }}" method="post">
    @endif
        @csrf

        <div class="row">
            <div class="form-group col-md-3">
                <label for="data_fechamento" class="form-label">Data do fechamento*</label>
                <input
                    type="date"
                    name="data_fechamento"
                    id="data_fechamento"
                    class="form-control"
                    value="{{ old('data_fechamento', $fechamento ? $fechamento->data_fechamento->format('Y-m-d') : now()->toDateString()) }}"
                    required
                >
            </div>
            <div class="form-group col-md-3">
                <label for="saldo_inicial" class="form-label">Fundo de caixa*</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="saldo_inicial"
                    id="saldo_inicial"
                    class="form-control"
                    value="{{ $saldoInicial }}"
                    required
                >
            </div>
            <div class="form-group col-md-3">
                <label for="valor_dinheiro" class="form-label">Valor pago em dinheiro*</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="valor_dinheiro"
                    id="valor_dinheiro"
                    class="form-control"
                    value="{{ $valorDinheiro }}"
                    required
                    {{ $readonlyTotais ? 'readonly' : '' }}
                >
            </div>
            <div class="form-group col-md-3">
                <label for="valor_cartao" class="form-label">Valor pago em cartao*</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="valor_cartao"
                    id="valor_cartao"
                    class="form-control"
                    value="{{ $valorCartao }}"
                    required
                    {{ $readonlyTotais ? 'readonly' : '' }}
                >
            </div>
        </div>

        <div class="row mt-3">
            <div class="form-group col-md-3">
                <label for="valor_pix" class="form-label">Valor pago em PIX*</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="valor_pix"
                    id="valor_pix"
                    class="form-control"
                    value="{{ $valorPix }}"
                    required
                    {{ $readonlyTotais ? 'readonly' : '' }}
                >
            </div>
            <div class="form-group col-md-3">
                <label for="valor_outros" class="form-label">Valor pago em outros</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="valor_outros"
                    id="valor_outros"
                    class="form-control"
                    value="{{ $valorOutros }}"
                    {{ $readonlyTotais ? 'readonly' : '' }}
                >
            </div>
            <div class="form-group col-md-3">
                <label for="total_saidas" class="form-label">Outras saidas</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="total_saidas"
                    id="total_saidas"
                    class="form-control"
                    value="{{ $totalSaidas }}"
                >
            </div>
            <div class="form-group col-md-3">
                <label for="saldo_final" class="form-label">Saldo final (calculado)</label>
                <input
                    type="text"
                    id="saldo_final"
                    class="form-control"
                    readonly
                >
            </div>
            <div class="form-group col-md-12 mt-2">
                <label for="observacoes" class="form-label">Observacoes</label>
                <textarea
                    name="observacoes"
                    id="observacoes"
                    class="form-control"
                    rows="2"
                >{{ old('observacoes', $fechamento ? $fechamento->observacoes : '') }}</textarea>
            </div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <input type="submit" value="{{ $fechamento ? 'Atualizar' : 'Cadastrar' }}" class="btn btn-primary">
            <a href="{{ route('fechamento-caixa.index') }}" class="btn btn-outline-secondary">
                Voltar
            </a>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
    (function () {
        const saldoInicialInput = document.getElementById('saldo_inicial');
        const valorDinheiroInput = document.getElementById('valor_dinheiro');
        const valorCartaoInput = document.getElementById('valor_cartao');
        const valorPixInput = document.getElementById('valor_pix');
        const valorOutrosInput = document.getElementById('valor_outros');
        const totalSaidasInput = document.getElementById('total_saidas');
        const saldoFinalInput = document.getElementById('saldo_final');

        function formatarBRL(valor) {
            return 'R$ ' + valor.toFixed(2).replace('.', ',');
        }

        function atualizarSaldoFinal() {
            const saldoInicial = parseFloat(saldoInicialInput.value || '0');
            const entradas = parseFloat(valorDinheiroInput.value || '0')
                + parseFloat(valorCartaoInput.value || '0')
                + parseFloat(valorPixInput.value || '0')
                + parseFloat(valorOutrosInput.value || '0');
            const saidas = parseFloat(totalSaidasInput.value || '0');

            const saldoFinal = saldoInicial + entradas - saidas;
            saldoFinalInput.value = formatarBRL(saldoFinal);
        }

        saldoInicialInput.addEventListener('input', atualizarSaldoFinal);
        valorDinheiroInput.addEventListener('input', atualizarSaldoFinal);
        valorCartaoInput.addEventListener('input', atualizarSaldoFinal);
        valorPixInput.addEventListener('input', atualizarSaldoFinal);
        valorOutrosInput.addEventListener('input', atualizarSaldoFinal);
        totalSaidasInput.addEventListener('input', atualizarSaldoFinal);

        atualizarSaldoFinal();
    })();
</script>
@endsection
