<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'FluxoCaixa') }}</title>

    <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Instrument+Serif:ital@0;1&family=Space+Grotesk:wght@400;500;600;700&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-body">
    <div class="app-bg">
        <div class="app-blob is-left"></div>
        <div class="app-blob is-right"></div>
    </div>
    <div class="container app-shell">
        <main class="error-shell">
            @yield('conteudo')
        </main>
    </div>
</body>
</html>
