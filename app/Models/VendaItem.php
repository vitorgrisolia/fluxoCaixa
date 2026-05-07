<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendaItem extends Model
{
    use HasFactory;

    protected $table = 'venda_itens';
    protected $primaryKey = 'id_venda_item';

    protected $fillable = [
        'id_compra',
        'id_produto',
        'nome_produto',
        'lote',
        'codigo_barras',
        'quantidade',
        'valor_unitario_venda',
        'valor_unitario_custo',
        'subtotal',
        'subtotal_custo',
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'id_compra', 'id_compra');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'id_produto', 'id_produto');
    }
}
