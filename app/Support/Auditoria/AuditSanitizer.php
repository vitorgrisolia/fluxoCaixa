<?php

namespace App\Support\Auditoria;

class AuditSanitizer
{
    private const CHAVES_SENSIVEIS = [
        'password',
        'senha',
        'nova_senha',
        'senha_atual',
        'token',
        '_token',
    ];

    public static function sanitizeArray(?array $dados): ?array
    {
        if ($dados === null) {
            return null;
        }

        $filtrados = [];
        foreach ($dados as $chave => $valor) {
            $chaveTexto = (string) $chave;
            if (self::isSensitive($chaveTexto)) {
                $filtrados[$chaveTexto] = '[oculto]';
                continue;
            }

            if (is_array($valor)) {
                $filtrados[$chaveTexto] = self::sanitizeArray($valor);
                continue;
            }

            if (is_object($valor)) {
                $filtrados[$chaveTexto] = '[objeto]';
                continue;
            }

            $filtrados[$chaveTexto] = $valor;
        }

        return $filtrados;
    }

    private static function isSensitive(string $key): bool
    {
        $key = strtolower($key);
        foreach (self::CHAVES_SENSIVEIS as $sensivel) {
            if (str_contains($key, $sensivel)) {
                return true;
            }
        }

        return false;
    }
}
