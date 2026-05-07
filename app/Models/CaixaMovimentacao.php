<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaixaMovimentacao extends Model
{
    use HasFactory;

    protected $table = 'caixa_movimentacoes';
    protected $primaryKey = 'id_movimentacao_caixa';
    protected $dates = ['data_movimentacao', 'created_at', 'updated_at'];

    protected $fillable = [
        'id_turno',
        'id_user',
        'tipo_movimentacao',
        'valor',
        'observacao',
        'data_movimentacao',
    ];

    public function turno()
    {
        return $this->belongsTo(CaixaTurno::class, 'id_turno', 'id_turno');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}

