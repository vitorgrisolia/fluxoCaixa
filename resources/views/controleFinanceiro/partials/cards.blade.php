<div class="row g-3 md-4">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <p class="card-text">Preço de Compras:</p>
                <h5 class="card-title">R$ {{number_format($totalCompra, 2, ',', '.') }}</h5>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-body">
                <p class="card-text">Preço de Venda:</p>
                <h5 class="card-title">R$ {{number_format($totalVenda, 2, ',', '.') }}</h5>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-body">
                <p class="card-text">Entradas Lançamento:</p>
                <h5 class="card-title">R$ {{number_format($entrada, 2, ',', '.') }}</h5>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-body">
                <p class="card-text">Saídas Lançamento:</p>
                <h5 class="card-title">R$ {{number_format($saida, 2, ',', '.') }}</h5>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-body">
                <p class="card-text">Saldo do Período:</p>
                <h5 class='{{ $saldo >= 0 ? "text-success" : "text-danger" }}'>R$ {{number_format($saldo, 2, ',', '.') }}</h5>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-body">
                <p class="card-text">Margem Bruta de Venda:</p>
                <h5 class="card-title">R$ {{number_format($margem, 1) }}%</h5>
            </div>
        </div>
    </div>
</div>
