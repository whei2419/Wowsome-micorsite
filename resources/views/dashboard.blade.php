@extends('layouts.app')

@section('content')
    <div class="p-4 map-page main-content">
        <x-dropdown-menu />
        <div class="justify-content-center w-100">
            <div class="top-container">
                <!-- Branding (top area) -->
                <div class="row flex-grow-1">
                    <div class="col-12 pt-5 animate-entry">
                        <x-branding/>
                    </div>
                </div>
            </div>
            <!-- login Modal -->
            <h2 class="mx-4 text-center animate-entry text-white text-bold heading pt-5">Rewards</h2>
            <!-- Modal -->
            <div class="modal fade custom-modal" id="notAllowedModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered w-75 m-auto">
                    <div class="modal-content card">
                        <div class="modal-body">
                            <div class="text-center content">
                                <div class="text-content mt-4 mb-">
                                    <p class="message text-dark">
                                        Referral reward not unlocked yet! <br>Invite a friend and have them claim Gift 1 & Gift 2 to unlock this reward!
                                    </p>
                                </div>
                                <button type="button" class="w-50 custom-btn custom-btn-primary" data-bs-dismiss="modal"
                                    aria-label="Close">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="station-selection-container">
                    <div class="col-12 tree-container" style="margin-bottom:-10px;">
                        <img class="tree-img" src="{{ asset('images/brand/tree.webp') }}" alt="">
                    </div>
                <div class="row snow-container">
                    <div class="col-6">
                        <img class="snow_2" src="{{ asset('images/brand/snow_2.webp') }}" alt="">
                    </div>
                    <div class="col-6">
                        <img class="snow_1" src="{{ asset('images/brand/snow_1.webp') }}" alt="">
                    </div>
                </div>
                <div class="card card-parent mb-2 animate-entry delay-2 px-3 py-4">
                    @foreach ($stations as $station)
                        @if($station->id != 4)
                        <a class="station-custom-btn-{{ $station->id }}"
                            type="button"

                            @if($station->id == 3)
                                onclick="window.location.href='{{ route('referrals.index') }}'"
                            @else
                                onclick="gotoStation({{ $station->id }})"
                            @endif
                            >

                            <div class="station-image-container">
                                        @php
                                            $customStations = [1, 2, 3];
                                            if (in_array($station->id, $customStations)) {
                                                $image = asset("images/station/ST{$station->id}.webp");
                                            } else {
                                                $image = asset('images/station/ST{{$station->id}}.webp');
                                            }
                                        @endphp
                                <img class="station-icon station-{{ $station->id }} pulse-slow"
                                    data-id="station-{{ $station->id }}"

                                    src="{{ $image }}"
                                    alt="Station {{ $station->id }}"
                                    style="@if($station->status) filter: grayscale(0); @endif"> <!-- grayscale only if NOT completed -->
                            </div>
                            <div class="station-details station-{{ $station->id }}">
                            </div>
                        </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
       
    </div>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let hasCompletedReferrals = @json($hasCompletedReferrals);
                let hasTier2Referrals = @json($hasTier2Referrals);
                window.gotoStamping = function(id,)
                {
                    var url = "{{ route('reward.index', ['reward' => ':id']);}}".replace(
                        ":id",id
                    );
                     window.location.href = url;
                }
                window.gotoStation = function(id, ) {
                    var url = "{{ route('reward.index', ['reward' => ':id']) }}".replace(
                        ":id",
                        id
                    );

                    if (id === 3 && !hasCompletedReferrals) {
                        // Show the not allowed modal if trying to access Tier 1 referral station without 1 completed referral
                        var notAllowedModal = new bootstrap.Modal(document.getElementById('notAllowedModal'));
                        notAllowedModal.show();
                        return;
                    }

                    if (id === 4 && !hasTier2Referrals) {
                        // Show the not allowed modal if trying to access Tier 2 referral station without 5 completed referrals
                        var notAllowedModal = new bootstrap.Modal(document.getElementById('notAllowedModal'));
                        notAllowedModal.show();
                        return;
                    }

                    // Redirect to the generated URL
                    window.location.href = url;
                }
            });
        </script>
        <script>
            const menuButton = document.getElementById('menuButton');
            const dropdownMenu = document.getElementById('dropdownMenu');
            const closeMenu = document.getElementById('closeMenu');

            function toggleMenu() {
                const isHidden = dropdownMenu.hasAttribute('hidden');
                if (isHidden) {
                    dropdownMenu.removeAttribute('hidden');
                    menuButton.setAttribute('aria-expanded', 'true');
                } else {
                    dropdownMenu.setAttribute('hidden', '');
                    menuButton.setAttribute('aria-expanded', 'false');
                }
            }

            menuButton.addEventListener('click', toggleMenu);
            closeMenu.addEventListener('click', () => {
                dropdownMenu.setAttribute('hidden', '');
                menuButton.setAttribute('aria-expanded', 'false');
            });

            // Close menu when clicking outside
            document.addEventListener('click', (event) => {
                if (!dropdownMenu.contains(event.target) && !menuButton.contains(event.target)) {
                    dropdownMenu.setAttribute('hidden', '');
                    menuButton.setAttribute('aria-expanded', 'false');
                }
            });
        </script>
    @endpush
@endsection
