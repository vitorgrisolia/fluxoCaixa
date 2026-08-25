<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use Illuminate\Support\Facades\Auth;

class HistoricoCompraController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();

        $query = Compra::query();
        if ($usuario->tipo_usuario !== 'admin') {
            $query->where('id_user', $usuario->id_user);
        }

        $compras = $query->with(['usuario', 'cliente'])->withCount('itens')
            ->orderBy('data_compra', 'desc')
            ->get();

        return view('compra.historico.index')->with(compact('compras'));
    }

    public function show(int $id)
    {
        $compra = Compra::with(['usuario', 'cliente', 'itens', 'documentosFiscais'])->findOrFail($id);
        $this->garantirPermissao($compra);

        return view('compra.historico.show')->with(compact('compra'));
    }

    private function garantirPermissao(Compra $compra): void
    {
        $usuario = Auth::user();
        if ($usuario->tipo_usuario !== 'admin' && $compra->id_user !== $usuario->id_user) {
            abort(403, 'Acesso permitido apenas ao responsavel pela compra.');
        }
    }
}
