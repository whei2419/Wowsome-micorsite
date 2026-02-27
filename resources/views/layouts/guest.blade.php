<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <!-- CSRF Token for security -->
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- Page Title -->
    <title>{{ config('app.name', 'Loading ...') }}</title>

    <!-- Common CDN packages (CSS, etc.) -->
    <x-appCdnPackages />

    <!-- Vite assets (compiled CSS and JS) -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body class="main-background {{ str_replace('.', '-', optional(request()->route())->getName() ?: 'no-route') }}">
    <!-- Main content area where page-specific content will be injected -->
    <main>
        {{ $slot }}
    </main>

    <!-- Common JavaScript packages -->
    <x-scriptPackages />

    <!-- Stack for page-specific JavaScript -->
    @stack('scripts')
</body>

</html>
