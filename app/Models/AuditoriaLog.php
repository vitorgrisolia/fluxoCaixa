<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditoriaLog extends Model
{
    use HasFactory;

    protected $table = 'auditoria_logs';
    protected $primaryKey = 'id_log';

    protected $fillable = [
        'id_user',
        'acao',
        'descricao',
        'origem',
        'rota',
        'metodo',
        'url',
        'ip',
        'user_agent',
        'entidade',
        'entidade_id',
        'dados',
        'dados_antes',
        'dados_depois',
    ];

    protected $casts = [
        'dados' => 'array',
        'dados_antes' => 'array',
        'dados_depois' => 'array',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
