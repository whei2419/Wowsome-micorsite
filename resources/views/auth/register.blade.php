<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

<<<<<<< Updated upstream
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
=======
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
        <div class="position-relative animate-entry col-12 brand-container">
            @include('components.branding')
        </div>
        <div class="pb-5 animate-entry delay-2 container card-container">
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

                    <div class="mb-3 row">
                        <div class="col-12">
                            <label for="age" class="text-primary">Age:</label>
                            <input id="age" placeholder="Your age" type="number"
                                class="input-text form-control @error('age') is-invalid @enderror" name="age"
                                value="{{ old('age') }}" required min="1" max="120" />
                            @error('age')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <div class="col-12">
                            <label for="gender" class="text-primary">Gender:</label>
                            <select id="gender" name="gender" required
                                class="input-text form-control @error('gender') is-invalid @enderror">
                                <option value="" disabled {{ old('gender') ? '' : 'selected' }}>Select gender
                                </option>
                                <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female
                                </option>
                                <option value="Unspecified" {{ old('gender') === 'Unspecified' ? 'selected' : '' }}>
                                    Unspecified</option>
                            </select>
                            @error('gender')
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
                                <div class="d-block invalid-feedback">{{ $message }}</div>
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
                        <div class="text-center col-12">
                            <button id="submitButton" type="submit" disabled
                                class="mt-4 animate-entry delay-3 custom-btn custom-btn-primary">
                                {{ __('SUBMIT') }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
>>>>>>> Stashed changes
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
