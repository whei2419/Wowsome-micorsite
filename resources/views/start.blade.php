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
            justify-content: center;
            padding: 2rem;
            gap: 2rem;
        }

        .start-card {
            width: 100%;
            max-width: 920px;
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

        .action-grid {
            display: flex;
            gap: 20px;
            justify-content: center;
            align-items: center;
        }

        .action-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: min(46vw, 300px);
            height: min(46vw, 300px);
            max-width: 360px;
            max-height: 360px;
            border-radius: 14px;
            text-decoration: none;
            color: inherit;
            background: rgba(255, 255, 255, 0.06);
            border: 1.5px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.35);
            transition: transform .18s ease, background .18s ease;
        }

        .action-card .icon {
            font-size: clamp(28px, 6vw, 48px);
            margin-bottom: 12px;
            color: #fff;
        }

        .action-card .label {
            font-weight: 800;
            font-size: clamp(1rem, 2.6vw, 1.2rem);
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .action-card:hover {
            transform: translateY(-6px);
            background: rgba(255, 255, 255, 0.09);
        }
    </style>

    <div class="start-wrapper"
        style="background: url('{{ asset('images/brand/Armani POY_landing_1_5x.webp') }}') center center / cover no-repeat;">

        <div class="start-logo animate-entry">
            <img src="{{ asset('images/brand/logo.webp') }}" alt="Brand Logo" class="logo" />
        </div>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
            integrity="sha512-pVnY6fKqzY1Xr1KXkqf0QK6K6Q3p0Z8Jt1g3Kq3s5Y6v3x7m2QYbG6q3V1y9KqzY1Xr1KXkqf0QK6K6Q3p0Z8=="
            crossorigin="anonymous" referrerpolicy="no-referrer" />

        <div class="start-card animate-entry">
            <div class="action-grid">
                <a href="{{ route('player', ['mode' => 'photo']) }}" class="action-card custom-btn custom-btn-primary">
                    <i class="fa-solid fa-camera icon" aria-hidden="true"></i>
                    <div class="label">Picture</div>
                </a>

                <a href="{{ route('player', ['mode' => 'video']) }}" class="action-card custom-btn">
                    <i class="fa-solid fa-video icon" aria-hidden="true"></i>
                    <div class="label">Video</div>
                </a>
            </div>
        </div>

    </div>
</x-guest-layout>
