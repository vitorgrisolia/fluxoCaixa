<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditoriaLog::with('usuario')
            ->orderBy('created_at', 'desc');

        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        if ($request->filled('usuario')) {
            $query->where('id_user', $request->usuario);
        }

        if ($request->filled('acao')) {
            $query->where('acao', $request->acao);
        }

        if ($request->filled('rota')) {
            $query->where('rota', 'like', '%'.$request->rota.'%');
        }

        $logs = $query->paginate(20)->appends($request->query());
        $usuarios = User::orderBy('nome')->get();

        return view('auditoria.index')->with([
            'logs' => $logs,
            'usuarios' => $usuarios,
            'dataInicio' => $request->get('data_inicio', Carbon::now()->startOfMonth()->toDateString()),
            'dataFim' => $request->get('data_fim', Carbon::now()->endOfMonth()->toDateString()),
        ]);
    }
}
