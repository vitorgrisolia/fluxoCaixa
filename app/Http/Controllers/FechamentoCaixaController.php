<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\FechamentoCaixa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FechamentoCaixaController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();

        $query = FechamentoCaixa::with('usuario')
            ->orderBy('data_fechamento', 'desc')
            ->orderBy('created_at', 'desc');

        if ($usuario->tipo_usuario !== 'admin') {
            $query->where('id_user', $usuario->id_user);
        }

        $fechamentos = $query->get();

        return view('fechamentoCaixa.index')->with(compact('fechamentos'));
    }

    public function create(Request $request)
    {
        $usuario = Auth::user();
        [$inicio, $fim] = $this->obterPeriodoLogin($request);
        $totaisPagamento = $this->calcularTotaisPagamento($usuario->id_user, $inicio, $fim);

        $fechamento = null;
        return view('fechamentoCaixa.form')->with(compact('fechamento', 'totaisPagamento', 'inicio', 'fim'));
    }

    public function store(Request $request)
    {
        $usuario = Auth::user();
        [$inicio, $fim] = $this->obterPeriodoLogin($request);
        $totaisPagamento = $this->calcularTotaisPagamento($usuario->id_user, $inicio, $fim);
        $dados = $this->validarDados($request, false);

        $fechamento = new FechamentoCaixa();
        $fechamento->fill($dados);
        $fechamento->id_user = $usuario->id_user;
        $fechamento->total_saidas = (float) ($dados['total_saidas'] ?? 0);
        $fechamento->valor_dinheiro = $totaisPagamento['valor_dinheiro'];
        $fechamento->valor_cartao = $totaisPagamento['valor_cartao'];
        $fechamento->valor_pix = $totaisPagamento['valor_pix'];
        $fechamento->valor_outros = $totaisPagamento['valor_outros'];
        $fechamento->total_entradas = $totaisPagamento['total_entradas'];
        $fechamento->saldo_final = $this->calcularSaldoFinal($fechamento->saldo_inicial, $fechamento->total_entradas, $fechamento->total_saidas);
        $fechamento->save();

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')
            ->with('status', 'Fechamento de caixa concluido. Usuario deslogado com sucesso.');
    }

    public function show(int $id)
    {
        $fechamento = FechamentoCaixa::with('usuario')->findOrFail($id);
        $this->garantirPermissao($fechamento);

        return view('fechamentoCaixa.show')->with(compact('fechamento'));
    }

    public function edit(int $id)
    {
        $fechamento = FechamentoCaixa::findOrFail($id);
        $this->garantirPermissao($fechamento);

        return view('fechamentoCaixa.form')->with(compact('fechamento'));
    }

    public function update(Request $request, int $id)
    {
        $fechamento = FechamentoCaixa::findOrFail($id);
        $this->garantirPermissao($fechamento);

        $usuario = Auth::user();
        $permitirTotais = $usuario->tipo_usuario === 'admin';
        $dados = $this->validarDados($request, $permitirTotais);

        if (! $permitirTotais) {
            $dados['valor_dinheiro'] = $fechamento->valor_dinheiro;
            $dados['valor_cartao'] = $fechamento->valor_cartao;
            $dados['valor_pix'] = $fechamento->valor_pix;
            $dados['valor_outros'] = $fechamento->valor_outros;
            $dados['total_entradas'] = $fechamento->total_entradas;
        }

        $fechamento->fill($dados);
        $fechamento->total_saidas = (float) ($dados['total_saidas'] ?? $fechamento->total_saidas ?? 0);
        if ($permitirTotais) {
            $fechamento->valor_dinheiro = (float) $fechamento->valor_dinheiro;
            $fechamento->valor_cartao = (float) $fechamento->valor_cartao;
            $fechamento->valor_pix = (float) $fechamento->valor_pix;
            $fechamento->valor_outros = (float) $fechamento->valor_outros;
            $fechamento->total_entradas = $fechamento->valor_dinheiro + $fechamento->valor_cartao + $fechamento->valor_pix + $fechamento->valor_outros;
        }
        $fechamento->saldo_final = $this->calcularSaldoFinal($fechamento->saldo_inicial, $fechamento->total_entradas, $fechamento->total_saidas);
        $fechamento->save();

        return redirect()->route('fechamento-caixa.index')
            ->with('success', 'Fechamento de caixa atualizado com sucesso.');
    }

    public function destroy(int $id)
    {
        $fechamento = FechamentoCaixa::findOrFail($id);
        $this->garantirPermissao($fechamento);

        $fechamento->delete();

        return redirect()->route('fechamento-caixa.index')
            ->with('danger', 'Fechamento de caixa excluido com sucesso.');
    }

    private function validarDados(Request $request, bool $permitirTotais): array
    {
        $regras = [
            'data_fechamento' => ['required', 'date'],
            'saldo_inicial' => ['required', 'numeric', 'min:0'],
            'total_saidas' => ['nullable', 'numeric', 'min:0'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ];

        if ($permitirTotais) {
            $regras = array_merge($regras, [
                'valor_dinheiro' => ['required', 'numeric', 'min:0'],
                'valor_cartao' => ['required', 'numeric', 'min:0'],
                'valor_pix' => ['required', 'numeric', 'min:0'],
                'valor_outros' => ['required', 'numeric', 'min:0'],
            ]);
        }

        return $request->validate($regras);
    }

    private function calcularSaldoFinal(float $saldoInicial, float $totalEntradas, ?float $totalSaidas): float
    {
        $saidas = (float) ($totalSaidas ?? 0);

        return $saldoInicial + $totalEntradas - $saidas;
    }

    private function obterPeriodoLogin(Request $request): array
    {
        $inicio = $request->session()->get('login_at');
        $inicio = $inicio ? Carbon::parse($inicio) : Carbon::now()->startOfDay();

        return [$inicio, Carbon::now()];
    }

    private function calcularTotaisPagamento(int $idUser, Carbon $inicio, Carbon $fim): array
    {
        $baseQuery = Compra::where('id_user', $idUser)
            ->whereBetween('data_compra', [$inicio, $fim]);

        $valorDinheiro = (float) (clone $baseQuery)->where('forma_pagamento', 'dinheiro')->sum('valor_total');
        $valorPix = (float) (clone $baseQuery)->where('forma_pagamento', 'pix')->sum('valor_total');
        $valorCartao = (float) (clone $baseQuery)->whereIn('forma_pagamento', ['cartao_debito', 'cartao_credito'])->sum('valor_total');
        $valorOutros = (float) (clone $baseQuery)->whereIn('forma_pagamento', ['boleto', 'vale_alimentacao'])->sum('valor_total');

        $totalEntradas = $valorDinheiro + $valorCartao + $valorPix + $valorOutros;

        return [
            'valor_dinheiro' => $valorDinheiro,
            'valor_cartao' => $valorCartao,
            'valor_pix' => $valorPix,
            'valor_outros' => $valorOutros,
            'total_entradas' => $totalEntradas,
        ];
    }

    private function garantirPermissao(FechamentoCaixa $fechamento): void
    {
        $usuario = Auth::user();
        if ($usuario->tipo_usuario !== 'admin' && $fechamento->id_user !== $usuario->id_user) {
            abort(403, 'Acesso permitido apenas ao responsavel pelo fechamento.');
        }
    }
}
