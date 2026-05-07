<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    protected $table = 'compras';
    protected $primaryKey = 'id_compra';
    protected $dates = ['data_compra', 'created_at', 'updated_at'];

    protected $fillable = [
        'data_compra',
        'valor_total',
        'forma_pagamento',
        'dividir_valor',
        'parcelas',
        'id_turno',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function turno()
    {
        return $this->belongsTo(CaixaTurno::class, 'id_turno', 'id_turno');
    }

    public function itens()
    {
        return $this->hasMany(VendaItem::class, 'id_compra', 'id_compra');
    }
}
