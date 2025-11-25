@extends('layouts.guest')

@section('title', 'Home Page')

@section('content')
    <div class="container-fluid main-content p-4">
        <div class="top-container">
            <!-- Branding (top area) -->
            <div class="row flex-grow-1">
                <div class="col-12 pt-5 animate-entry">
                    <x-branding/>
                </div>
            </div>
        </div>
        <div class="image-container">
            <!-- Center image (middle area) -->
            <div class="row">
                <div class="col-12 d-flex justify-content-center align-items-center p-0 mt-3 animate-entry">
                    <img class="welcome_img w-100" 
                        src="{{ asset('images/brand/ctree.webp') }}" 
                        alt="">
                </div>
            </div>
        </div>
        <div class="text-container">
            <!-- center text -->
            <h4 class="text-center text-white my-3">
                Register & Head to<br>
                Shoppes at Four Seasons Place<br>
                to Redeem Your Holiday Gift!
            </h4>
            <p class="text-center text-white mb-3">
                Don't miss your chance to experience some holiday magic!</p>
        </div>
        <div class="button-container">
            <!-- Bottom CTA -->
            <div class="row">
                <div class="col-12 text-center">
                    <div class="d-block">
                        <div class="colanimate-entry delay-2 mb-2">
                            <a href="{{route('register')}}" class="custom-btn custom-btn-primary">
                                Sign Up
                            </a>
                        </div>
                        <div class="colanimate-entry delay-2">
                            <a href="{{route('login')}}" class="custom-btn custom-btn-secondary">
                                Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer-welcome mb-4 text-center w-100">
                <x-footer/>
            </div>
        </div>
    </div>
@endsection