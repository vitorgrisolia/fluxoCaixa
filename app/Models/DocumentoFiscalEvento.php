<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoFiscalEvento extends Model
{
    protected $table = 'documento_fiscal_eventos';
    protected $primaryKey = 'id_evento';
    protected $fillable = ['id_documento_fiscal', 'tipo', 'status', 'protocolo', 'codigo_status', 'motivo', 'xml', 'resposta'];
    protected $casts = ['resposta' => 'array'];

    public function documentoFiscal()
    {
        return $this->belongsTo(DocumentoFiscal::class, 'id_documento_fiscal', 'id_documento_fiscal');
    }
}
