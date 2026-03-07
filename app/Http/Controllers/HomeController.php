<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Lancamento;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $baseQuery = Lancamento::query()
            ->where('id_user', $user->id_user);

        $totalLancamentos = (clone $baseQuery)->count();

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

        $ultimosLancamentos = (clone $baseQuery)
            ->with(['centroCusto.tipo'])
            ->orderBy('dt_faturamento', 'desc')
            ->limit(8)
            ->get();

        return view('home.index')->with([
            'totalLancamentos' => $totalLancamentos,
            'totalEntradas' => $totalEntradas,
            'totalSaidas' => $totalSaidas,
            'saldoAtual' => $totalEntradas - $totalSaidas,
            'ultimosLancamentos' => $ultimosLancamentos,
        ]);
    }

    public function create()
    {
        return redirect()->route('home.index');
    }
}
