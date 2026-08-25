@extends('layouts.base')

@section('conteudo')
<div class="col-12">
    <h1>
        <i class="h3 bi bi-gear-fill"> Configurações de sistema</i>
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

    <div class="card border-0 shadow-sm mt-3">
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

                    <div class="col-12 mt-4">
                        <h2 class="h5">Dados fiscais do emitente</h2>
                        <p class="text-muted mb-0">Valide estes dados com o contador antes da homologacao fiscal.</p>
                    </div>
                    <div class="col-md-6">
                        <label for="razao_social" class="form-label">Razao social</label>
                        <input type="text" name="razao_social" id="razao_social" class="form-control"
                            value="{{ old('razao_social', $configuracao->razao_social ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="cnpj" class="form-label">CNPJ</label>
                        <input type="text" name="cnpj" id="cnpj" class="form-control" maxlength="14"
                            value="{{ old('cnpj', $configuracao->cnpj ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="inscricao_estadual" class="form-label">Inscricao estadual</label>
                        <input type="text" name="inscricao_estadual" id="inscricao_estadual" class="form-control"
                            value="{{ old('inscricao_estadual', $configuracao->inscricao_estadual ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="regime_tributario" class="form-label">Regime tributario (CRT)</label>
                        <select name="regime_tributario" id="regime_tributario" class="form-select">
                            <option value="">Selecione</option>
                            <option value="1" {{ old('regime_tributario', $configuracao->regime_tributario ?? '') == '1' ? 'selected' : '' }}>1 - Simples Nacional</option>
                            <option value="2" {{ old('regime_tributario', $configuracao->regime_tributario ?? '') == '2' ? 'selected' : '' }}>2 - Simples, excesso sublimite</option>
                            <option value="3" {{ old('regime_tributario', $configuracao->regime_tributario ?? '') == '3' ? 'selected' : '' }}>3 - Regime normal</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="cnae" class="form-label">CNAE</label>
                        <input type="text" name="cnae" id="cnae" class="form-control" maxlength="7"
                            value="{{ old('cnae', $configuracao->cnae ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="codigo_municipio" class="form-label">Codigo IBGE municipio</label>
                        <input type="text" name="codigo_municipio" id="codigo_municipio" class="form-control" maxlength="7"
                            value="{{ old('codigo_municipio', $configuracao->codigo_municipio ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="municipio" class="form-label">Municipio</label>
                        <input type="text" name="municipio" id="municipio" class="form-control"
                            value="{{ old('municipio', $configuracao->municipio ?? '') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="uf" class="form-label">UF</label>
                        <input type="text" name="uf" id="uf" class="form-control" maxlength="2"
                            value="{{ old('uf', $configuracao->uf ?? '') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="cep" class="form-label">CEP</label>
                        <input type="text" name="cep" id="cep" class="form-control" maxlength="8"
                            value="{{ old('cep', $configuracao->cep ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="ambiente_fiscal" class="form-label">Ambiente fiscal</label>
                        <select name="ambiente_fiscal" id="ambiente_fiscal" class="form-select" required>
                            <option value="homologacao" {{ old('ambiente_fiscal', $configuracao->ambiente_fiscal ?? 'homologacao') === 'homologacao' ? 'selected' : '' }}>Homologacao</option>
                            <option value="producao" {{ old('ambiente_fiscal', $configuracao->ambiente_fiscal ?? '') === 'producao' ? 'selected' : '' }}>Producao</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="serie_nfe" class="form-label">Serie NF-e</label>
                        <input type="number" name="serie_nfe" id="serie_nfe" class="form-control" min="1" max="999"
                            value="{{ old('serie_nfe', $configuracao->serie_nfe ?? 1) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label for="proximo_numero_nfe" class="form-label">Proxima NF-e</label>
                        <input type="number" name="proximo_numero_nfe" id="proximo_numero_nfe" class="form-control" min="1"
                            value="{{ old('proximo_numero_nfe', $configuracao->proximo_numero_nfe ?? 1) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label for="serie_nfce" class="form-label">Serie NFC-e</label>
                        <input type="number" name="serie_nfce" id="serie_nfce" class="form-control" min="1" max="999"
                            value="{{ old('serie_nfce', $configuracao->serie_nfce ?? 1) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label for="proximo_numero_nfce" class="form-label">Proxima NFC-e</label>
                        <input type="number" name="proximo_numero_nfce" id="proximo_numero_nfce" class="form-control" min="1"
                            value="{{ old('proximo_numero_nfce', $configuracao->proximo_numero_nfce ?? 1) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label for="csc_id" class="form-label">Identificador CSC</label>
                        <input type="text" name="csc_id" id="csc_id" class="form-control"
                            value="{{ old('csc_id', $configuracao->csc_id ?? '') }}">
                    </div>
                    <div class="col-md-5">
                        <label for="csc_token" class="form-label">Token CSC</label>
                        <input type="password" name="csc_token" id="csc_token" class="form-control"
                            placeholder="Deixe vazio para manter o token atual">
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
