<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#Models
use App\Models\UserPermissao;
use App\Models\Lancamento;
use App\Support\Permissoes\PermissionMatrix;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    
    protected $primaryKey = 'id_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
        'email',
        'password',
        'tipo_usuario',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    
    /*
     *   Relacionamentos
     *   https://laravel.com/docs/9.x/eloquent-relationships#main-content
    */

    public function lancamentos()
    {
        return $this->belongsTo(Lancamento::class, 'id_user', 'id_user')
                    ->orderBy('dt_faturamento');
    }

    public function turnos()
    {
        return $this->hasMany(CaixaTurno::class, 'id_user', 'id_user');
    }

    public function permissoes()
    {
        return $this->hasMany(UserPermissao::class, 'id_user', 'id_user');
    }

    public function possuiPermissao(string $chavePermissao): bool
    {
        $chavePermissao = trim($chavePermissao);
        if ($chavePermissao === '') {
            return false;
        }

        if ($this->currentAccessToken()) {
            $tokenPermite = $this->tokenCan('*') || $this->tokenCan($chavePermissao);
            if (! $tokenPermite) {
                return false;
            }
        }

        try {
            $permissoesUsuario = $this->relationLoaded('permissoes')
                ? $this->permissoes
                : $this->permissoes()->get();
        } catch (\Throwable $exception) {
            $permissoesUsuario = collect();
        }

        $overrides = $permissoesUsuario
            ->keyBy(function (UserPermissao $permissao) {
                return $permissao->chave_permissao;
            });

        if ($overrides->has($chavePermissao)) {
            return (bool) $overrides->get($chavePermissao)->permitido;
        }

        foreach ($overrides as $chave => $override) {
            if (! $override->permitido) {
                continue;
            }

            if (PermissionMatrix::allows($chavePermissao, [$chave])) {
                return true;
            }
        }

        $defaults = PermissionMatrix::roleDefaults((string) $this->tipo_usuario);

        return PermissionMatrix::allows($chavePermissao, $defaults);
    }
}
