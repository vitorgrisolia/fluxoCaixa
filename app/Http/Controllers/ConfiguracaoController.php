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
            'razao_social' => ['nullable', 'string', 'max:255'],
            'cnpj' => ['nullable', 'digits:14'],
            'inscricao_estadual' => ['nullable', 'string', 'max:20'],
            'regime_tributario' => ['nullable', 'in:1,2,3'],
            'cnae' => ['nullable', 'digits:7'],
            'codigo_municipio' => ['nullable', 'digits:7'],
            'municipio' => ['nullable', 'string', 'max:255'],
            'uf' => ['nullable', 'string', 'size:2'],
            'cep' => ['nullable', 'digits:8'],
            'ambiente_fiscal' => ['required', 'in:homologacao,producao'],
            'serie_nfe' => ['required', 'integer', 'min:1', 'max:999'],
            'proximo_numero_nfe' => ['required', 'integer', 'min:1'],
            'serie_nfce' => ['required', 'integer', 'min:1', 'max:999'],
            'proximo_numero_nfce' => ['required', 'integer', 'min:1'],
            'csc_id' => ['nullable', 'string', 'max:50'],
            'csc_token' => ['nullable', 'string', 'max:255'],
        ]);

        $dados['uf'] = isset($dados['uf']) ? strtoupper($dados['uf']) : null;
        if (empty($dados['csc_token'])) {
            unset($dados['csc_token']);
        }

        if (! $configuracao) {
            $configuracao = new ConfiguracaoSistema();
        }

        $configuracao->fill($dados);
        $configuracao->save();

        return redirect()->route('configuracoes.index')
            ->with('success', 'Configuracoes atualizadas com sucesso.');
    }
}
