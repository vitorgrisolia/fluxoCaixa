@extends('layouts.base')

@section('conteudo')
<div class="col-12">
    <h1>
        <i class="bi bi-gear-fill"></i>
        Configuracoes gerais do sistema
    </h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
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

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('configuracoes.update') }}" method="post">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nome_sistema" class="form-label">Nome do sistema*</label>
                        <input
                            type="text"
                            name="nome_sistema"
                            id="nome_sistema"
                            class="form-control"
                            value="{{ old('nome_sistema', $configuracao->nome_sistema ?? '') }}"
                            required
                        >
                    </div>
                    <div class="col-md-6">
                        <label for="nome_empresa" class="form-label">Nome da empresa</label>
                        <input
                            type="text"
                            name="nome_empresa"
                            id="nome_empresa"
                            class="form-control"
                            value="{{ old('nome_empresa', $configuracao->nome_empresa ?? '') }}"
                        >
                    </div>
                    <div class="col-md-4">
                        <label for="email_contato" class="form-label">Email de contato</label>
                        <input
                            type="email"
                            name="email_contato"
                            id="email_contato"
                            class="form-control"
                            value="{{ old('email_contato', $configuracao->email_contato ?? '') }}"
                        >
                    </div>
                    <div class="col-md-4">
                        <label for="telefone_contato" class="form-label">Telefone de contato</label>
                        <input
                            type="text"
                            name="telefone_contato"
                            id="telefone_contato"
                            class="form-control"
                            value="{{ old('telefone_contato', $configuracao->telefone_contato ?? '') }}"
                        >
                    </div>
                    <div class="col-md-4">
                        <label for="moeda" class="form-label">Moeda*</label>
                        <input
                            type="text"
                            name="moeda"
                            id="moeda"
                            class="form-control"
                            value="{{ old('moeda', $configuracao->moeda ?? 'BRL') }}"
                            required
                        >
                    </div>
                    <div class="col-md-8">
                        <label for="endereco" class="form-label">Endereco</label>
                        <input
                            type="text"
                            name="endereco"
                            id="endereco"
                            class="form-control"
                            value="{{ old('endereco', $configuracao->endereco ?? '') }}"
                        >
                    </div>
                    <div class="col-md-4">
                        <label for="mensagem_rodape" class="form-label">Mensagem no rodape</label>
                        <input
                            type="text"
                            name="mensagem_rodape"
                            id="mensagem_rodape"
                            class="form-control"
                            value="{{ old('mensagem_rodape', $configuracao->mensagem_rodape ?? '') }}"
                        >
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        Salvar configuracoes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
