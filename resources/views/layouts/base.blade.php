<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'GrisoliaSistema') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Instrument+Serif:ital@0;1&family=Space+Grotesk:wght@400;500;600;700&display=swap">

    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- JavaScript Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-u1OknCvxWvY5kfmNBILK2hRnQC3Pr17a+RTT6rIHI7NnikvbZlHgTPOOmMi466C8" crossorigin="anonymous" defer>
    </script>
</head>
<body class="app-body">
    <div class="app-bg">
        <div class="app-blob is-left"></div>
        <div class="app-blob is-right"></div>
    </div>
    <div class="container app-shell">
        {{-- MENU --}}
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="/">
                    <img src="{{ asset('img/logo-grisolia.jpg') }}" alt="Grisolia Sistema" class="brand-logo me-2">
                    Caixa
                    - {{ Auth::user()->nome }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavFluxo"
                    aria-controls="navbarNavFluxo" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                @php
                    $isAdmin = Auth::user()->tipo_usuario === 'admin';
                @endphp
                <div class="collapse navbar-collapse ml-3" id="navbarNavFluxo">
                    <div class="navbar-nav">
                        @if($isAdmin)
                            <a class="nav-link" href="{{ route('home.index') }}">
                                <i class="bi bi-house-door-fill"></i>
                                Home
                            </a>
                            <a class="nav-link" href="{{ route('lancamento.index') }}">
                                <i class="bi bi-piggy-bank-fill"></i>
                                Lancamentos
                            </a>
                            <a class="nav-link" href="{{ route('centro.index') }}">
                                <i class="bi bi-basket-fill"></i>
                                Centro de Custo
                            </a>
                            <a class="nav-link" href="{{ route('tipo.index') }}">
                                <i class="bi bi-arrow-down-up"></i>
                                Tipos
                            </a>
                            <a class="nav-link" href="{{ route('usuario.index') }}">
                                <i class="bi bi-people-fill"></i>
                                Usuarios
                            </a>
                            <a class="nav-link" href="{{ route('produto.index') }}">
                                <i class="bi bi-box-seam"></i>
                                Produtos
                            </a>
                            <a class="nav-link" href="{{ route('estoque.index') }}">
                                <i class="bi bi-clipboard2-data"></i>
                                Controle de Estoque
                            </a>
                            <a class="nav-link" href="{{ route('controle-financeiro.index') }}">
                                <i class="bi bi-bank"></i>
                                Controle Financeiro
                            </a>
                        @else
                            <a class="nav-link" href="{{ route('leitor.produtos') }}">
                                <i class="bi bi-upc-scan"></i>
                                Leitor de Produtos
                            </a>
                            <a class="nav-link" href="{{ route('leitor.finalizar') }}">
                                <i class="bi bi-cart-check-fill"></i>
                                Finalizar compra
                            </a>
                        @endif
                        <a class="nav-link" href="{{ route('logout') }}">
                            <i class="bi bi-box-arrow-right"></i>
                            Sair
                        </a>
                    </div>
                </div>
            </div>
        </nav>
        {{-- /MENU --}}

        {{-- CONTEUDO --}}
        <main class="row app-content mb-4">
            @yield('conteudo')
        </main>
        {{-- CONTEUDO --}}

        @include('layouts.footer')
    </div>
    @yield('script')
</body>
</html>
