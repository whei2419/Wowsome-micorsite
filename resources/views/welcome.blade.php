<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />

    <title>{{ config('app.name', 'Loading ...') }}</title>

    <x-appCdnPackages />
    @vite(['resources/sass/app.scss'])

</head>

<body class="antialiased welcome-page">
    <div class="main-container">
        <h1 class="animate-entry delay-1">Welcome</h1>
        <img onclick="window.location.href='{{ route('dashboard') }}'" class="animate-entry delay-2 logo"
            src="{{ asset('images/brand/nb_ellipse_logo_1_5x.webp') }}" alt="Brand Logo" />
        <a href="{{ route('register') }}" class="animate-entry delay-3 custom-btn custom-btn-primary pulse-slow">
            START
        </a>
    </div>
</body>

</html>
