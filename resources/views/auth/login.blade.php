<x-guest-layout>
    <style>
        * {
            font-family: 'PlusJakartaSans', sans-serif;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .login-card {
            background: rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 420px;
        }

        .login-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .login-logo .logo {
            max-width: 200px;
        }

        .login-title {
            font-size: 1.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 0.25rem;
            color: #ffffff;
        }

        .login-subtitle {
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 0.4rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.12);
            border: 1.5px solid rgba(255, 255, 255, 0.22);
            border-radius: 10px;
            padding: 0.65rem 1rem;
            font-size: 0.95rem;
            color: #ffffff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.35);
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.18);
            border-color: rgba(255, 255, 255, 0.55);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.08);
            outline: none;
            color: #ffffff;
        }

        .form-control.is-invalid {
            border-color: #f87171;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .toggle-pw {
            position: absolute;
            right: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: rgba(255, 255, 255, 0.5);
            background: none;
            border: none;
            padding: 0;
            line-height: 1;
        }

        .password-wrapper .toggle-pw:hover {
            color: #ffffff;
        }

        .btn-login,
        .btn-login.custom-btn,
        .btn-login.custom-btn-primary {
            display: block;
            width: 100% !important;
            padding: 0.8rem !important;
            border-radius: 10px !important;
            font-weight: 700 !important;
            font-size: 1rem !important;
            letter-spacing: 0.06em !important;
            text-transform: uppercase !important;
            cursor: pointer;
            margin-top: 0.5rem;
            background: rgba(255, 255, 255, 0.12) !important;
            color: #ffffff !important;
            border: 1.5px solid rgba(255, 255, 255, 0.45) !important;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2) !important;
            transition: all 0.25s ease !important;
            height: auto !important;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }

        .btn-login:hover,
        .btn-login.custom-btn:hover,
        .btn-login.custom-btn-primary:hover {
            background: rgba(255, 255, 255, 0.22) !important;
            border-color: rgba(255, 255, 255, 0.7) !important;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.3) !important;
            transform: translateY(-1px);
        }

        .btn-login:active {
            transform: scale(0.97) translateY(0);
        }

        .powered-text {
            text-align: center;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.3);
            margin-top: 1.5rem;
        }
    </style>

    <div class="login-wrapper"
        style="background: url('{{ asset('images/brand/Armani POY_second_1_5x.webp') }}') center center / cover no-repeat;">
        <div class="login-card animate-entry">

            <div class="login-logo">
                <img src="{{ asset('images/brand/logo.webp') }}" alt="Brand Logo" class="logo" />
            </div>

            <x-auth-session-status class="mb-3" :status="session('status')" />

            @if (session('error'))
                <div class="alert alert-danger py-2 px-3 mb-3" style="border-radius:8px; font-size:0.875rem;">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                        name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                        placeholder="you@example.com" />
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-wrapper">
                        <input id="password" type="password"
                            class="form-control @error('password') is-invalid @enderror" name="password" required
                            autocomplete="current-password" placeholder="••••••••" />
                        <button type="button" class="toggle-pw" onclick="togglePassword()"
                            aria-label="Toggle password">
                            <i class="fas fa-eye" id="pw-icon"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-login custom-btn custom-btn-primary">
                    Sign In
                </button>
            </form>

            <p class="powered-text">Powered by WOWSOME®️ 2026</p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('pw-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</x-guest-layout>
