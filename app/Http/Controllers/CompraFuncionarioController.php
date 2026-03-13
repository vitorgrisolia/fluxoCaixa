<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompraFuncionarioController extends Controller
{
    public function create()
    {
        $totalCompra = Produto::sum(DB::raw('preco_compra * quantidade'));

        return view('compra.finalizar')->with(compact('totalCompra'));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'forma_pagamento' => ['required', 'in:pix,dinheiro,cartao_debito,cartao_credito,boleto,vale_alimentacao'],
            'dividir_valor' => ['required', 'in:sim,nao'],
            'parcelas' => ['nullable', 'integer', 'min:1', 'max:12', 'required_if:dividir_valor,sim'],
        ]);

        $totalCompra = Produto::sum(DB::raw('preco_compra * quantidade'));

        $compra = new Compra();
        $compra->fill([
            'data_compra' => now(),
            'valor_total' => $totalCompra,
            'forma_pagamento' => $dados['forma_pagamento'],
            'dividir_valor' => $dados['dividir_valor'],
            'parcelas' => $dados['dividir_valor'] === 'sim' ? (int) ($dados['parcelas'] ?? 1) : null,
        ]);
        $compra->id_user = Auth::user()->id_user;
        $compra->save();

        $formas = [
            'pix' => 'PIX',
            'dinheiro' => 'Dinheiro',
            'cartao_debito' => 'Cartao de debito',
            'cartao_credito' => 'Cartao de credito',
            'boleto' => 'Boleto',
            'vale_alimentacao' => 'Vale alimentacao',
        ];

        $formaSelecionada = $formas[$dados['forma_pagamento']] ?? $dados['forma_pagamento'];
        $mensagem = "Compra finalizada com sucesso. Forma de pagamento: {$formaSelecionada}.";

        if ($dados['dividir_valor'] === 'sim' && !empty($dados['parcelas'])) {
            $valorParcela = $totalCompra / (int) $dados['parcelas'];

            $mensagem .= " Pagamento dividido em {$dados['parcelas']}x de R$ ".number_format($valorParcela, 2, ',', '.').".";
        }

        return redirect()->route('leitor.finalizar')->with('success', $mensagem);
    }
}
