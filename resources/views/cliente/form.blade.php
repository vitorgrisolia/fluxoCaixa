@extends('layouts.base')

@section('conteudo')
<div class="col-12 col-xl-10 mx-auto">
    <h1 class="h3">{{ $cliente->exists ? 'Editar cliente' : 'Novo cliente' }}</h1>
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="card" method="post" action="{{ $cliente->exists ? route('cliente.update', $cliente->id_cliente) : route('cliente.store') }}">
        @csrf
        <div class="card-body"><div class="row g-3">
            <div class="col-md-8"><label class="form-label">Nome / razao social</label><input class="form-control" name="nome" value="{{ old('nome', $cliente->nome) }}" required></div>
            <div class="col-md-4"><label class="form-label">CPF/CNPJ</label><input class="form-control" name="cpf_cnpj" value="{{ old('cpf_cnpj', $cliente->cpf_cnpj) }}" maxlength="14"></div>
            <div class="col-md-4"><label class="form-label">Inscricao estadual</label><input class="form-control" name="inscricao_estadual" value="{{ old('inscricao_estadual', $cliente->inscricao_estadual) }}"></div>
            <div class="col-md-4"><label class="form-label">Indicador IE</label><select class="form-select" name="indicador_ie"><option value="">Selecione</option>@foreach(['contribuinte'=>'Contribuinte','isento'=>'Isento','nao_contribuinte'=>'Nao contribuinte'] as $value=>$label)<option value="{{ $value }}" {{ old('indicador_ie', $cliente->indicador_ie)===$value?'selected':'' }}>{{ $label }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">Telefone</label><input class="form-control" name="telefone" value="{{ old('telefone', $cliente->telefone) }}"></div>
            <div class="col-md-6"><label class="form-label">E-mail</label><input type="email" class="form-control" name="email" value="{{ old('email', $cliente->email) }}"></div>
            <div class="col-md-6"><label class="form-label">Logradouro</label><input class="form-control" name="logradouro" value="{{ old('logradouro', $cliente->logradouro) }}"></div>
            <div class="col-md-2"><label class="form-label">Numero</label><input class="form-control" name="numero" value="{{ old('numero', $cliente->numero) }}"></div>
            <div class="col-md-4"><label class="form-label">Complemento</label><input class="form-control" name="complemento" value="{{ old('complemento', $cliente->complemento) }}"></div>
            <div class="col-md-4"><label class="form-label">Bairro</label><input class="form-control" name="bairro" value="{{ old('bairro', $cliente->bairro) }}"></div>
            <div class="col-md-4"><label class="form-label">Municipio</label><input class="form-control" name="municipio" value="{{ old('municipio', $cliente->municipio) }}"></div>
            <div class="col-md-2"><label class="form-label">UF</label><input class="form-control text-uppercase" name="uf" value="{{ old('uf', $cliente->uf) }}" maxlength="2"></div>
            <div class="col-md-3"><label class="form-label">Codigo IBGE</label><input class="form-control" name="codigo_municipio" value="{{ old('codigo_municipio', $cliente->codigo_municipio) }}" maxlength="7"></div>
            <div class="col-md-3"><label class="form-label">CEP</label><input class="form-control" name="cep" value="{{ old('cep', $cliente->cep) }}" maxlength="8"></div>
        </div></div>
        <div class="card-footer d-flex gap-2"><button class="btn btn-primary">Salvar</button><a class="btn btn-outline-secondary" href="{{ route('cliente.index') }}">Cancelar</a></div>
    </form>
</div>
@endsection
