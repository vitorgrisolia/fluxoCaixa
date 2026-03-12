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

        AuditoriaLog::create([
            'id_user' => $usuario->id_user,
            'acao' => $acao,
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

        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
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
        if ($rota === 'login') {
            return 'LOGIN';
        }

        if ($rota === 'logout') {
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

        return strtoupper($request->method());
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

            $filtrados[$chave] = is_array($valor) ? $valor : (string) $valor;
        }

        return $filtrados;
    }
}
