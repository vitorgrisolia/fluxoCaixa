@extends('layouts.base')

@section('conteudo')
<div class="col-md-12">
    <h1>
        <i class="h3 bi bi-people-fill">Cadastro de Usuarios</i>
        
    </h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('danger'))
        <div class="alert alert-danger">
            {{ session('danger') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Erro ao processar:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $emEdicao = !is_null($usuarioEdicao);
    @endphp

    <div class="card mb-4 mt-3">
        <div class="card-header pb-0">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ !$emEdicao ? 'active' : '' }}" href="{{ route('usuario.index') }}">
                        Cadastro
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    @if($emEdicao)
                        <a class="nav-link active" href="#">
                            Edicao
                        </a>
                    @else
                        <span class="nav-link disabled">Edicao</span>
                    @endif
                </li>
            </ul>
        </div>
        <div class="card-body">
            @if($emEdicao)
                <h5 class="card-title">Editar usuario #{{ $usuarioEdicao->id_user }}</h5>
                <form action="{{ route('usuario.update', ['id' => $usuarioEdicao->id_user]) }}" method="post">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nome_edicao" class="form-label">Nome</label>
                            <input
                                type="text"
                                name="nome"
                                id="nome_edicao"
                                class="form-control"
                                value="{{ old('nome', $usuarioEdicao->nome) }}"
                                required
                            >
                        </div>
                        <div class="col-md-6">
                            <label for="email_edicao" class="form-label">E-mail</label>
                            <input
                                type="email"
                                name="email"
                                id="email_edicao"
                                class="form-control"
                                value="{{ old('email', $usuarioEdicao->email) }}"
                                required
                            >
                        </div>
                        <div class="col-md-4">
                            <label for="tipo_usuario_edicao" class="form-label">Tipo de usuario</label>
                            <select name="tipo_usuario" id="tipo_usuario_edicao" class="form-select" required>
                                <option value="">Selecione</option>
                                <option
                                    value="funcionario"
                                    {{ old('tipo_usuario', $usuarioEdicao->tipo_usuario) == 'funcionario' ? 'selected' : '' }}
                                >
                                    Funcionario
                                </option>
                                <option
                                    value="admin"
                                    {{ old('tipo_usuario', $usuarioEdicao->tipo_usuario) == 'admin' ? 'selected' : '' }}
                                >
                                    Admin
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="password_edicao" class="form-label">Nova senha (opcional)</label>
                            <input type="password" name="password" id="password_edicao" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label for="password_confirmation_edicao" class="form-label">Confirmar nova senha</label>
                            <input type="password" name="password_confirmation" id="password_confirmation_edicao" class="form-control">
                        </div>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            Atualizar usuario
                        </button>
                        <a href="{{ route('usuario.index') }}" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            @else
                <h5 class="card-title">Novo usuario</h5>
                <form action="{{ route('usuario.store') }}" method="post">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" name="nome" id="nome" class="form-control" value="{{ old('nome') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="tipo_usuario" class="form-label">Tipo de usuario</label>
                            <select name="tipo_usuario" id="tipo_usuario" class="form-select" required>
                                <option value="">Selecione</option>
                                <option value="funcionario" {{ old('tipo_usuario') == 'funcionario' ? 'selected' : '' }}>
                                    Funcionario
                                </option>
                                <option value="admin" {{ old('tipo_usuario') == 'admin' ? 'selected' : '' }}>
                                    Admin
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="password" class="form-label">Senha</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label for="password_confirmation" class="form-label">Confirmar senha</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-dark">
                            Cadastrar usuario
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Usuarios cadastrados
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Tipo</th>
                        <th>Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($usuarios as $usuario)
                        <tr>
                            <td>{{ $usuario->id_user }}</td>
                            <td>{{ $usuario->nome }}</td>
                            <td>{{ $usuario->email }}</td>
                            <td>
                                <span class="badge {{ $usuario->tipo_usuario === 'admin' ? 'bg-primary' : 'bg-secondary' }}">
                                    {{ $usuario->tipo_usuario }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('usuario.edit', ['id' => $usuario->id_user]) }}" class="btn btn-sm btn-outline-primary">
                                        Editar
                                    </a>
                                    <form
                                        action="{{ route('usuario.delete', ['id' => $usuario->id_user]) }}"
                                        method="post"
                                        onsubmit="return confirm('Deseja realmente excluir este usuario?');"
                                    >
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Excluir
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Nenhum usuario cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
