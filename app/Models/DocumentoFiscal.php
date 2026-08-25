<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoFiscal extends Model
{
    use HasFactory;

    protected $table = 'documentos_fiscais';
    protected $primaryKey = 'id_documento_fiscal';

    protected $fillable = [
        'id_compra', 'idempotencia', 'solicitado_por', 'modelo', 'serie', 'numero', 'ambiente', 'status', 'chave_acesso',
        'protocolo', 'codigo_status', 'motivo_status', 'xml_envio', 'xml_autorizado',
        'autorizado_em', 'cancelado_em', 'solicitado_em', 'tentativas', 'proxima_tentativa_em',
    ];

    protected $casts = [
        'autorizado_em' => 'datetime',
        'cancelado_em' => 'datetime',
        'solicitado_em' => 'datetime',
        'proxima_tentativa_em' => 'datetime',
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'id_compra', 'id_compra');
    }

    public function eventos()
    {
        return $this->hasMany(DocumentoFiscalEvento::class, 'id_documento_fiscal', 'id_documento_fiscal')->latest();
    }
}
