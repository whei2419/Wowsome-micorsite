<x-guest-layout>
    <div class="register-main">
        <style>
            .register-main .form-check {
                position: relative;
            }

            .register-main .form-check-input {
                position: absolute;
                opacity: 0;
                width: 1.25rem;
                height: 1.25rem;
                margin: 0;
                left: 0;
                top: 50%;
                transform: translateY(-50%);
                z-index: 2;
            }

            .register-main .form-check-label {
                padding-left: 1.75rem;
                position: relative;
                display: inline-block;
                cursor: pointer;
            }

            .register-main .form-check-label .custom-checkbox {
                position: absolute;
                left: 0;
                top: 50%;
                transform: translateY(-50%);
                width: 1.25rem;
                height: 1.25rem;
                border: 2px solid #cbd5e1;
                border-radius: 0.25rem;
                background: #ffffff;
                display: inline-block;
            }

            .register-main .form-check-input:focus+.form-check-label .custom-checkbox {
                box-shadow: 0 0 0 3px rgba(14, 165, 164, 0.12);
            }

            .register-main .form-check-input:checked+.form-check-label .custom-checkbox {
                background: #0ea5a4;
                border-color: #0ea5a4;
            }

            .register-main .form-check-input:checked+.form-check-label .custom-checkbox::after {
                content: "";
                position: absolute;
                left: 6px;
                top: 2px;
                width: 5px;
                height: 10px;
                border: solid #ffffff;
                border-width: 0 2px 2px 0;
                transform: rotate(45deg);
            }
        </style>
        <div class="col-12 animate-entry position-relative brand-container">
            @include('components.branding')
        </div>
        <div class="container card-container animate-entry delay-2">
            <h1 class="animate-entry delay-1">Registration</h1>
            <form id="form" method="POST" action="{{ route('register') }}">
                @csrf
                <div class="fields-container">
                    <div class="mb-3 row">
                        <div class="col-12">
                            <label for="fname" class="text-primary">Full Name:</label>
                            <input id="fname" placeholder="Your name" type="text"
                                class="input-text form-control @error('fname') is-invalid @enderror" name="fname"
                                value="{{ old('fname') }}" required autocomplete="fname" autofocus />
                            @error('fname')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <div class="col-12">
                            <label for="email" class="text-primary">Email Address:</label>

                            <input id="email" placeholder="Email Address" type="email"
                                class="input-text form-control @error('email') is-invalid @enderror" name="email"
                                value="{{ old('email') }}" required autocomplete="email" />

                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-1 row">
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input @error('agree') is-invalid @enderror" type="checkbox"
                                name="agree" id="agree" required>
                            <label class="form-check-label" for="agree">
                                <span class="custom-checkbox" aria-hidden="true"></span>
                                I agree to the <a href="https://www.newbalance.com.my/terms.html" target="_blank">Terms
                                    &amp; Conditions</a> and <a href="https://www.newbalance.com.my/privacy-policy.html"
                                    target="_blank">Privacy
                                    Policy</a>.
                            </label>
                            @error('agree')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3 row">
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="marketing" id="marketing"
                                value="1" {{ old('marketing') ? 'checked' : '' }}>
                            <label class="form-check-label" for="marketing">
                                <span class="custom-checkbox" aria-hidden="true"></span>
                                Subscribe to New Balance E-Newsletter
                            </label>
                        </div>
                    </div>
                </div>
                <div class="button-container">
                    <div class="mb-0 row">
                        <div class="col-12 text-center">
                            <button id="submitButton" type="submit" disabled
                                class="custom-btn custom-btn-primary animate-entry delay-3 mt-4">
                                {{ __('SUBMIT') }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var agree = document.getElementById('agree');
                var submit = document.getElementById('submitButton');
                if (!agree || !submit) return;

                function toggle() {
                    submit.disabled = !agree.checked;
                }
                // initialize and bind
                toggle();
                agree.addEventListener('change', toggle);
            });
        </script>
    @endpush
</x-guest-layout>
