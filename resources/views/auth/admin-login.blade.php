@extends('layouts.guest')
@section('content')

<style>
    .admin-main-content{
        display: flex;
        justify-content: center;   
        align-items:center;
        width: 100vw;
        height: 100svh;
    }
</style>

<body class="main admin-login">
    <div class="admin-main-content">
        <div class="card p-0 shadow-lg rounded admin-card animate-entry">
            <div class="row g-0 h-100">
                <div class="col-lg-6 col-md-0 bg-dark d-lg-flex h-lg-100 h-md-50 py-4">
                    <div class="branding-container w-100 h-100  d-flex justify-content-center align-items-center animate-entry delay-2">
                        @include('components.branding')
                    </div>
                </div>
                <div class="col-12 col-lg-6 d-flex flex-column justify-content-center align-items-center p-lg-5 px-md-5  p-3">
                    <form method="POST" id="loginForm" action="{{ route('authenticateAdmin') }}" class="w-100 animate-entry delay-5">
                        @csrf
                        {{-- <div class="branding-container w-100 d-flex d-lg-none justify-content-center align-items-center mb-4 animate-entry delay-2">
                            @include('components.branding')
                        </div> --}}
                        <h4 class="mb-4 text-center">Welcome to {{ env('APP_NAME') }} Admin Panel</h4>
                        <div class="mb-4 input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input placeholder="Enter your email" type="email" name="email" class="form-control"
                                id="exampleInputEmail1" aria-describedby="emailHelp" /> 
                        </div>
                        <div class="mb-3 input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input placeholder="Enter your password" type="password" name="password"
                                class="form-control" id="exampleInputPassword1" />
                            <!-- <span id="password-toggle"><i class="fas fa-eye"></i></span> -->
                        </div>

                        <div class="checkbox-container mb-5">
                            <input type="checkbox" id="remember" name="remember" />
                            <label for="remember">Remember me</label>
                        </div>
                        <button type="submit" class="btn login-button w-100 shadow-sm">Login</button>
                        <p class="small-text text-center mt-4">Powered by WOWSOME®️ 2025</p>
                    </form>
                </div>
            </div>
        </div>
        <x-script-packages />
    </div>
</body>

@endsection
