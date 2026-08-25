@extends('layouts.base')

@section('conteudo')
<div class="col-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h1 class="h3 mb-1">Clientes</h1><p class="text-muted mb-0">Destinatarios para vendas e documentos fiscais.</p></div>
        <a href="{{ route('cliente.create') }}" class="btn btn-primary">Novo cliente</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead><tr><th>Nome</th><th>CPF/CNPJ</th><th>Municipio</th><th>Contato</th><th>Acoes</th></tr></thead>
        <tbody>
        @forelse($clientes as $cliente)
            <tr>
                <td class="fw-semibold">{{ $cliente->nome }}</td><td>{{ $cliente->cpf_cnpj ?: '-' }}</td>
                <td>{{ $cliente->municipio ? $cliente->municipio.'/'.$cliente->uf : '-' }}</td><td>{{ $cliente->email ?: $cliente->telefone ?: '-' }}</td>
                <td class="d-flex gap-2"><a class="btn btn-sm btn-outline-primary" href="{{ route('cliente.edit', $cliente->id_cliente) }}">Editar</a>
                <form method="post" action="{{ route('cliente.destroy', $cliente->id_cliente) }}" onsubmit="return confirm('Desativar este cliente?')">@csrf<button class="btn btn-sm btn-outline-danger">Desativar</button></form></td>
            </tr>
        @empty<tr><td colspan="5" class="text-center text-muted py-4">Nenhum cliente cadastrado.</td></tr>@endforelse
        </tbody>
    </table></div></div>
    <div class="mt-3">{{ $clientes->links() }}</div>
</div>
@endsection
