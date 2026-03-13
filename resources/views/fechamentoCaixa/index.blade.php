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
                            <a href="{{ route('fechamento-caixa.edit', ['id' => $fechamento->id_fechamento]) }}" class="btn btn-success btn-sm">
                                Editar
                            </a>
                            <form action="{{ route('fechamento-caixa.destroy', ['id' => $fechamento->id_fechamento]) }}" method="post">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">
                                    Excluir
                                </button>
                            </form>
                        </td>
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
                @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 9 : 8 }}" class="text-center text-muted">
                            Nenhum fechamento de caixa registrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
