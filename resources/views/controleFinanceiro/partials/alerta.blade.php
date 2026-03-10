@if($saldo > 0)
    <div class="alert alert-success" role="alert">
        Saldo positivo: R$ {{ number_format($saldo, 2, ',', '.') }}
    </div>
@elseif($saldo < 0)
    <div class="alert alert-danger" role="alert">
        Saldo negativo: R$ {{ number_format($saldo, 2, ',', '.') }}
    </div>
@else
    <div class="alert alert-info" role="alert">
        Sem Movimentação Financeira do Período Selecionado
    </div>
@endif