@extends('layouts.base')

@section('conteudo')
<div class="col-12">
    <h1>
        <i class="h3 bi bi-person-circle">Meu perfil</i>
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

    <div class="row g-3 mt-2">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h5">Dados do usuario</h2>
                    <form action="{{ route('perfil.update') }}" method="post">
                        @csrf
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome*</label>
                            <input
                                type="text"
                                name="nome"
                                id="nome"
                                class="form-control"
                                value="{{ old('nome', $usuario->nome) }}"
                                required
                            >
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail*</label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-control"
                                value="{{ old('email', $usuario->email) }}"
                                required
                            >
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Perfil</label>
                            <input type="text" class="form-control" value="{{ ucfirst($usuario->tipo_usuario) }}" readonly>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            Atualizar dados
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h5">Alterar senha</h2>
                    <form action="{{ route('perfil.password') }}" method="post">
                        @csrf
                        <div class="mb-3">
                            <label for="senha_atual" class="form-label">Senha atual*</label>
                            <input
                                type="password"
                                name="senha_atual"
                                id="senha_atual"
                                class="form-control"
                                required
                            >
                        </div>
                        <div class="mb-3">
                            <label for="nova_senha" class="form-label">Nova senha*</label>
                            <input
                                type="password"
                                name="nova_senha"
                                id="nova_senha"
                                class="form-control"
                                required
                            >
                        </div>
                        <div class="mb-3">
                            <label for="nova_senha_confirmation" class="form-label">Confirmar nova senha*</label>
                            <input
                                type="password"
                                name="nova_senha_confirmation"
                                id="nova_senha_confirmation"
                                class="form-control"
                                required
                            >
                        </div>
                        <button type="submit" class="btn btn-outline-dark">
                            Atualizar senha
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
