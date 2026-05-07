<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'token_name' => ['nullable', 'string', 'max:120'],
            'abilities' => ['nullable', 'array'],
            'abilities.*' => ['string', 'max:120'],
        ]);

        $user = User::where('email', $dados['email'])->first();
        if (! $user || ! Hash::check($dados['password'], $user->password)) {
            return response()->json([
                'message' => 'Credenciais invalidas.',
            ], 401);
        }

        if (! $user->possuiPermissao('api.tokens.gerenciar')) {
            return response()->json([
                'message' => 'Usuario sem permissao para emitir token de integracao.',
            ], 403);
        }

        $abilitiesSolicitadas = isset($dados['abilities']) && is_array($dados['abilities'])
            ? $dados['abilities']
            : [];
        $abilitiesPermitidas = $this->resolverAbilitiesPermitidas($user, $abilitiesSolicitadas);
        if (empty($abilitiesPermitidas)) {
            return response()->json([
                'message' => 'Usuario autenticado, mas sem abilities API disponiveis para emissao de token.',
            ], 422);
        }

        $tokenName = trim((string) ($dados['token_name'] ?? 'integracao-'.now()->format('YmdHis')));

        $token = $user->createToken($tokenName, $abilitiesPermitidas);

        return response()->json([
            'message' => 'Token criado com sucesso.',
            'token' => $token->plainTextToken,
            'token_name' => $tokenName,
            'abilities' => $abilitiesPermitidas,
        ], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $tokenAtual = $request->user()?->currentAccessToken();
        if (! $tokenAtual) {
            return response()->json([
                'message' => 'Nenhum token autenticado para revogar.',
            ], 400);
        }

        $tokenAtual->delete();

        return response()->json([
            'message' => 'Token revogado com sucesso.',
        ]);
    }

    private function resolverAbilitiesPermitidas(User $user, array $abilitiesSolicitadas): array
    {
        if ($user->possuiPermissao('*')) {
            return ['*'];
        }

        $abilitiesApi = array_values(array_filter(
            (array) config('permissions.available', []),
            function ($item) {
                return is_string($item) && str_starts_with($item, 'api.');
            }
        ));

        $abilitiesDoUsuario = array_values(array_filter($abilitiesApi, function ($ability) use ($user) {
            return $user->possuiPermissao($ability);
        }));

        if (empty($abilitiesSolicitadas)) {
            return $abilitiesDoUsuario;
        }

        $solicitadasValidas = array_values(array_filter($abilitiesSolicitadas, function ($ability) use ($abilitiesDoUsuario) {
            return in_array($ability, $abilitiesDoUsuario, true);
        }));

        return empty($solicitadasValidas) ? $abilitiesDoUsuario : $solicitadasValidas;
    }
}
