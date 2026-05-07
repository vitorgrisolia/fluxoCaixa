<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserPermissao;
use Illuminate\Console\Command;

class UserPermissionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:user
                            {id_user : ID do usuario}
                            {chave : Chave da permissao}
                            {acao=allow : allow, deny ou clear}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Define override de permissao por usuario.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $idUser = (int) $this->argument('id_user');
        $chave = trim((string) $this->argument('chave'));
        $acao = strtolower(trim((string) $this->argument('acao')));

        $usuario = User::find($idUser);
        if (! $usuario) {
            $this->error('Usuario nao encontrado.');

            return self::FAILURE;
        }

        if ($chave === '') {
            $this->error('Chave de permissao invalida.');

            return self::FAILURE;
        }

        if (! in_array($acao, ['allow', 'deny', 'clear'], true)) {
            $this->error('Acao invalida. Use allow, deny ou clear.');

            return self::FAILURE;
        }

        if ($acao === 'clear') {
            UserPermissao::where('id_user', $idUser)->where('chave_permissao', $chave)->delete();
            $this->info("Override removido para {$usuario->nome} em {$chave}.");

            return self::SUCCESS;
        }

        UserPermissao::updateOrCreate(
            [
                'id_user' => $idUser,
                'chave_permissao' => $chave,
            ],
            [
                'permitido' => $acao === 'allow',
            ]
        );

        $status = $acao === 'allow' ? 'permitido' : 'negado';
        $this->info("Permissao {$chave} {$status} para {$usuario->nome}.");

        return self::SUCCESS;
    }
}
