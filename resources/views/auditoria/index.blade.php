@extends('layouts.base')

@section('conteudo')
<div class="col-12">
    <h1>
        <i class="bi bi-shield-check"></i>
        Auditoria e Logs
    </h1>

    <form action="{{ route('auditoria.index') }}" method="get" class="row g-2 align-items-end mb-3">
        <div class="col-md-3">
            <label for="data_inicio" class="form-label">Data inicio</label>
            <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="{{ request('data_inicio', $dataInicio) }}">
        </div>
        <div class="col-md-3">
            <label for="data_fim" class="form-label">Data fim</label>
            <input type="date" name="data_fim" id="data_fim" class="form-control" value="{{ request('data_fim', $dataFim) }}">
        </div>
        <div class="col-md-3">
            <label for="usuario" class="form-label">Usuario</label>
            <select name="usuario" id="usuario" class="form-select">
                <option value="">Todos</option>
                @foreach ($usuarios as $usuario)
                    <option value="{{ $usuario->id_user }}" {{ (string) request('usuario') === (string) $usuario->id_user ? 'selected' : '' }}>
                        {{ $usuario->nome }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="acao" class="form-label">Acao</label>
            <select name="acao" id="acao" class="form-select">
                <option value="">Todas</option>
                @foreach (['LOGIN', 'LOGOUT', 'CRIAR', 'ATUALIZAR', 'EXCLUIR', 'POST'] as $acao)
                    <option value="{{ $acao }}" {{ request('acao') === $acao ? 'selected' : '' }}>
                        {{ $acao }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </div>
        <div class="col-md-12 mt-2">
            <label for="rota" class="form-label">Rota (contendo)</label>
            <input type="text" name="rota" id="rota" class="form-control" value="{{ request('rota') }}">
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-border table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Usuario</th>
                            <th>Acao</th>
                            <th>Descricao</th>
                            <th>Metodo</th>
                            <th>Rota</th>
                            <th>IP</th>
                            <th>Dados</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}</td>
                                <td>{{ optional($log->usuario)->nome ?? '-' }}</td>
                                <td>{{ $log->acao }}</td>
                                <td>{{ $log->descricao ?? '-' }}</td>
                                <td>{{ $log->metodo }}</td>
                                <td>{{ $log->rota ?? $log->url }}</td>
                                <td>{{ $log->ip ?? '-' }}</td>
                                <td class="text-muted small">
                                    @if (!empty($log->dados))
                                        {{ json_encode($log->dados, JSON_UNESCAPED_UNICODE) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    Nenhum registro encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
