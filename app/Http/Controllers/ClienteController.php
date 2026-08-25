<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::orderBy('nome')->paginate(20);
        return view('cliente.index', compact('clientes'));
    }

    public function create()
    {
        return view('cliente.form', ['cliente' => new Cliente()]);
    }

    public function store(Request $request)
    {
        Cliente::create($this->validateData($request));
        return redirect()->route('cliente.index')->with('success', 'Cliente cadastrado com sucesso.');
    }

    public function edit(int $id)
    {
        return view('cliente.form', ['cliente' => Cliente::findOrFail($id)]);
    }

    public function update(Request $request, int $id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->update($this->validateData($request, $cliente));
        return redirect()->route('cliente.index')->with('success', 'Cliente atualizado com sucesso.');
    }

    public function destroy(int $id)
    {
        Cliente::findOrFail($id)->delete();
        return redirect()->route('cliente.index')->with('success', 'Cliente desativado com sucesso.');
    }

    private function validateData(Request $request, ?Cliente $cliente = null): array
    {
        $id = $cliente?->id_cliente;
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cpf_cnpj' => ['nullable', 'digits_between:11,14', Rule::unique('clientes', 'cpf_cnpj')->ignore($id, 'id_cliente')],
            'inscricao_estadual' => ['nullable', 'string', 'max:20'],
            'indicador_ie' => ['nullable', 'in:contribuinte,isento,nao_contribuinte'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'logradouro' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:255'],
            'bairro' => ['nullable', 'string', 'max:255'],
            'codigo_municipio' => ['nullable', 'digits:7'],
            'municipio' => ['nullable', 'string', 'max:255'],
            'uf' => ['nullable', 'string', 'size:2'],
            'cep' => ['nullable', 'digits:8'],
        ]);
        $data['uf'] = isset($data['uf']) ? strtoupper($data['uf']) : null;
        return $data;
    }
}
