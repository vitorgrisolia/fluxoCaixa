<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'produtos';
    protected $primaryKey = 'id_produto';
    protected $dates = ['validade', 'created_at', 'updated_at', 'deleted_at'];

    protected $fillable = [
        'nome',
        'lote',
        'codigo_barras',
        'quantidade',
        'tipo_quantidade',
        'validade',
        'preco_compra',
        'preco_venda',
        'ncm',
        'cest',
        'cfop',
        'cst_csosn',
        'origem_mercadoria',
        'unidade_comercial',
    ];

    public function movimentacoes()
    {
        return $this->hasMany(MovimentacaoProduto::class, 'id_produto', 'id_produto');
    }

    public function itensVendidos()
    {
        return $this->hasMany(CompraItem::class, 'id_produto', 'id_produto');
    }
}
