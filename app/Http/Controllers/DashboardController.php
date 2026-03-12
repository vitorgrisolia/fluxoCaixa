<?php

namespace App\Http\Controllers;

use App\Models\Lancamento;
use App\Models\Produto;
use App\Models\FechamentoCaixa;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();
        $hoje = Carbon::today();

        $totalVencidos = Produto::whereDate('validade', '<', $hoje)->count();
        $totalVencendo = Produto::whereBetween('validade', [$hoje, $hoje->copy()->addDays(30)])->count();

        $inicioMes = $hoje->copy()->startOfMonth();
        $fimMes = $hoje->copy()->endOfMonth();

        $baseQuery = Lancamento::where('id_user', $usuario->id_user)
            ->whereBetween('dt_faturamento', [$inicioMes, $fimMes]);

        $totalEntradas = (clone $baseQuery)
            ->whereHas('centroCusto.tipo', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->whereRaw('LOWER(tipo) LIKE ?', ['%entrada%'])
                        ->orWhereRaw('LOWER(tipo) LIKE ?', ['%receita%']);
                });
            })
            ->sum('valor');

        $totalSaidas = (clone $baseQuery)
            ->whereHas('centroCusto.tipo', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->whereRaw('LOWER(tipo) LIKE ?', ['%saida%'])
                        ->orWhereRaw('LOWER(tipo) LIKE ?', ['%despesa%']);
                });
            })
            ->sum('valor');

        $saldoMes = $totalEntradas - $totalSaidas;

        $fechamentosRecentes = FechamentoCaixa::with('usuario')
            ->orderBy('data_fechamento', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard')->with([
            'totalVencidos' => $totalVencidos,
            'totalVencendo' => $totalVencendo,
            'saldoMes' => $saldoMes,
            'inicioMes' => $inicioMes,
            'fimMes' => $fimMes,
            'fechamentosRecentes' => $fechamentosRecentes,
        ]);
    }
}
