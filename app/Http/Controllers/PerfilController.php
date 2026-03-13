<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PerfilController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();

        return view('perfil.index')->with(compact('usuario'));
    }

    public function update(Request $request)
    {
        $usuario = Auth::user();

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($usuario->id_user, 'id_user'),
            ],
        ]);

        $usuario->fill($dados);
        $usuario->save();

        return redirect()->route('perfil.index')->with('success', 'Perfil atualizado com sucesso.');
    }

    public function updatePassword(Request $request)
    {
        $usuario = Auth::user();

        $dados = $request->validate([
            'senha_atual' => ['required', 'string'],
            'nova_senha' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($dados['senha_atual'], $usuario->password)) {
            return redirect()->route('perfil.index')
                ->withErrors(['senha_atual' => 'Senha atual invalida.'])
                ->withInput();
        }

        $usuario->password = Hash::make($dados['nova_senha']);
        $usuario->save();

        return redirect()->route('perfil.index')->with('success', 'Senha atualizada com sucesso.');
    }
}
