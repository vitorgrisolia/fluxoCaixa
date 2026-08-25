@extends('layouts.base')

@section('conteudo')
<div class="col-12">
    <h1>
        <i class="bi bi-receipt"></i>
        Detalhe da compra
    </h1>

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <strong>Data</strong>
                    <div>{{ \Carbon\Carbon::parse($compra->data_compra)->format('d/m/Y H:i') }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Valor total</strong>
                    <div>R$ {{ number_format($compra->valor_total, 2, ',', '.') }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Forma de pagamento</strong>
                    <div>{{ ucfirst(str_replace('_', ' ', $compra->forma_pagamento)) }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Parcelamento</strong>
                    <div>{{ $compra->dividir_valor === 'sim' ? ($compra->parcelas . 'x') : 'Nao' }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Status</strong>
                    <div>{{ ucfirst($compra->status) }}</div>
                </div>
                <div class="col-md-4">
                    <strong>Cliente</strong>
                    <div>{{ optional($compra->cliente)->nome ?? 'Consumidor nao identificado' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">Itens vendidos</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Codigo</th>
                            <th>Quantidade</th>
                            <th>Valor unitario</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($compra->itens as $item)
                            <tr>
                                <td>{{ $item->produto_nome }}</td>
                                <td>{{ $item->produto_codigo ?: '-' }}</td>
                                <td>{{ number_format($item->quantidade, 3, ',', '.') }} {{ $item->unidade }}</td>
                                <td>R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                                <td>R$ {{ number_format($item->valor_total, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Venda antiga sem itens individualizados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <a href="{{ route('leitor.historico.index') }}" class="btn btn-outline-secondary">
            Voltar
        </a>
    </div>

    @if(Auth::user()->tipo_usuario === 'admin' && $compra->status === 'concluida')
        <div class="card border-primary mt-4">
            <div class="card-header fw-bold">Documento fiscal</div>
            <div class="card-body">
                <p class="text-muted">A solicitacao reserva a numeracao. A transmissao somente ocorrera depois da configuracao de um provedor fiscal.</p>
                <form action="{{ route('documentos-fiscais.solicitar', $compra->id_compra) }}" method="post" class="d-flex gap-2">@csrf
                    <select class="form-select" name="modelo" required><option value="65">NFC-e (modelo 65)</option><option value="55">NF-e (modelo 55)</option></select>
                    <button class="btn btn-primary">Solicitar</button>
                </form>
            </div>
        </div>
        <div class="card border-danger mt-4">
            <div class="card-header text-danger fw-bold">Estornar venda</div>
            <div class="card-body">
                <form action="{{ route('compras.estornar', $compra->id_compra) }}" method="post" onsubmit="return confirm('Confirma o estorno desta venda?');">
                    @csrf
                    <label for="motivo" class="form-label">Motivo obrigatório</label>
                    <textarea name="motivo" id="motivo" class="form-control" minlength="10" maxlength="500" required></textarea>
                    <button class="btn btn-danger mt-3" type="submit">Confirmar estorno</button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
