<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user() ?? Auth::user();
        if (! $user) {
            abort(401, 'Autenticacao obrigatoria.');
        }

        if (! method_exists($user, 'possuiPermissao') || ! $user->possuiPermissao($permission)) {
            abort(403, "Sem permissao para executar esta acao ({$permission}).");
        }

        return $next($request);
    }
}
