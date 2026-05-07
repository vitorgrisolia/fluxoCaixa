<?php

namespace App\Http\Controllers;

use App\Models\CaixaMovimentacao;
use App\Models\CaixaTurno;
use App\Models\Compra;
use App\Models\FechamentoCaixa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FechamentoCaixaController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();

        $query = FechamentoCaixa::with(['usuario', 'turno'])
            ->orderBy('data_fechamento', 'desc')
            ->orderBy('created_at', 'desc');

        if ($usuario->tipo_usuario !== 'admin') {
            $query->where('id_user', $usuario->id_user);
        }

        $fechamentos = $query->get();

        return view('fechamentoCaixa.index')->with(compact('fechamentos'));
    }

    public function create()
    {
        $usuario = Auth::user();
        $isAdmin = $usuario->tipo_usuario === 'admin';

        if ($isAdmin) {
            return redirect()->route('fechamento-caixa.index')
                ->with('danger', 'Fechamento de turno deve ser realizado pelo funcionario responsavel.');
        }

        $turnoAberto = $this->obterTurnoAbertoFuncionario($usuario->id_user);
        if (! $turnoAberto) {
            return redirect()->route('caixa.turno.index')
                ->with('danger', 'Nao existe turno aberto. Abra um turno antes de fechar o caixa.');
        }

        $jaFechado = FechamentoCaixa::where('id_turno', $turnoAberto->id_turno)->exists();
        if ($jaFechado) {
            return redirect()->route('fechamento-caixa.index')
                ->with('danger', 'Este turno ja possui fechamento registrado.');
        }

        $inicio = Carbon::parse($turnoAberto->data_abertura);
        $fim = Carbon::now();
        $totaisPagamento = $this->calcularTotaisPagamentoPorTurno($turnoAberto->id_turno);
        $totaisMovimentacao = $this->calcularTotaisMovimentacaoTurno($turnoAberto->id_turno);

        $fechamento = null;

        return view('fechamentoCaixa.form')->with(compact(
            'fechamento',
            'totaisPagamento',
            'totaisMovimentacao',
            'inicio',
            'fim',
            'turnoAberto'
        ));
    }

    public function store(Request $request)
    {
        $usuario = Auth::user();

        if ($usuario->tipo_usuario !== 'funcionario') {
            abort(403, 'Apenas funcionario pode fechar o proprio turno.');
        }

        $turnoAberto = $this->obterTurnoAbertoFuncionario($usuario->id_user);
        if (! $turnoAberto) {
            return redirect()->route('caixa.turno.index')
                ->with('danger', 'Nao existe turno aberto para fechamento.');
        }

        $jaFechado = FechamentoCaixa::where('id_turno', $turnoAberto->id_turno)->exists();
        if ($jaFechado) {
            return redirect()->route('fechamento-caixa.index')
                ->with('danger', 'Este turno ja possui fechamento registrado.');
        }

        $dados = $this->validarDados($request, false);
        $totaisPagamento = $this->calcularTotaisPagamentoPorTurno($turnoAberto->id_turno);
        $totaisMovimentacao = $this->calcularTotaisMovimentacaoTurno($turnoAberto->id_turno);

        DB::transaction(function () use ($dados, $usuario, $turnoAberto, $totaisPagamento, $totaisMovimentacao) {
            $fechamento = new FechamentoCaixa();
            $fechamento->fill($dados);
            $fechamento->id_user = $usuario->id_user;
            $fechamento->id_turno = $turnoAberto->id_turno;
            $fechamento->saldo_inicial = (float) $turnoAberto->saldo_inicial;
            $fechamento->total_saidas = (float) ($dados['total_saidas'] ?? 0);
            $fechamento->valor_dinheiro = $totaisPagamento['valor_dinheiro'];
            $fechamento->valor_cartao = $totaisPagamento['valor_cartao'];
            $fechamento->valor_pix = $totaisPagamento['valor_pix'];
            $fechamento->valor_outros = $totaisPagamento['valor_outros'];
            $fechamento->total_entradas = $totaisPagamento['total_entradas'];
            $fechamento->total_sangria = $totaisMovimentacao['total_sangria'];
            $fechamento->total_suprimento = $totaisMovimentacao['total_suprimento'];
            $fechamento->saldo_final = $this->calcularSaldoFinal(
                $fechamento->saldo_inicial,
                $fechamento->total_entradas,
                $fechamento->total_suprimento,
                $fechamento->total_sangria,
                $fechamento->total_saidas
            );
            $fechamento->save();

            $turnoAberto->status = 'fechado';
            $turnoAberto->data_fechamento = now();
            $turnoAberto->saldo_final = $fechamento->saldo_final;
            $turnoAberto->observacoes_fechamento = $fechamento->observacoes;
            $turnoAberto->save();
        });

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')
            ->with('status', 'Fechamento de caixa concluido. Turno encerrado e usuario deslogado com sucesso.');
    }

    public function show(int $id)
    {
        $fechamento = FechamentoCaixa::with('usuario')->findOrFail($id);
        $this->garantirPermissao($fechamento);

        return view('fechamentoCaixa.show')->with(compact('fechamento'));
    }

    public function edit(int $id)
    {
        $fechamento = FechamentoCaixa::with('turno')->findOrFail($id);
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

        if (! $permitirTotais && $fechamento->id_turno) {
            $turno = CaixaTurno::find($fechamento->id_turno);
            if ($turno) {
                $dados['saldo_inicial'] = (float) $turno->saldo_inicial;
            }
        }

        if (! $permitirTotais) {
            $dados['valor_dinheiro'] = $fechamento->valor_dinheiro;
            $dados['valor_cartao'] = $fechamento->valor_cartao;
            $dados['valor_pix'] = $fechamento->valor_pix;
            $dados['valor_outros'] = $fechamento->valor_outros;
            $dados['total_sangria'] = $fechamento->total_sangria;
            $dados['total_suprimento'] = $fechamento->total_suprimento;
            $dados['total_entradas'] = $fechamento->total_entradas;
        }

        $fechamento->fill($dados);
        $fechamento->total_saidas = (float) ($dados['total_saidas'] ?? $fechamento->total_saidas ?? 0);
        if ($permitirTotais) {
            $fechamento->valor_dinheiro = (float) $fechamento->valor_dinheiro;
            $fechamento->valor_cartao = (float) $fechamento->valor_cartao;
            $fechamento->valor_pix = (float) $fechamento->valor_pix;
            $fechamento->valor_outros = (float) $fechamento->valor_outros;
            $fechamento->total_sangria = (float) ($dados['total_sangria'] ?? $fechamento->total_sangria ?? 0);
            $fechamento->total_suprimento = (float) ($dados['total_suprimento'] ?? $fechamento->total_suprimento ?? 0);
            $fechamento->total_entradas = $fechamento->valor_dinheiro + $fechamento->valor_cartao + $fechamento->valor_pix + $fechamento->valor_outros;
        }

        $fechamento->saldo_final = $this->calcularSaldoFinal(
            (float) $fechamento->saldo_inicial,
            (float) $fechamento->total_entradas,
            (float) ($fechamento->total_suprimento ?? 0),
            (float) ($fechamento->total_sangria ?? 0),
            (float) ($fechamento->total_saidas ?? 0)
        );

        $fechamento->save();

        if ($fechamento->id_turno) {
            $turno = CaixaTurno::find($fechamento->id_turno);
            if ($turno) {
                $turno->saldo_final = $fechamento->saldo_final;
                $turno->observacoes_fechamento = $fechamento->observacoes;
                if (! $turno->data_fechamento) {
                    $turno->data_fechamento = now();
                }
                if ($turno->status !== 'fechado') {
                    $turno->status = 'fechado';
                }
                $turno->save();
            }
        }

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
                'total_sangria' => ['nullable', 'numeric', 'min:0'],
                'total_suprimento' => ['nullable', 'numeric', 'min:0'],
            ]);
        }

        return $request->validate($regras);
    }

    private function calcularSaldoFinal(float $saldoInicial, float $totalEntradas, float $totalSuprimento, float $totalSangria, ?float $totalSaidas): float
    {
        $saidas = (float) ($totalSaidas ?? 0);

        return $saldoInicial + $totalEntradas + $totalSuprimento - $totalSangria - $saidas;
    }

    private function obterTurnoAbertoFuncionario(int $idUser): ?CaixaTurno
    {
        return CaixaTurno::where('id_user', $idUser)
            ->where('status', 'aberto')
            ->orderBy('data_abertura', 'desc')
            ->first();
    }

    private function calcularTotaisPagamentoPorTurno(int $idTurno): array
    {
        $baseQuery = Compra::where('id_turno', $idTurno);

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

    private function calcularTotaisMovimentacaoTurno(int $idTurno): array
    {
        $baseQuery = CaixaMovimentacao::where('id_turno', $idTurno);

        $totalSangria = (float) (clone $baseQuery)->where('tipo_movimentacao', 'sangria')->sum('valor');
        $totalSuprimento = (float) (clone $baseQuery)->where('tipo_movimentacao', 'suprimento')->sum('valor');

        return [
            'total_sangria' => $totalSangria,
            'total_suprimento' => $totalSuprimento,
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
