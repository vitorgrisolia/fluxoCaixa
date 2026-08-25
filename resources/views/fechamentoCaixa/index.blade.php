@extends('layouts.base')

@section('conteudo')
<div class="col-12">
    <h1>
        <i class="h3 bi bi-cash-stack">Fechamento de Caixa</i>
        
    </h1>

    @php
        $isAdmin = Auth::user()->tipo_usuario === 'admin';
    @endphp

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

    @if (! $isAdmin)
        <a href="{{ route('fechamento-caixa.create') }}" class="btn btn-dark mb-3 mt-3">
            Novo
        </a>
    @endif

    <div class="table-responsive">
        <table class="table table-striped table-border table-hover">
            <thead>
                <tr>
                    <th>Acoes</th>
                    <th>Status</th>
                    <th>Data</th>
                    @if ($isAdmin)
                        <th>Funcionario</th>
                    @endif
                    <th>Fundo de caixa</th>
                    <th>Dinheiro</th>
                    <th>Cartao</th>
                    <th>PIX</th>
                    <th>Outros</th>
                    <th>Saldo final</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($fechamentos as $fechamento)
                    <tr>
                        <td class="d-flex flex-wrap gap-2">
                            <a href="{{ route('fechamento-caixa.show', ['id' => $fechamento->id_fechamento]) }}" class="btn btn-dark btn-sm">
                                Ver
                            </a>
                            @if($isAdmin && $fechamento->status === 'fechado')
                                <button class="btn btn-warning btn-sm" data-bs-toggle="collapse" data-bs-target="#reabrir-{{ $fechamento->id_fechamento }}">Reabrir</button>
                            @endif
                        </td>
                        <td><span class="badge {{ $fechamento->status === 'fechado' ? 'bg-success' : 'bg-warning text-dark' }}">{{ ucfirst($fechamento->status) }}</span></td>
                        <td>{{ \Carbon\Carbon::parse($fechamento->data_fechamento)->format('d/m/Y') }}</td>
                        @if ($isAdmin)
                            <td>{{ optional($fechamento->usuario)->nome ?? '---' }}</td>
                        @endif
                        <td>R$ {{ number_format($fechamento->saldo_inicial, 2, ',', '.') }}</td>
                        <td>R$ {{ number_format($fechamento->valor_dinheiro, 2, ',', '.') }}</td>
                        <td>R$ {{ number_format($fechamento->valor_cartao, 2, ',', '.') }}</td>
                        <td>R$ {{ number_format($fechamento->valor_pix, 2, ',', '.') }}</td>
                        <td>R$ {{ number_format($fechamento->valor_outros, 2, ',', '.') }}</td>
                        <td>R$ {{ number_format($fechamento->saldo_final, 2, ',', '.') }}</td>
                    </tr>
                    @if($isAdmin && $fechamento->status === 'fechado')
                        <tr class="collapse" id="reabrir-{{ $fechamento->id_fechamento }}"><td colspan="10">
                            <form class="d-flex gap-2" method="post" action="{{ route('fechamento-caixa.reabrir', $fechamento->id_fechamento) }}">@csrf
                                <input class="form-control" name="motivo_reabertura" minlength="10" maxlength="500" placeholder="Informe o motivo da reabertura" required>
                                <button class="btn btn-warning">Confirmar</button>
                            </form>
                        </td></tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 10 : 9 }}" class="text-center text-muted">
                            Nenhum fechamento de caixa registrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
