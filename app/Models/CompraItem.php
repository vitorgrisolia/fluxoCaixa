<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompraItem extends Model
{
    use HasFactory;

    protected $table = 'compra_itens';
    protected $primaryKey = 'id_compra_item';

    protected $fillable = [
        'id_compra', 'id_produto', 'produto_nome', 'produto_codigo', 'lote', 'quantidade',
        'unidade', 'valor_unitario', 'desconto', 'valor_total', 'ncm', 'cest', 'cfop',
        'cst_csosn', 'origem_mercadoria', 'gtin',
    ];

    protected $casts = [
        'quantidade' => 'decimal:3',
        'valor_unitario' => 'decimal:2',
        'desconto' => 'decimal:2',
        'valor_total' => 'decimal:2',
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'id_compra', 'id_compra');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'id_produto', 'id_produto')->withTrashed();
    }
}
