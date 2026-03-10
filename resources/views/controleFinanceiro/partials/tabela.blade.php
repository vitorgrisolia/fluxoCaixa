<div class="table-responsive mt-4">
    <table class="table table-striped table-border table-hover align-middle mb-0">
        <thead>
            <tr>
                <th>Data</th>
                <th>Descricao</th>
                <th>Centro de Custo</th>
                <th>Tipo</th>
                <th>Valor (R$)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lancamentos as $l)
                <tr>
                    <td>{{ $l->dt_faturamento ? $l->dt_faturamento->format('d/m/Y') : '-' }}</td>
                    <td>{{ $l->descricao ?? '-' }}</td>
                    <td>{{ $l->centroCusto->centro_custo ?? '-' }}</td>
                    <td>{{ $l->centroCusto->tipo->tipo ?? '-' }}</td>
                    <td>R$ {{ number_format($l->valor, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Nenhum lancamento encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
