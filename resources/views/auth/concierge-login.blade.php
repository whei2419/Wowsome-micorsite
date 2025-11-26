@extends('layouts.app')
@section('content')
<style>
        .login-page h4,
        .login-page label,
        .login-page input,
        .login-page p,
        .login-page a,
        .login-page span {
            font-family: 'GothamBold' !important;
        }
    </style>
    <div class="login-page vh-100">
        <div class="main-content main-background with-scroll">
            <div class="col-12 animate-entry mb-4">
                @include('components.branding')
            </div>
                <h2 class="mx-4 text-center sub-heading-text animate-entry">LOGIN</h2>
            <div class="col-12 animate-entry delay-2 bg-white p-3 mt-4 card-parent" style="margin-bottom:20vh;">
                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />
                <form method="POST" action="{{ route('authenticateConcierge') }}" >
                    @csrf
                        <div class="row mb-3">
                            <div class="col-12 input-group w-100">
                                <label for="email" class="text-main">Username <span class="text-danger">*</span></label>
                                <input id="email" type="email"
                                    class="input-text form-control w-100 mb-2 @error('email') is-invalid @enderror"
                                    name="email" value="{{ old('email') }}" required autocomplete="number"
                                    autofocus />
                                @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                                @if(session('error'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ session('error') }}</strong>
                                </span>
                                @endif
                            </div>

                            <div class="col-12 input-group w-100">
                                <label for="password" class="text-main">Password <span class="text-danger">*</span></label>
                                <input id="password" type="password"
                                    class="input-text form-control w-100 @error('password') is-invalid @enderror"
                                    name="password" value="" required autocomplete="password"
                                    autofocus />
                                @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                                @if(session('error'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ session('error') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>

                    <!-- Password -->
       

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />

                    <div class="d-flex justify-center">
                        <x-primary-button class="custom-btn custom-btn-primary" style="width:95%;margin:auto;">
                            {{ __('Submit') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/intlTelInput.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.querySelector("#form");
        const input = document.querySelector("#number");
        const errorMsg = document.querySelector("#error-msg");
        const validMsg = document.querySelector("#valid-msg");
        const dialInput = document.querySelector("#dialCode");
        const isoInput = document.querySelector("#countryIso");
        const errorMap = [
            "Invalid number",
            "Invalid country code",
            "Too short",
            "Too long",
            "Invalid number",
        ];
        const submitButton = document.querySelector("#submitButton");
        const iti = window.intlTelInput(input, {
            initialCountry: "my",
            preferredCountries: ["my"],
            hiddenInput: "country",
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/utils.js"
        });

        const reset = () => {
            input.classList.remove("error");
            errorMsg.innerHTML = "";
            errorMsg.classList.add("d-none");
            validMsg.classList.add("d-none");
        };

        const showError = (msg) => {
            input.classList.add("error");
            errorMsg.innerHTML = msg;
            errorMsg.classList.remove("d-none");
        };

        input.addEventListener("keyup", function () {
            reset();
            if (!input.value.trim()) {
                showError("Required");
                submitButton.disabled = true;
            } else if (iti.isValidNumber()) {
                validMsg.classList.remove("d-none");
                submitButton.disabled = false;
            } else {
                const errorCode = iti.getValidationError();
                const msg = errorMap[errorCode] || "Invalid number";
                showError(msg);
                submitButton.disabled = true;
            }
        });

        // Function to update dial code in the div
            function updateCountryData() {
                const countryData = iti.getSelectedCountryData();
                dialInput.value = "+" + countryData.dialCode;  // e.g., +1
                isoInput.value = countryData.iso2;             // e.g., us, my, ca
                console.log("Dial code:", dialInput.value, "ISO:", isoInput.value);
                // submitButton.disabled = false;
            }

            // Set initial default dial code on page load
            updateCountryData();

            // Update dial code whenever country changes
            input.addEventListener("countrychange", updateCountryData);

            if (!input) return;
            
            input.addEventListener("keypress", function (e) {
            const char = String.fromCharCode(e.which);
            if (!/[0-9+]/.test(char)) {
                e.preventDefault();
            }
        });

        // Prevent form submission if not Malaysian number
        form.addEventListener("submit", function (e) {
            const countryData = iti.getSelectedCountryData();
            console.log(countryData);
            const number = input.value.trim();

            // Check if number is valid for the selected country
            if (!iti.isValidNumber()) {
                const msg = `Please enter a valid phone number for ${countryData.name}`;
                showError(msg);
                e.preventDefault();
                submitButton.disabled = true;
            } else {
                submitButton.disabled = false; // enable submit if valid
            }
        });
    });
</script>
@endsection