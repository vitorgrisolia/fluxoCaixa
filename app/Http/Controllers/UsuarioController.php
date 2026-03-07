<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::orderBy('nome')->get();
        $usuarioEdicao = null;

        return view('usuario.index')->with(compact('usuarios', 'usuarioEdicao'));
    }

    public function edit(int $id)
    {
        $usuarios = User::orderBy('nome')->get();
        $usuarioEdicao = User::findOrFail($id);

        return view('usuario.index')->with(compact('usuarios', 'usuarioEdicao'));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'tipo_usuario' => ['required', 'in:admin,funcionario'],
        ]);

        User::create([
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'password' => Hash::make($dados['password']),
            'tipo_usuario' => $dados['tipo_usuario'],
        ]);

        return redirect()->route('usuario.index')->with('success', 'Usuario cadastrado com sucesso.');
    }

    public function update(Request $request, int $id)
    {
        $usuario = User::findOrFail($id);

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($usuario->id_user, 'id_user'),
            ],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'tipo_usuario' => ['required', 'in:admin,funcionario'],
        ]);

        $usuario->nome = $dados['nome'];
        $usuario->email = $dados['email'];
        $usuario->tipo_usuario = $dados['tipo_usuario'];

        if (!empty($dados['password'])) {
            $usuario->password = Hash::make($dados['password']);
        }

        $usuario->save();

        return redirect()->route('usuario.edit', ['id' => $usuario->id_user])
            ->with('success', 'Usuario atualizado com sucesso.');
    }
}
