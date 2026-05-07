<?php

namespace App\Support\Auditoria;

use App\Models\AuditoriaLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static function logRequest(
        Request $request,
        ?int $idUsuario,
        string $acao,
        string $descricao,
        ?string $rota,
        array $dadosRequisicao = []
    ): void {
        AuditoriaLog::create([
            'id_user' => $idUsuario,
            'acao' => $acao,
            'descricao' => $descricao,
            'origem' => 'middleware',
            'rota' => $rota,
            'metodo' => $request->method(),
            'url' => substr((string) $request->path(), 0, 255),
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'dados' => AuditSanitizer::sanitizeArray($dadosRequisicao),
            'dados_antes' => null,
            'dados_depois' => null,
        ]);
    }

    public static function logModel(
        string $acao,
        Model $model,
        ?array $antes = null,
        ?array $depois = null
    ): void {
        $request = request();
        $rota = $request?->route()?->getName();
        $entidade = $model->getTable();
        $idEntidade = (string) $model->getKey();
        $descricao = self::buildDescription($acao, $entidade, $idEntidade);

        AuditoriaLog::create([
            'id_user' => Auth::user()?->id_user,
            'acao' => $acao,
            'descricao' => $descricao,
            'origem' => 'observer',
            'rota' => $rota,
            'metodo' => $request?->method() ?? 'CLI',
            'url' => substr((string) ($request?->path() ?? 'console'), 0, 255),
            'ip' => $request?->ip(),
            'user_agent' => $request ? substr((string) $request->userAgent(), 0, 255) : 'artisan',
            'entidade' => $entidade,
            'entidade_id' => $idEntidade,
            'dados' => [
                'campos' => self::extractFields($antes, $depois),
            ],
            'dados_antes' => AuditSanitizer::sanitizeArray($antes),
            'dados_depois' => AuditSanitizer::sanitizeArray($depois),
        ]);
    }

    private static function buildDescription(string $acao, string $entidade, string $id): string
    {
        return match ($acao) {
            'CRIAR' => "Criou registro em {$entidade} (ID {$id}).",
            'ATUALIZAR' => "Atualizou registro em {$entidade} (ID {$id}).",
            'EXCLUIR' => "Excluiu registro em {$entidade} (ID {$id}).",
            default => "Registrou evento {$acao} em {$entidade} (ID {$id}).",
        };
    }

    private static function extractFields(?array $antes, ?array $depois): array
    {
        $camposAntes = is_array($antes) ? array_keys($antes) : [];
        $camposDepois = is_array($depois) ? array_keys($depois) : [];

        return array_values(array_unique(array_merge($camposAntes, $camposDepois)));
    }
}
