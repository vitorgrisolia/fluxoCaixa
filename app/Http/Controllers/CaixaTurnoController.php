<?php

namespace App\Http\Controllers;

use App\Models\CaixaMovimentacao;
use App\Models\CaixaTurno;
use App\Models\Compra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CaixaTurnoController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();
        $isAdmin = $usuario->tipo_usuario === 'admin';

        if ($isAdmin) {
            $turnosRecentes = CaixaTurno::with('usuario')
                ->orderBy('data_abertura', 'desc')
                ->take(50)
                ->get();
            $turnosAbertos = $turnosRecentes->where('status', 'aberto')->count();

            return view('caixa.turno')->with(compact('isAdmin', 'turnosRecentes', 'turnosAbertos'));
        }

        $turnoAberto = CaixaTurno::where('id_user', $usuario->id_user)
            ->where('status', 'aberto')
            ->orderBy('data_abertura', 'desc')
            ->first();

        $movimentacoesTurno = collect();
        $resumoTurno = [
            'vendas_total' => 0.0,
            'vendas_dinheiro' => 0.0,
            'total_sangria' => 0.0,
            'total_suprimento' => 0.0,
            'saldo_dinheiro_estimado' => 0.0,
        ];

        if ($turnoAberto) {
            $movimentacoesTurno = CaixaMovimentacao::where('id_turno', $turnoAberto->id_turno)
                ->orderBy('data_movimentacao', 'desc')
                ->orderBy('id_movimentacao_caixa', 'desc')
                ->get();
            $resumoTurno = $this->calcularResumoTurno($turnoAberto);
        }

        $turnosRecentes = CaixaTurno::where('id_user', $usuario->id_user)
            ->orderBy('data_abertura', 'desc')
            ->take(15)
            ->get();

        return view('caixa.turno')->with(compact(
            'isAdmin',
            'turnoAberto',
            'movimentacoesTurno',
            'resumoTurno',
            'turnosRecentes'
        ));
    }

    public function abrir(Request $request)
    {
        $usuario = Auth::user();
        if ($usuario->tipo_usuario !== 'funcionario') {
            abort(403, 'Acesso permitido apenas para funcionarios.');
        }

        $dados = $request->validate([
            'saldo_inicial' => ['required', 'numeric', 'min:0'],
        ]);

        $turnoAberto = CaixaTurno::where('id_user', $usuario->id_user)
            ->where('status', 'aberto')
            ->exists();

        if ($turnoAberto) {
            return redirect()->route('caixa.turno.index')
                ->with('danger', 'Ja existe um turno aberto para este funcionario.');
        }

        CaixaTurno::create([
            'id_user' => $usuario->id_user,
            'data_abertura' => now(),
            'saldo_inicial' => $dados['saldo_inicial'],
            'status' => 'aberto',
        ]);

        return redirect()->route('caixa.turno.index')
            ->with('success', 'Turno de caixa aberto com sucesso.');
    }

    public function movimentar(Request $request)
    {
        $usuario = Auth::user();
        if ($usuario->tipo_usuario !== 'funcionario') {
            abort(403, 'Acesso permitido apenas para funcionarios.');
        }

        $dados = $request->validate([
            'tipo_movimentacao' => ['required', 'in:sangria,suprimento'],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'observacao' => ['nullable', 'string', 'max:500'],
        ]);

        $turnoAberto = CaixaTurno::where('id_user', $usuario->id_user)
            ->where('status', 'aberto')
            ->orderBy('data_abertura', 'desc')
            ->first();

        if (! $turnoAberto) {
            return redirect()->route('caixa.turno.index')
                ->with('danger', 'Nao existe turno aberto para registrar movimentacao.');
        }

        $resumo = $this->calcularResumoTurno($turnoAberto);
        $valor = (float) $dados['valor'];

        if ($dados['tipo_movimentacao'] === 'sangria' && $valor > $resumo['saldo_dinheiro_estimado']) {
            return redirect()->route('caixa.turno.index')
                ->with('danger', 'Sangria maior que o saldo estimado em dinheiro do turno.');
        }

        CaixaMovimentacao::create([
            'id_turno' => $turnoAberto->id_turno,
            'id_user' => $usuario->id_user,
            'tipo_movimentacao' => $dados['tipo_movimentacao'],
            'valor' => $valor,
            'observacao' => $dados['observacao'] ?? null,
            'data_movimentacao' => now(),
        ]);

        return redirect()->route('caixa.turno.index')
            ->with('success', 'Movimentacao de caixa registrada com sucesso.');
    }

    private function calcularResumoTurno(CaixaTurno $turno): array
    {
        $vendasBase = Compra::where('id_turno', $turno->id_turno);
        $vendasTotal = (float) (clone $vendasBase)->sum('valor_total');
        $vendasDinheiro = (float) (clone $vendasBase)
            ->where('forma_pagamento', 'dinheiro')
            ->sum('valor_total');

        $movimentacoesBase = CaixaMovimentacao::where('id_turno', $turno->id_turno);
        $totalSangria = (float) (clone $movimentacoesBase)
            ->where('tipo_movimentacao', 'sangria')
            ->sum('valor');
        $totalSuprimento = (float) (clone $movimentacoesBase)
            ->where('tipo_movimentacao', 'suprimento')
            ->sum('valor');

        $saldoDinheiroEstimado = (float) $turno->saldo_inicial + $vendasDinheiro + $totalSuprimento - $totalSangria;

        return [
            'vendas_total' => $vendasTotal,
            'vendas_dinheiro' => $vendasDinheiro,
            'total_sangria' => $totalSangria,
            'total_suprimento' => $totalSuprimento,
            'saldo_dinheiro_estimado' => $saldoDinheiroEstimado,
        ];
    }
}

