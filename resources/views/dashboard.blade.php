@extends('layouts.frontend')

@section('content')
<x-app-layout>
    <div class="py-4 map-page main-content main-background">
        <div class="animate-entry">
            @include('components.branding')
        </div>

        <!-- login Modal -->

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
            <div class="card card-parent mb-2 animate-entry delay-2">
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
    @endpush
@endsection
