<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaixaTurno extends Model
{
    use HasFactory;

    protected $table = 'caixa_turnos';
    protected $primaryKey = 'id_turno';
    protected $dates = ['data_abertura', 'data_fechamento', 'created_at', 'updated_at'];

    protected $fillable = [
        'id_user',
        'data_abertura',
        'saldo_inicial',
        'status',
        'data_fechamento',
        'saldo_final',
        'observacoes_fechamento',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function compras()
    {
        return $this->hasMany(Compra::class, 'id_turno', 'id_turno');
    }

    public function movimentacoes()
    {
        return $this->hasMany(CaixaMovimentacao::class, 'id_turno', 'id_turno');
    }

    public function fechamento()
    {
        return $this->hasOne(FechamentoCaixa::class, 'id_turno', 'id_turno');
    }
}

