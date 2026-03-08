<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ProdutoController extends Controller
{
    public function index()
    {
        $produtos = Produto::orderBy('validade')->orderBy('nome')->get();
        $hoje = Carbon::today();

        $totalVencidos = Produto::whereDate('validade', '<', $hoje)->count();
        $totalVencendo = Produto::whereBetween('validade', [$hoje, $hoje->copy()->addDays(30)])->count();

        return view('produto.index')->with(compact('produtos', 'totalVencidos', 'totalVencendo'));
    }

    public function create()
    {
        $produto = null;

        return view('produto.form')->with(compact('produto'));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'lote' => ['required', 'string', 'max:100'],
            'quantidade' => ['required', 'integer', 'min:0'],
            'tipo_quantidade' => ['required', 'in:caixa,unidade'],
            'validade' => ['required', 'date'],
            'preco_compra' => ['required', 'numeric', 'min:0'],
            'preco_venda' => ['required', 'numeric', 'min:0'],
        ]);

        $produto = new Produto();
        $produto->fill($dados);
        $produto->save();

        return redirect()->route('produto.index')->with('success', 'Produto cadastrado com sucesso.');
    }

    public function edit(int $id)
    {
        $produto = Produto::findOrFail($id);

        return view('produto.form')->with(compact('produto'));
    }

    public function update(Request $request, int $id)
    {
        $produto = Produto::findOrFail($id);

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'lote' => ['required', 'string', 'max:100'],
            'quantidade' => ['required', 'integer', 'min:0'],
            'tipo_quantidade' => ['required', 'in:caixa,unidade'],
            'validade' => ['required', 'date'],
            'preco_compra' => ['required', 'numeric', 'min:0'],
            'preco_venda' => ['required', 'numeric', 'min:0'],
        ]);

        $produto->fill($dados);
        $produto->save();

        return redirect()->route('produto.index')->with('success', 'Produto atualizado com sucesso.');
    }

    public function destroy(int $id)
    {
        $produto = Produto::findOrFail($id);
        $produto->delete();

        return redirect()->route('produto.index')->with('success', 'Produto excluido com sucesso.');
    }
}
