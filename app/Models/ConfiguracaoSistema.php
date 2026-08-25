<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracaoSistema extends Model
{
    use HasFactory;

    protected $table = 'configuracoes';
    protected $primaryKey = 'id_configuracao';

    protected $fillable = [
        'nome_sistema',
        'nome_empresa',
        'email_contato',
        'telefone_contato',
        'endereco',
        'moeda',
        'mensagem_rodape',
        'razao_social',
        'cnpj',
        'inscricao_estadual',
        'regime_tributario',
        'cnae',
        'codigo_municipio',
        'municipio',
        'uf',
        'cep',
        'ambiente_fiscal',
        'serie_nfe',
        'proximo_numero_nfe',
        'serie_nfce',
        'proximo_numero_nfce',
        'csc_id',
        'csc_token',
    ];

    protected $casts = [
        'csc_token' => 'encrypted',
    ];
}
