@extends('layouts.error')

@section('conteudo')
<div class="error-card">
    <div class="error-code">404</div>
    <h1 class="error-title">Pagina nao encontrada</h1>
    <p class="error-text">
        A pagina que voce tentou acessar nao existe ou foi movida.
    </p>
    <div class="d-flex flex-wrap gap-2 mt-3">
        <a href="{{ url('/') }}" class="btn btn-primary">
            Ir para o inicio
        </a>
    </div>
</div>
@endsection
