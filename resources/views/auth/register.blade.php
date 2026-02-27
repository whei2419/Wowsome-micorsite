<x-guest-layout>
    <div class="register-main">
        <div class="col-12 animate-entry position-relative brand-container">
            @include('components.branding')
        </div>
        <div class="container card-container">
            <h1>Registration</h1>
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

                <div class="mb-3 row">
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input @error('agree') is-invalid @enderror" type="checkbox"
                                name="agree" id="agree" required>
                            <label class="form-check-label" for="agree">
                                I agree to the <a href="/terms" target="_blank">Terms &amp; Conditions</a> and <a
                                    href="https://www.newbalance.com.my/privacy-policy.html" target="_blank">Privacy
                                    Policy</a>.
                            </label>
                            @error('ac c gree')
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
                            <label class="form-check-label" for="marketing">Subscribe to New Balance
                                E-Newsletter</label>
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
