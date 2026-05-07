<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPermissao extends Model
{
    use HasFactory;

    protected $table = 'user_permissoes';
    protected $primaryKey = 'id_permissao';

    protected $fillable = [
        'id_user',
        'chave_permissao',
        'permitido',
    ];

    protected $casts = [
        'permitido' => 'boolean',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
