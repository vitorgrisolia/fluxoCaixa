<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracaoSistema;
use Illuminate\Http\Request;

class ConfiguracaoController extends Controller
{
    public function index()
    {
        $configuracao = ConfiguracaoSistema::first();

        if (! $configuracao) {
            $configuracao = ConfiguracaoSistema::create([
                'nome_sistema' => config('app.name', 'FluxoCaixa'),
                'moeda' => 'BRL',
            ]);
        }

        return view('configuracoes.index')->with(compact('configuracao'));
    }

    public function update(Request $request)
    {
        $configuracao = ConfiguracaoSistema::first();

        $dados = $request->validate([
            'nome_sistema' => ['required', 'string', 'max:255'],
            'nome_empresa' => ['nullable', 'string', 'max:255'],
            'email_contato' => ['nullable', 'email', 'max:255'],
            'telefone_contato' => ['nullable', 'string', 'max:30'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'moeda' => ['required', 'string', 'max:10'],
            'mensagem_rodape' => ['nullable', 'string', 'max:255'],
        ]);

        if (! $configuracao) {
            $configuracao = new ConfiguracaoSistema();
        }

        $configuracao->fill($dados);
        $configuracao->save();

        return redirect()->route('configuracoes.index')
            ->with('success', 'Configuracoes atualizadas com sucesso.');
    }
}
