@extends('layouts.app')
@section('content')
<style>
    .referral-card {
        border-radius: 12px;
        padding: 15px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid transparent;
    }

    .referral-card.active {
        border-color: #28a745; /* highlight color */
        background-color: #d4edda;
    }

    .referral-card.inactive {
        filter: grayscale(100%);
        opacity: 0.5;
        cursor: not-allowed;
    }

    .referral-card.inactive a {
        pointer-events: none;
    }

    .tier {
        padding: 10px;
        border-radius: 8px;
        color: white;
    }

    .tier a
    {
        text-decoration: none;
    }

    .tier1 {
        background:radial-gradient(56.54% 56.54% at 50% 50%, #349C65 15.72%, #1F6C45 100%);
    }

    .tier2 {
        background: radial-gradient(55.22% 118.8% at 50.25% -1.11%, #ED3241 0%, #D62B39 17.35%, #9C1924 53.71%, #56030B 93.3%, #5A040C 99.61%);
    }

    .tier span {
        font-weight: 500;
        font-size: 1rem;
        color:#FFEAB8
    }

    .tap-copy-btn {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 15px;
        border-radius: 8px;
        border: 1px solid #ccc;
        cursor: pointer;
        margin-bottom: 10px;
    }

    .tap-copy-btn:hover {
        background-color: #f8f9fa;
    }

    img.tiers-ico {
        width: auto;
        height: 45px;
        width: 40%;
        object-fit: contain;
        margin: auto;
    }

    </style>

    <div class="with-scroll py-4 map-page" data-id="{{ request()->segment(2) }}">
        <div class="animate-entry">
            @include('components.branding')
        </div>
            @php
                $type = request()->segment(2) == 1
                    ? 'weekday'
                    : (request()->segment(2) == 2 ? 'weekend' : 'referral');

                $image = "images/brand/{$type}_hero.webp";

                $alt   = request()->segment(2) == 1
                    ? 'Weekday Img'
                    : (request()->segment(2) == 2 ? 'Weekend Img' : 'Referral Img');
            @endphp
        <div class="hero mt-4 animate-entry">
            <img class="w-100" src="{{ asset($image) }}" alt="{{ $alt }}">
        </div>
        <div class="main-content">
        <!-- login Modal -->
                <div class="mb-2 ">
                    <!-- Center image (middle area) -->
                    <div class="row">
                        <div class="col-12  text-center text-white my-4 p-0 animate-entry">
                            <h2 class="text-bold main-heading">Refer a friend and get a gift</h2>
                            <p class="text-light sub-heading">Share your code with your friends , You will <br>receive an exclusive gift</p>
                        </div>
                    </div>

                    <!-- Referral Code -->
                       <div class="row animate-entry mb-2">
                            <div class="col-7 pe-1">
                                <a href="#" id="copyCodeBtn" data-code="{{ $user->referral_code }}">
                                    <div class="card p-2 text-center">
                                        {{ $user->referral_code }} <br>
                                        <small> Tap to copy code</small>
                                    </div>
                                </a>
                            </div>
                            <div class="col-5 ps-1">
                                <div class="card p-2 text-center">
                                    <span>{{ $totalReferrals }}</span>
                                    <small>Total Referral</small>
                                </div>
                            </div>
                       </div>

                        <!-- Copy Link Button -->
                        <div class="row animate-entry mb-3">
                            <div class="col-12">
                                <button id="copyLinkBtn" class="custom-btn custom-btn-secondary w-100" data-url="{{ $referralUrl }}">
                                    📋 Copy Registration Link
                                </button>
                            </div>
                        </div>

                    <!-- Tiers -->
                     <div class="row mb-3 animate-entry delay-2">
                        <div class="col-6 pe-1">
                            <div class="gradient-card">
                                <div class="tier tier1 referral-card {{ $completedReferrals >= 1 ? 'active' : 'inactive' }}" id="tier1">
                                    <a href="{{ route('reward.index', ['reward' => 3]) }}">
                                        <img class="tiers-ico mb-2" src="{{ asset('images/brand/tier1.webp');}}" alt="">
                                        <div><span>Tier 1</span></div>
                                        <div><span>{{ min($completedReferrals, 1) }}/1</span></div>
                                    </a>
                                </div>
                            </div>
                        </div>
                            <div class="col-6 ps-1">
                                <div class="gradient-card">
                                    <div class="tier tier2 referral-card {{ $completedReferrals >= 5 ? 'active' : 'inactive' }}" id="tier2">
                                        <a href="{{ route('reward.index', ['reward' => 4]) }}">
                                            <img class="tiers-ico mb-2" src="{{ asset('images/brand/tier2.webp');}}" alt="">
                                            <div><span>Tier 2</span></div>
                                            <div><span>{{ min($completedReferrals, 5) }}/5</span></div>
                                        </a>
                                    </div>
                                </div>
                        </div>
                     </div>

                    <!-- Bottom CTA -->
                    <div class="row animate-entry">
                        <div class="col-12 text-center">
                            <div class="d-block">
                                <div class="col mb-3 animate-entry delay-2">
                                    <button type="button" class="custom-btn custom-btn-primary"
                                        onclick="window.location.href='{{ route('dashboard') }}'">
                                        Back
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <x-footer/>
        </div>
    </div>
    <script>
    // Copy referral code
    document.getElementById('copyCodeBtn').addEventListener('click', function(e) {
        e.preventDefault();
        const code = this.getAttribute('data-code');
        navigator.clipboard.writeText(code).then(function() {
            alert('Referral code copied: ' + code);
        }).catch(function(err) {
            console.error('Could not copy text: ', err);
        });
    });

    // Copy registration link
    document.getElementById('copyLinkBtn').addEventListener('click', function() {
        const url = this.getAttribute('data-url');
        navigator.clipboard.writeText(url).then(function() {
            alert('Registration link copied to clipboard!');
        }).catch(function(err) {
            console.error('Could not copy text: ', err);
        });
    });

    document.getElementById('openModalBtn')?.addEventListener('click', function () {
        // Initialize modal
        var modalEl = document.getElementById('qrModal');
        var myModal = new bootstrap.Modal(modalEl);
        myModal.show();

        // Close button in footer
        modalEl.querySelector('.close').addEventListener('click', function () {
            myModal.hide(); // hides modal
            removeBackdrop();
        });

        // Also remove backdrop if somehow stuck
        modalEl.addEventListener('hidden.bs.modal', function () {
            removeBackdrop();
        });

        function removeBackdrop() {
            document.querySelectorAll('.modal-backdrop').forEach(function (el) {
                el.remove();
            });
        }
    });
    </script>
@endsection
