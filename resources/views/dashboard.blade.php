@extends('layouts.app')

<style>
    #dropdownMenu
    {
        left: 50%;
        transform: translateX(-50%);
        position: absolute;
        top: 60px;
        right: 0;
        background: #E7C791;
        border-radius: 12px;
        padding: 16px;
        width: 90%;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
        color: #6b3e00;
        z-index: 9999;
    }

    #closeMenu {
        background:none;
        border:none;
        font-weight:bold;
        font-size:1.4rem;
        color:#6b3e00;
        cursor:pointer;
    }

    #menuButton
    {
        background:#fff;
        border:none;
        border-radius:50%;
        width:48px;
        height:48px;
        cursor:pointer;
        display:flex;
        align-items:center;
        justify-content:center;
        box-shadow:0 2px 5px rgba(0,0,0,0.2);
        position:relative; z-index:1000;
    }
</style>

@section('content')
    <div class="p-4 map-page main-content">
       <div class="top-container">
            <!-- Branding (top area) -->
            <div class="row flex-grow-1">
                <div class="col-12 pt-5 animate-entry">
                    <x-branding/>
                </div>
            </div>
        </div>
        <!-- login Modal -->
        <h2 class="mx-4 text-center animate-entry text-white text-bold heading py-5">Rewards</h2>

        <!-- Menu Button -->
            <div class="btn-container text-end pt-5">
                <button id="menuButton" aria-expanded="false" aria-controls="dropdownMenu" aria-label="Toggle Menu">
                    <!-- Hamburger Icon -->
                    <svg width="24" height="24" fill="#333" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                        <rect y="4" width="24" height="2" rx="1"></rect>
                        <rect y="11" width="24" height="2" rx="1"></rect>
                        <rect y="18" width="24" height="2" rx="1"></rect>
                    </svg>
                </button>
            </div>

            <!-- Dropdown Menu -->
            <div id="dropdownMenu" role="menu" aria-labelledby="menuButton" hidden>
                <!-- Menu Header with Close Button -->
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <span style="font-weight:600; font-size:1.1rem;">Menu</span>
                    <button id="closeMenu" aria-label="Close Menu">×</button>
                </div>

                <!-- Menu Items -->
                <a href="{{ route('dashboard'); }}" role="menuitem" style="display:block; padding:12px; border-radius:8px; text-decoration:none; color:#5a3300; margin-bottom:8px; background:#e7c791cc; box-shadow: inset 0 2px 4px rgba(255 255 255 / 0.5);">Rewards</a>

                <a href="{{ route('directory'); }}" role="menuitem" style="display:block; padding:12px; border-radius:8px; text-decoration:none; color:#5a3300; margin-bottom:8px; background:#e7c791cc; box-shadow: inset 0 2px 4px rgba(255 255 255 / 0.5);">Directory <span style="float:right;">→</span></a>

                <a href="{{ route('linktree'); }}" role="menuitem" style="display:block; padding:12px; border-radius:8px; text-decoration:none; color:#5a3300; margin-bottom:8px; background:#e7c791cc; box-shadow: inset 0 2px 4px rgba(255 255 255 / 0.5);">Linktree <span style="float:right;">→</span></a>

                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" role="menuitem" style="display:block; width:100%; padding:12px; border-radius:8px; border:none; text-decoration:none; color:#5a3300; background:#e7c791cc; box-shadow: inset 0 2px 4px rgba(255 255 255 / 0.5); font-family: 'Montserrat', sans-serif; font-weight: 700; text-align:left; cursor:pointer;">Logout</button>
                </form>
            </div>


        <!-- Modal -->
        <div class="modal fade custom-modal" id="notAllowedModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered w-75 m-auto">
                <div class="modal-content card">
                    <div class="modal-body">
                        <div class="text-center content">
                            <div class="text-content mt-4 mb-4">
                                <p class="message text-dark">
                                    Ready for Treasure Spot 3? <br>First, complete Treasure Spot 1 & Treasure Spot 2 to unlock it!
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
            <div class="card card-parent mb-2 animate-entry delay-2 px-3 py-4">
                @foreach ($stations as $station)
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
                @endforeach
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let canAccessStation3 = @json($canAccessStation3);
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

                    if (id === 4 && !canAccessStation3) {
                        // Show the not allowed modal if trying to access station 3 without permission
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
