<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CaixaGrisolia') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Instrument+Serif:ital@0;1&family=Space+Grotesk:wght@400;500;600;700&display=swap">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="app-body">
        <div class="app-bg">
            <div class="app-blob is-left"></div>
            <div class="app-blob is-right"></div>
        </div>
        <div class="app-shell">
            @include('layouts.navigation')

            <header class="container mt-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        {{ $header }}
                    </div>
                </div>
            </header>

            <main class="container app-content">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
