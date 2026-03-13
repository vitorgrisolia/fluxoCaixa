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
        'rota',
        'metodo',
        'url',
        'ip',
        'user_agent',
        'dados',
    ];

    protected $casts = [
        'dados' => 'array',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
