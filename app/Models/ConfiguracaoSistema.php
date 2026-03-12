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
    ];
}
