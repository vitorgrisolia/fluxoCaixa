@extends('layouts.base')

@section('conteudo')
<div class="col-md-12">
    <h1>
        <i class="bi bi-people-fill"></i>
        Cadastro de Usuarios
    </h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Erro ao cadastrar:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            Novo usuario
        </div>
        <div class="card-body">
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">Nenhum usuario cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
