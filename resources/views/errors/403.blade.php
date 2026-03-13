@extends('layouts.error')

@section('conteudo')
<div class="error-card">
    <div class="error-code">403</div>
    <h1 class="error-title">Acesso negado</h1>
    <p class="error-text">
        Voce nao tem permissao para acessar esta pagina.
    </p>
    <div class="d-flex flex-wrap gap-2 mt-3">
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
            Voltar
        </a>
        <a href="{{ url('/') }}" class="btn btn-primary">
            Ir para o inicio
        </a>
    </div>
</div>
@endsection
