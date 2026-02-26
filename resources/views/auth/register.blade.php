<x-guest-layout>
    <div class="register-main main-content with-scroll">
        <div class="justify-content-center w-100">
            <div class="col-12 animate-entry position-relative brand-container">
                @include('components.branding')
            </div>
            <div class="container card-container">
                <h1>Register</h1>
                <form id="form" method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="fields-container">
                        <div class="mb-3 row">
                            <div class="col-12">
                                <label for="fname" class="text-primary">Full Name: <span
                                        class="text-danger">*</span></label>
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
                                <label for="email" class="text-primary">E-mail: <span
                                        class="text-danger">*</span></label>

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

                        @error('country')
                            <div class="text-danger text-center mb-2">{!! $message !!}</div>
                        @enderror
                    </div>


                    <div class="button-container">
                        <div class="mb-0 row">
                            <div class="col-12 text-center">
                                <button id="submitButton" type="submit"
                                    class="custom-btn custom-btn-primary animate-entry delay-3 mt-4">
                                    {{ __('SUBMIT') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
