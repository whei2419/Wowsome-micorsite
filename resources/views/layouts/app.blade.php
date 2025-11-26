<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=nunito:400,600,700&display=swap" rel="stylesheet" />
        <x-app-cdn-packages />
        <!-- Scripts -->
        @vite(['resources/sass/app.scss', 'resources/js/frontend.js'])
        
        <style>
            .app-wrapper {
                min-height: 100vh;
                min-height: 100svh;
            }
        </style>
    </head>
    <body class="{{ request()->segment(2) == 1 ? 'weekday-background' : (request()->segment(2) == 2 ? 'weekend-background' : 'main-background') }}">
        <div class="app-wrapper">
            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow-sm">
                    <div class="container py-4">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                 @yield('content')
            </main>
        </div>
        <x-script-packages />

    <!-- Stack for page-specific JavaScript -->
    @stack('scripts')
    </body>
</html>
