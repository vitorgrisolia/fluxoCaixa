<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HistoricoCompraController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();

        $compras = Compra::where('id_user', $usuario->id_user)
            ->orderBy('data_compra', 'desc')
            ->get();

        return view('compra.historico.index')->with(compact('compras'));
    }

    public function create()
    {
        $compra = null;
        return view('compra.historico.form')->with(compact('compra'));
    }

    public function store(Request $request)
    {
        $dados = $this->validarDados($request);

        $compra = new Compra();
        $compra->fill($dados);
        $compra->id_user = Auth::user()->id_user;
        $compra->save();

        return redirect()->route('leitor.historico.index')
            ->with('success', 'Compra registrada com sucesso.');
    }

    public function show(int $id)
    {
        $compra = Compra::findOrFail($id);
        $this->garantirPermissao($compra);

        return view('compra.historico.show')->with(compact('compra'));
    }

    public function edit(int $id)
    {
        $compra = Compra::findOrFail($id);
        $this->garantirPermissao($compra);

        return view('compra.historico.form')->with(compact('compra'));
    }

    public function update(Request $request, int $id)
    {
        $compra = Compra::findOrFail($id);
        $this->garantirPermissao($compra);

        $dados = $this->validarDados($request);
        $compra->fill($dados);
        $compra->save();

        return redirect()->route('leitor.historico.index')
            ->with('success', 'Compra atualizada com sucesso.');
    }

    public function destroy(int $id)
    {
        $compra = Compra::findOrFail($id);
        $this->garantirPermissao($compra);

        $compra->delete();

        return redirect()->route('leitor.historico.index')
            ->with('danger', 'Compra excluida com sucesso.');
    }

    private function validarDados(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'data_compra' => ['required', 'date'],
            'valor_total' => ['required', 'numeric', 'min:0'],
            'forma_pagamento' => ['required', 'in:pix,dinheiro,cartao_debito,cartao_credito,boleto,vale_alimentacao'],
            'dividir_valor' => ['nullable', 'in:sim,nao'],
            'parcelas' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);
        $validator->sometimes('dividir_valor', 'required', function ($input) {
            return $input->forma_pagamento === 'cartao_credito';
        });
        $validator->sometimes('parcelas', 'required', function ($input) {
            return $input->forma_pagamento === 'cartao_credito' && $input->dividir_valor === 'sim';
        });

        $dados = $validator->validate();
        $cartaoCredito = $dados['forma_pagamento'] === 'cartao_credito';

        $dados['dividir_valor'] = $cartaoCredito ? ($dados['dividir_valor'] ?? 'nao') : 'nao';
        $dados['parcelas'] = ($cartaoCredito && $dados['dividir_valor'] === 'sim')
            ? (int) ($dados['parcelas'] ?? 1)
            : null;

        return $dados;
    }

    private function garantirPermissao(Compra $compra): void
    {
        $usuario = Auth::user();
        if ($compra->id_user !== $usuario->id_user) {
            abort(403, 'Acesso permitido apenas ao responsavel pela compra.');
        }
    }
}
