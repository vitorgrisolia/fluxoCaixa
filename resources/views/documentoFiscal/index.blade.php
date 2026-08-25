@extends('layouts.base')
@section('conteudo')
<div class="col-12">
    <div class="d-flex justify-content-between align-items-end mb-3"><div><h1 class="h3 mb-1">Documentos fiscais</h1><p class="text-muted mb-0">Controle de solicitacoes, autorizacoes e rejeicoes.</p></div>
    <form class="d-flex gap-2"><select class="form-select" name="status"><option value="">Todos os status</option>@foreach(['aguardando_integracao','pendente','autorizada','rejeitada','cancelada'] as $status)<option value="{{ $status }}" {{ request('status')===$status?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$status)) }}</option>@endforeach</select><button class="btn btn-outline-primary">Filtrar</button></form></div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="alert alert-warning"><strong>Atenção:</strong> documentos aguardando integração não foram enviados nem autorizados pela SEFAZ.</div>
    <div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Numero</th><th>Venda</th><th>Modelo</th><th>Ambiente</th><th>Status</th><th>Chave</th><th></th></tr></thead><tbody>
    @forelse($documentos as $doc)<tr><td>{{ $doc->serie }}/{{ $doc->numero }}</td><td>#{{ $doc->id_compra }} {{ optional($doc->compra->cliente)->nome }}</td><td>{{ $doc->modelo }}</td><td>{{ ucfirst($doc->ambiente) }}</td><td><span class="badge {{ $doc->status==='autorizada'?'bg-success':($doc->status==='rejeitada'?'bg-danger':'bg-warning text-dark') }}">{{ str_replace('_',' ',$doc->status) }}</span></td><td class="small">{{ $doc->chave_acesso ?: '-' }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('documentos-fiscais.show',$doc->id_documento_fiscal) }}">Ver</a></td></tr>
    @empty<tr><td colspan="7" class="text-center text-muted py-4">Nenhum documento fiscal solicitado.</td></tr>@endforelse
    </tbody></table></div></div><div class="mt-3">{{ $documentos->links() }}</div>
</div>
@endsection
