<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FechamentoCaixa extends Model
{
    use HasFactory;

    protected $table = 'fechamento_caixas';
    protected $primaryKey = 'id_fechamento';
    protected $dates = ['data_fechamento', 'created_at', 'updated_at'];

    protected $fillable = [
        'data_fechamento',
        'saldo_inicial',
        'valor_dinheiro',
        'valor_cartao',
        'valor_pix',
        'valor_outros',
        'total_entradas',
        'total_saidas',
        'saldo_final',
        'observacoes',
        'status',
        'reaberto_por',
        'reaberto_em',
        'motivo_reabertura',
    ];

    protected $casts = ['reaberto_em' => 'datetime'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
