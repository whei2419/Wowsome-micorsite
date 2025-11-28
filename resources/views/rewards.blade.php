@extends('layouts.app')
@section('title', 'Rewards')
@section('content')

<style>
      /* QR BUTTON */
        .qr-preview {
            width: 140px;
            background: #ffffff;
            padding: 15px 10px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            cursor: pointer;
        }

        .qr-preview img {
            width: 80px;
            height: 80px;
        }

        .qr-preview span {
            font-size: 13px;
            color: #555;
            margin-top: 8px;
            display: block;
        }

        /* MODAL BOX */
        .custom-modal-content {
            padding: 10% 5% 5% 5%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 75vw;
            margin: auto;
        }


        .modal-content .close {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 24px;
            cursor: pointer;
        }

        .qr-large {
            width: 200px;
        }

        .modal-text {
            margin-top: 15px;
            font-size: 14px;
        }

        .modal-email {
            font-weight: bold;
            margin-top: 5px;
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
        <div class="px-4">
        <!-- login Modal -->
                <div class="mb-2 ">
                    <!-- Center image (middle area) -->
                    <div class="row">
                        <div class="col-12 text-center text-white mb-0 my-4 p-0 animate-entry">
                            @if(request()->segment(2) == 3)
                                <h2 class="text-bold main-heading">
                                    Congratulations
                                </h2>
                                <p class="text-light mb-0">You won an exclusive gift</p>
                                <p class="text-light">Valid from 1 Dec - 31 Dec 2025</p>
                            @else
                                <h2 class="text-bold main-heading">
                                    {{ request()->segment(2) == 1 ? 'Weekday' : (request()->segment(2) == 2 ? 'Weekend' : '') }} Exclusive for Elite Circle
                                </h2>
                                <p class="text-light mb-0">Valid from 1 Dec - 31 Dec 2025</p>
                                <p class="text-light">Sign up and enjoy your gifts</p>
                            @endif

                        </div>
                    </div>


                    <!-- Modal Button -->
                    <!-- QR BUTTON -->
                    <div class="d-flex justify-content-center align-items-center mb-4 animate-entry">
                        @if($hasClaimed)
                            <div class="qr-preview m-auto d-flex flex-column justify-content-center align-items-center" style="background: #f0f0f0; cursor: default;">
                                <div style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-check-circle" style="font-size: 48px; color: #28a745;"></i>
                                </div>
                                <span class="text-regular" style="color: #28a745; font-weight: bold;">Already Claimed</span>
                            </div>
                        @else
                            <div id="openModalBtn" class="qr-preview m-auto d-flex flex-column justify-content-center align-items-center"
                            data-bs-toggle="modal"
                            data-bs-target="#qrModal">
                                {!! QrCode::size(80)->generate($userHash) !!}
                                <span class="text-regular">Tap to show QR</span>
                            </div>
                        @endif
                    </div>

                    <!-- MODAL -->
                    @if(!$hasClaimed)
                     <div class="modal fade animate-entry delay-2" id="qrModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content custom-modal-content">
                                <span class="close text-dark ">&times;</span>

                                {!! QrCode::size(200)->generate($userHash) !!}

                                <p class="modal-text text-dark text-center">
                                    Present your QR code at <br>
                                    Shoppes at Four Seasons Place Concierge
                                </p>
                                <p class="modal-email text-dark text-center">{{ $user->email }}</p>
                            </div>
                        </div>
                    </div>
                    @endif


                    <!-- Bottom CTA -->
                    <div class="row">
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
        @if(!$hasClaimed)
        document.getElementById('openModalBtn').addEventListener('click', function () {
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
        @endif
    </script>

@endsection
