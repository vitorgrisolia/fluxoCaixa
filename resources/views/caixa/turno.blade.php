@extends('layouts.base')

@section('conteudo')
<div class="col-12">
    <h1>
        <i class="bi bi-clock-history"></i>
        Turno de Caixa
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
            <strong>Confira os campos:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

@if ($isAdmin)
    <div class="col-12">
        <div class="alert alert-info">
            Turnos abertos no momento: <strong>{{ $turnosAbertos }}</strong>.
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID Turno</th>
                        <th>Funcionario</th>
                        <th>Status</th>
                        <th>Abertura</th>
                        <th>Fechamento</th>
                        <th>Fundo inicial</th>
                        <th>Saldo final</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($turnosRecentes as $turno)
                        <tr>
                            <td>#{{ $turno->id_turno }}</td>
                            <td>{{ optional($turno->usuario)->nome ?? '---' }}</td>
                            <td>
                                @if ($turno->status === 'aberto')
                                    <span class="badge bg-success">Aberto</span>
                                @else
                                    <span class="badge bg-secondary">Fechado</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($turno->data_abertura)->format('d/m/Y H:i') }}</td>
                            <td>
                                {{ $turno->data_fechamento ? \Carbon\Carbon::parse($turno->data_fechamento)->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td>R$ {{ number_format($turno->saldo_inicial, 2, ',', '.') }}</td>
                            <td>
                                {{ $turno->saldo_final !== null ? 'R$ '.number_format($turno->saldo_final, 2, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Nenhum turno registrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="col-12">
        @if (!isset($turnoAberto) || !$turnoAberto)
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Abrir turno</h5>
                    <p class="text-muted">Abra um turno para liberar vendas no leitor de produtos.</p>
                    <form action="{{ route('caixa.turno.abrir') }}" method="post" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-md-4">
                            <label for="saldo_inicial" class="form-label">Fundo de caixa inicial*</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="saldo_inicial"
                                id="saldo_inicial"
                                class="form-control"
                                value="{{ old('saldo_inicial', 0) }}"
                                required
                            >
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-dark">Abrir turno</button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Turno atual #{{ $turnoAberto->id_turno }}</h5>
                    <p class="text-muted mb-3">
                        Aberto em {{ \Carbon\Carbon::parse($turnoAberto->data_abertura)->format('d/m/Y H:i') }}.
                    </p>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <strong>Fundo inicial</strong>
                            <div>R$ {{ number_format($turnoAberto->saldo_inicial, 2, ',', '.') }}</div>
                        </div>
                        <div class="col-md-3">
                            <strong>Vendas no turno</strong>
                            <div>R$ {{ number_format($resumoTurno['vendas_total'], 2, ',', '.') }}</div>
                        </div>
                        <div class="col-md-3">
                            <strong>Sangria</strong>
                            <div>R$ {{ number_format($resumoTurno['total_sangria'], 2, ',', '.') }}</div>
                        </div>
                        <div class="col-md-3">
                            <strong>Suprimento</strong>
                            <div>R$ {{ number_format($resumoTurno['total_suprimento'], 2, ',', '.') }}</div>
                        </div>
                        <div class="col-md-3">
                            <strong>Saldo dinheiro estimado</strong>
                            <div>R$ {{ number_format($resumoTurno['saldo_dinheiro_estimado'], 2, ',', '.') }}</div>
                        </div>
                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <a href="{{ route('leitor.produtos') }}" class="btn btn-outline-dark">Ir para leitor</a>
                        <a href="{{ route('fechamento-caixa.create') }}" class="btn btn-success">Fechar turno (fechamento de caixa)</a>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title">Registrar sangria/suprimento</h5>
                    <form action="{{ route('caixa.turno.movimentar') }}" method="post" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-md-3">
                            <label for="tipo_movimentacao" class="form-label">Tipo*</label>
                            <select name="tipo_movimentacao" id="tipo_movimentacao" class="form-select" required>
                                <option value="">Selecione</option>
                                <option value="sangria" {{ old('tipo_movimentacao') === 'sangria' ? 'selected' : '' }}>Sangria</option>
                                <option value="suprimento" {{ old('tipo_movimentacao') === 'suprimento' ? 'selected' : '' }}>Suprimento</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="valor" class="form-label">Valor*</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0.01"
                                name="valor"
                                id="valor"
                                class="form-control"
                                value="{{ old('valor') }}"
                                required
                            >
                        </div>
                        <div class="col-md-4">
                            <label for="observacao" class="form-label">Observacao</label>
                            <input
                                type="text"
                                name="observacao"
                                id="observacao"
                                class="form-control"
                                value="{{ old('observacao') }}"
                                maxlength="500"
                            >
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-dark">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title">Movimentacoes do turno</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Tipo</th>
                                    <th>Valor</th>
                                    <th>Observacao</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($movimentacoesTurno as $movimentacao)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($movimentacao->data_movimentacao)->format('d/m/Y H:i') }}</td>
                                        <td>
                                            {{ $movimentacao->tipo_movimentacao === 'sangria' ? 'Sangria' : 'Suprimento' }}
                                        </td>
                                        <td>R$ {{ number_format($movimentacao->valor, 2, ',', '.') }}</td>
                                        <td>{{ $movimentacao->observacao ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Nenhuma movimentacao registrada.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">Turnos recentes</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Status</th>
                                <th>Abertura</th>
                                <th>Fechamento</th>
                                <th>Fundo</th>
                                <th>Saldo final</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($turnosRecentes as $turno)
                                <tr>
                                    <td>#{{ $turno->id_turno }}</td>
                                    <td>
                                        @if ($turno->status === 'aberto')
                                            <span class="badge bg-success">Aberto</span>
                                        @else
                                            <span class="badge bg-secondary">Fechado</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($turno->data_abertura)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        {{ $turno->data_fechamento ? \Carbon\Carbon::parse($turno->data_fechamento)->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td>R$ {{ number_format($turno->saldo_inicial, 2, ',', '.') }}</td>
                                    <td>{{ $turno->saldo_final !== null ? 'R$ '.number_format($turno->saldo_final, 2, ',', '.') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Nenhum turno registrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

