<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';

    protected $fillable = [
        'nome', 'cpf_cnpj', 'inscricao_estadual', 'indicador_ie', 'email', 'telefone',
        'logradouro', 'numero', 'complemento', 'bairro', 'codigo_municipio', 'municipio',
        'uf', 'cep',
    ];

    public function compras()
    {
        return $this->hasMany(Compra::class, 'id_cliente', 'id_cliente');
    }
}
