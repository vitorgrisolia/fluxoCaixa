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
        'quantidade',
        'tipo_quantidade',
        'validade',
        'preco_compra',
        'preco_venda',
    ];
}
