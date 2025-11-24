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

        <!-- Scripts -->
        @vite(['resources/sass/app.scss', 'resources/js/frontend.js'])
        
        <style>
            .guest-wrapper {
                min-height: 100vh;
                min-height: 100svh;
                padding-left: env(safe-area-inset-left);
                padding-right: env(safe-area-inset-right);
            }
        </style>
    </head>
    <body>
        <div class="guest-wrapper d-flex flex-column justify-content-center align-items-center py-4 bg-light">
            <div class="mb-4">
                <a href="/">
                    <h2 class="text-primary">{{ config('app.name', 'Laravel') }}</h2>
                </a>
            </div>

            <div class="card shadow-sm" style="width: 100%; max-width: 450px;">
                <div class="card-body p-4">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
