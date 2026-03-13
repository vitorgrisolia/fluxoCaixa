<?php

namespace App\Http\Middleware;

use App\Models\AuditoriaLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $usuarioAntes = Auth::user();

        $response = $next($request);

        if (! $this->deveRegistrar($request)) {
            return $response;
        }

        $usuario = Auth::user() ?? $usuarioAntes;
        if (! $usuario) {
            return $response;
        }

        $rota = optional($request->route())->getName();
        $acao = $this->resolverAcao($request, $rota);
        $descricao = $this->resolverDescricao($request, $rota, $acao);

        AuditoriaLog::create([
            'id_user' => $usuario->id_user,
            'acao' => $acao,
            'descricao' => $descricao,
            'rota' => $rota,
            'metodo' => $request->method(),
            'url' => substr((string) $request->path(), 0, 255),
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'dados' => $this->filtrarDados($request->all()),
        ]);

        return $response;
    }

    private function deveRegistrar(Request $request): bool
    {
        if (! $request->route()) {
            return false;
        }

        if (in_array($request->method(), ['HEAD', 'OPTIONS'], true)) {
            return false;
        }

        $rota = $request->route()->getName();
        if ($rota && str_starts_with($rota, 'auditoria.')) {
            return false;
        }

        return true;
    }

    private function resolverAcao(Request $request, ?string $rota): string
    {
        if ($rota === 'login' || $request->path() === 'login') {
            return 'LOGIN';
        }

        if ($rota === 'logout' || $request->path() === 'logout') {
            return 'LOGOUT';
        }

        if ($rota) {
            if (str_ends_with($rota, '.store')) {
                return 'CRIAR';
            }
            if (str_ends_with($rota, '.update')) {
                return 'ATUALIZAR';
            }
            if (str_ends_with($rota, '.destroy') || str_ends_with($rota, '.delete')) {
                return 'EXCLUIR';
            }
        }

        if ($request->method() === 'GET') {
            return 'ACESSO';
        }

        return strtoupper($request->method());
    }

    private function resolverDescricao(Request $request, ?string $rota, string $acao): string
    {
        if ($acao === 'LOGIN') {
            return 'Realizou login no sistema.';
        }

        if ($acao === 'LOGOUT') {
            return 'Realizou logout do sistema.';
        }

        $entidade = $this->resolverEntidade($rota);
        $id = $request->route('id');
        $idTexto = $id ? " (ID {$id})" : '';

        return match ($acao) {
            'CRIAR' => "Criou {$entidade}{$idTexto}.",
            'ATUALIZAR' => "Atualizou {$entidade}{$idTexto}.",
            'EXCLUIR' => "Excluiu {$entidade}{$idTexto}.",
            'ACESSO' => "Acessou {$entidade}.",
            default => "Executou acao {$acao} em {$entidade}.",
        };
    }

    private function resolverEntidade(?string $rota): string
    {
        if (! $rota) {
            return 'pagina do sistema';
        }

        $mapa = [
            'dashboard' => 'Dashboard',
            'home' => 'Home',
            'lancamento' => 'Lancamentos',
            'centro' => 'Centro de custo',
            'tipo' => 'Tipos',
            'usuario' => 'Usuarios',
            'produto' => 'Produtos',
            'estoque' => 'Controle de estoque',
            'controle-financeiro' => 'Controle financeiro',
            'relatorios' => 'Relatorios',
            'fechamento-caixa' => 'Fechamento de caixa',
            'leitor' => 'Leitor de produtos',
            'perfil' => 'Perfil',
            'configuracoes' => 'Configuracoes',
            'auditoria' => 'Auditoria',
        ];

        foreach ($mapa as $prefixo => $nome) {
            if (str_starts_with($rota, $prefixo)) {
                return $nome;
            }
        }

        return "rota {$rota}";
    }

    private function filtrarDados(array $dados): array
    {
        $bloqueados = ['password', 'senha', 'nova_senha', 'senha_atual', 'token', '_token'];

        $filtrados = [];
        foreach ($dados as $chave => $valor) {
            $chaveLower = strtolower((string) $chave);
            $bloquear = false;
            foreach ($bloqueados as $item) {
                if (str_contains($chaveLower, $item)) {
                    $bloquear = true;
                    break;
                }
            }

            if ($bloquear) {
                $filtrados[$chave] = '[oculto]';
                continue;
            }

            if (is_array($valor)) {
                $filtrados[$chave] = $this->sanitizarArray($valor);
            } elseif (is_object($valor)) {
                $filtrados[$chave] = '[objeto]';
            } else {
                $filtrados[$chave] = (string) $valor;
            }
        }

        return $filtrados;
    }

    private function sanitizarArray(array $valores): array
    {
        $resultado = [];
        foreach ($valores as $chave => $valor) {
            if (is_array($valor)) {
                $resultado[$chave] = $this->sanitizarArray($valor);
            } elseif (is_object($valor)) {
                $resultado[$chave] = '[objeto]';
            } else {
                $resultado[$chave] = (string) $valor;
            }
        }

        return $resultado;
    }
}
