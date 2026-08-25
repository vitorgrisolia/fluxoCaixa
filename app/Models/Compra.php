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
        'id_cliente',
        'data_compra',
        'valor_total',
        'forma_pagamento',
        'dividir_valor',
        'parcelas',
        'status',
        'cancelada_em',
        'motivo_cancelamento',
    ];

    protected $casts = [
        'data_compra' => 'datetime',
        'cancelada_em' => 'datetime',
        'valor_total' => 'decimal:2',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function itens()
    {
        return $this->hasMany(CompraItem::class, 'id_compra', 'id_compra');
    }

    public function documentosFiscais()
    {
        return $this->hasMany(DocumentoFiscal::class, 'id_compra', 'id_compra');
    }
}
