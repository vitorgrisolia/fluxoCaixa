@extends('layouts.base')
@section('conteudo')
<div class="col-12"><a href="{{ route('documentos-fiscais.index') }}" class="btn btn-outline-secondary mb-3">Voltar</a>
<div class="card"><div class="card-header d-flex justify-content-between"><strong>Documento {{ $documento->serie }}/{{ $documento->numero }}</strong><span class="badge bg-warning text-dark">{{ str_replace('_',' ',$documento->status) }}</span></div><div class="card-body"><div class="row g-3">
<div class="col-md-3"><strong>Modelo</strong><div>{{ $documento->modelo }}</div></div><div class="col-md-3"><strong>Ambiente</strong><div>{{ ucfirst($documento->ambiente) }}</div></div><div class="col-md-3"><strong>Venda</strong><div>#{{ $documento->id_compra }}</div></div><div class="col-md-3"><strong>Cliente</strong><div>{{ optional($documento->compra->cliente)->nome ?? 'Nao identificado' }}</div></div>
<div class="col-12"><strong>Retorno fiscal</strong><div>{{ $documento->motivo_status ?: 'Sem retorno.' }}</div></div><div class="col-12"><strong>Chave de acesso</strong><div class="font-monospace">{{ $documento->chave_acesso ?: 'Ainda nao gerada/autorizada' }}</div></div>
</div></div></div>
<div class="card mt-3"><div class="card-header">Eventos</div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Data</th><th>Tipo</th><th>Status</th><th>Motivo</th></tr></thead><tbody>@forelse($documento->eventos as $evento)<tr><td>{{ $evento->created_at->format('d/m/Y H:i:s') }}</td><td>{{ $evento->tipo }}</td><td>{{ $evento->status }}</td><td>{{ $evento->motivo }}</td></tr>@empty<tr><td colspan="4" class="text-muted text-center">Sem eventos.</td></tr>@endforelse</tbody></table></div></div>
</div>
@endsection
