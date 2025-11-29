@extends('layouts.guest')

@section('title', 'Register Page')

@section('content')
<style>
    .iti {
            width: 100%;
        }
    span.iti__country-name {
        color: #000000 !important;
    }


    </style>
    <div class="register-main main-content with-scroll">
        <div class="justify-content-center w-100">
            <div class="col-12 animate-entry mb-4">
                @include('components.branding')
            </div>
            <h2 class="mx-4 text-center animate-entry text-white text-bold heading">Registration</h2>
            @if(isset($referralCode) && !empty($referralCode))
                <div class="text-center text-white mb-2">
                    <small>Referred by: {{ $referralCode }}</small>
                </div>
            @endif
            <div class=" mt-4 w-100  animate-entry delay-3 p-3">
                <div class="py-3 register-form-parent">
                    <form id="form" method="POST" action="{{ route('register') }}">
                        @csrf
                        <input type="hidden" name="dialCode" id="dialCode" ></input>
                        <input type="hidden" name="countryIso" id="countryIso">
                        @if(isset($referralCode) && !empty($referralCode))
                            <input type="hidden" name="referral" value="{{ $referralCode }}">
                        @endif
                        <div class="mb-3 row">
                            <div class="col-12">
                                <div class="col-12 tree-container">
                                    <img class="tree-img" src="{{ asset('images/brand/tree.webp') }}" alt="">
                                </div>
                                <label for="fname" class="text-main text-white">Full Name <span class="text-danger">*</span></label>
                                <div class="row snow-container">
                                    <div class="col-6">
                                        <img class="snow_2" src="{{ asset('images/brand/snow_2.webp') }}" alt="">
                                    </div>
                                    <div class="col-6">
                                        <img class="snow_1" src="{{ asset('images/brand/snow_1.webp') }}" alt="">
                                    </div>
                                </div>
                                <div class="gradient-input">
                                    <input id="fname" placeholder="Please enter your full name" type="text"
                                    class="input-text form-control @error('fname') is-invalid @enderror" name="fname"
                                    value="{{ old('fname') }}" required autocomplete="fname" autofocus />
                                    @error('fname')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>


                        <div class="mb-3 row">
                            <div class="col-12 input-group w-100">
                                <label for="number" class="text-main text-white">Contact Number <span class="text-danger">*</span></label>
                                <div class="row snow-container">
                                    <div class="col-6">
                                        <img class="snow_2" src="{{ asset('images/brand/snow_2.webp') }}" alt="">
                                    </div>
                                    <div class="col-6">
                                        <img class="snow_1" src="{{ asset('images/brand/snow_1.webp') }}" alt="">
                                    </div>
                                </div>
                                <div class="gradient-input">
                                    <input id="number" type="phone"
                                    class="input-text form-control w-100 @error('number') is-invalid @enderror"
                                    name="number" value="{{ old('number') }}" required autocomplete="number"
                                    autofocus />
                                </div>
                                @error('number')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                                <span id="valid-msg" class="d-none text-danger"></span>
                                <span id="error-msg" class="d-none text-danger"></span>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <div class="col-12">
                                <label for="email" class="text-dark text-white">Email <span class="text-danger">*</span></label>
                                <div class="row snow-container">
                                    <div class="col-6">
                                        <img class="snow_2" src="{{ asset('images/brand/snow_2.webp') }}" alt="">
                                    </div>
                                    <div class="col-6">
                                        <img class="snow_1" src="{{ asset('images/brand/snow_1.webp') }}" alt="">
                                    </div>
                                </div>
                                <div class="gradient-input">
                                    <input id="email" placeholder="Please enter your email" type="email"
                                        class="input-text form-control @error('email') is-invalid @enderror" name="email"
                                        value="{{ old('email') }}" required autocomplete="email" />
                                </div>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>


                         @error('country')
                            <div class="text-danger text-center mb-2">{!! $message !!}</div>
                        @enderror
                        <div class="mt-4 mb-2 row">
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="privacy_policy" value="1" id="privacyPolicy" required="">
                                    <label class="form-check-label text-light sub-heading" for="privacyPolicy">
                                       I agree that the collection and processing of my personal data will be in compliance with the Shoppes at Four Seasons Place’s <a href="{{ asset('docs/terms.pdf') }}" target="_blank" class="text-white">Terms and Conditions</a> .
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-0 row">
                            <div class="col-12 text-center mb-2">
                                <button id="submitButton" type="submit"
                                    class="w-100 custom-btn custom-btn-primary animate-entry delay-3">
                                    {{ __('Submit') }}
                                </button>
                            </div>
                            <div class="col-12 text-center">
                                <button id="" type="button" onclick="window.location='{{ route('login') }}'"
                                    class="w-100 custom-btn custom-btn-secondary animate-entry delay-3">
                                    {{ __('Login') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <x-footer/>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/intlTelInput.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.querySelector("#form");
        const input = document.querySelector("#number");
        const errorMsg = document.querySelector("#error-msg");
        const validMsg = document.querySelector("#valid-msg");
        const errorMap = [
            "Invalid number",
            "Invalid country code",
            "Too short",
            "Too long",
            "Invalid number",
        ];
        const submitButton = document.querySelector("#submitButton");
        const iti = window.intlTelInput(input, {
            hiddenInput: "country",
            initialCountry: "my",
            preferredCountries: ["my"],
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

        // Prevent form submission if not Malaysian number
        form.addEventListener("submit", function (e) {
            const countryData = iti.getSelectedCountryData();
            if (!iti.isValidNumber() || countryData.iso2 !== 'my') {
                let msg = "Please enter a valid Malaysian phone number";
                if (countryData.iso2 !== 'my') {
                    msg = "Only Malaysian phone numbers are allowed.";
                }
                showError(msg);
                 e.preventDefault();
                submitButton.disabled = true;
            }
        });
    });
</script>

@endsection
