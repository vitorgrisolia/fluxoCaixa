<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::orderBy('nome')->get();

        return view('usuario.index')->with(compact('usuarios'));
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
}
