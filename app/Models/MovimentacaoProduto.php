<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimentacaoProduto extends Model
{
    use HasFactory;

    protected $table = 'movimentacao_produtos';
    protected $primaryKey = 'id_movimentacao';

    protected $fillable = [
        'id_produto',
        'tipo_movimentacao',
        'quantidade',
        'valor_unitario_venda',
        'data_movimentacao',
        'observacao',
    ];

    protected $casts = [
        'data_movimentacao' => 'date',
        'valor_unitario_venda' => 'decimal:2',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'id_produto', 'id_produto')->withTrashed();
    }
}
