<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SequenciaFiscal extends Model
{
    protected $table = 'sequencias_fiscais';
    protected $primaryKey = 'id_sequencia';
    protected $fillable = ['modelo', 'serie', 'ambiente', 'proximo_numero'];
}
