<x-guest-layout>
    <style>
        * {
            font-family: 'PlusJakartaSans', sans-serif;
        }

        .start-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            padding: 3rem 2rem;
        }

        .start-card {
            width: 100%;
            max-width: 320px;
            text-align: center;
        }

        .start-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 2.5rem;
        }

        .start-logo .logo {
            max-width: 200px;
        }

        .btn-start,
        .btn-start.custom-btn,
        .btn-start.custom-btn-primary {
            display: block;
            width: 100% !important;
            padding: 0.85rem !important;
            border-radius: 50px !important;
            font-weight: 700 !important;
            font-size: 1rem !important;
            letter-spacing: 0.08em !important;
            text-transform: uppercase !important;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.12) !important;
            color: #ffffff !important;
            border: 1.5px solid rgba(255, 255, 255, 0.45) !important;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2) !important;
            transition: all 0.25s ease !important;
            height: auto !important;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            text-decoration: none;
        }

        .btn-start:hover,
        .btn-start.custom-btn:hover {
            background: rgba(255, 255, 255, 0.22) !important;
            border-color: rgba(255, 255, 255, 0.7) !important;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.3) !important;
            transform: translateY(-1px);
            color: #ffffff !important;
        }

        .btn-start:active {
            transform: scale(0.97);
        }
    </style>

    <div class="start-wrapper"
        style="background: url('{{ asset('images/brand/Armani POY_landing_1_5x.webp') }}') center center / cover no-repeat;">

        <div class="start-logo animate-entry">
            <img src="{{ asset('images/brand/logo.webp') }}" alt="Brand Logo" class="logo" />
        </div>

        <div class="start-card animate-entry">
            <a href="{{ url('/redemption') }}" class="btn-start custom-btn custom-btn-primary">
                Start
            </a>
        </div>

    </div>
</x-guest-layout>
