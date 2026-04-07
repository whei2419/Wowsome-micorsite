<x-app-layout>
    <style>
        #touchBox {
            width: 40svh;
            height: 40svh;
            padding: 10%;
            display: flex;
            align-items: center;
            justify-content: center;
            user-select: none;
            touch-action: none;
            /* Important for multi-touch */
            position: relative;
        }

        #touchBox img.stamping-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            /* or cover if you want it to fill */
            pointer-events: none;
            /* ← prevents blocking touch events */
            user-select: none;
        }

        .overlay {
            position: fixed;
            top: env(safe-area-inset-top, 0);
            left: env(safe-area-inset-left, 0);
            width: calc(100vw - env(safe-area-inset-left, 0) - env(safe-area-inset-right, 0));
            height: calc(100svh - env(safe-area-inset-top, 0) - env(safe-area-inset-bottom, 0));
            pointer-events: none;
            backdrop-filter: blur(8px);
        }

        /* Overlay for station 1 */
        .overlay.station-1 {
            background: linear-gradient(180deg,
                    rgba(233, 239, 250, 0.3) 0%,
                    rgba(124, 161, 255, 0.3) 48.56%,
                    rgba(9, 84, 181, 0.3) 100%);
        }

        /* Overlay for station 2 */
        .overlay.station-2 {
            background: linear-gradient(180deg,
                    rgba(233, 250, 241, 0.3) 0%,
                    /* light green with transparency */
                    rgba(124, 255, 194, 0.3) 48.56%,
                    /* mid green with transparency */
                    rgba(9, 181, 95, 0.3) 100%
                    /* dark green with transparency */
                );
        }
    </style>
    <div class="py-4 map-page main-content stamping-page" data-id="{{ request()->segment(2) }}">
        <div class="overlay station-{{ request()->segment(2) }}"></div>
        <div class="animate-entry">
            @include('components.branding')
        </div>
        <!-- login Modal -->
        <!-- Welcome Modal -->
        <div class="modal fade transparent-modal" id="welcomeModal" tabindex="-1" aria-labelledby="welcomeModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="position-relative text-center modal-content card">

                    <!-- Close Button (top-right) -->
                    <button type="button" class="top-0 position-absolute m-3 btn-close end-0" data-bs-dismiss="modal"
                        aria-label="Close"></button>

                    <!-- Image -->
                    <img src="{{ asset('images/dutchlady/dutchLadyWelcomeModal.webp') }}" alt="Welcome"
                        class="img-fluid">

                </div>
            </div>
        </div>
        <div class="mt-4 text-center success-text d-none">
            <h2 class="animate-entry sub-heading-text">Nicely done!!<br>
                Stamp Collected!</h2>
        </div>
        <div class="mb-2 animate-entry delay-2 station-selection-container">
            <!-- Center image (middle area) -->
            <div class="row">
                <div class="d-flex align-items-center justify-content-center p-0 animate-entry col-12">
                    <div id="touchBox">
                        <img class="stamping-image"
                            src="{{ asset('images/brand/STMP' . request()->segment(2) . '.webp') }}" alt="Stamp Image"
                            data-stamp-id="{{ request()->segment(2) }}">
                    </div>
                </div>
            </div>

            <div id="countDisplay" class="mt-3 text-center d-none">
                Touches inside count: <span id="countNum">0</span>
            </div>


            <!-- Bottom CTA -->
            <div class="row">
                <div class="text-center col-12">
                    <div class="d-block">
                        <div class="mb-3 animate-entry delay-2 col">
                            <button type="button" class="custom-btn custom-btn-primary stamp-btn"
                                @if (request()->segment(2) == 3) onclick="window.location.href='{{ route('station.giftselection') }}'"
                                @else
                                    onclick="window.location.href='{{ route('dashboard') }}'" @endif>
                                Home
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-footer />
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const box = document.getElementById("touchBox");
            const countDisplay = document.getElementById("countNum");
            const stampingPage = document.querySelector(".stamping-page");
            const stationid = stampingPage ? parseInt(stampingPage.dataset.id) : null;
            const activeInside = new Map();
            let hasStamped = false;
            const user = @json($user);
            isStamped();

            function isStamped() {
                if (user) {
                    hasStamped = true;
                    stampingPage.classList.add("active");
                    const text = document.querySelector(".success-text");
                    text.classList.remove("d-none");
                    const button = document.querySelector(".stamp-btn");
                    if (button) {
                        button.removeAttribute("disabled");
                        button.style.pointerEvents = "auto";
                        console.log('test');
                    }
                }
            }

            function isInside(event, element) {
                const rect = element.getBoundingClientRect();
                return (
                    event.clientX >= rect.left &&
                    event.clientX <= rect.right &&
                    event.clientY >= rect.top &&
                    event.clientY <= rect.bottom
                );
            }

            function updateDisplay() {
                countDisplay.textContent = activeInside.size;
                // Toggle grayscale removal
                // Only trigger once
                const requiredCount = 3;
                console.log(requiredCount);
                console.log('activeInside.size:', activeInside.size, 'requiredCount:', requiredCount, 'hasStamped:',
                    hasStamped);

                if (!hasStamped && activeInside.size === requiredCount) {
                    console.log('activeInside.size:', activeInside.size, 'requiredCount:', requiredCount,
                        'hasStamped:', hasStamped);

                    hasStamped = true;
                    stampingPage.classList.add("active");

                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const stampImage = document.querySelector('.stamping-image');
                    const stampId = stampingPage ? parseInt(stampingPage.dataset.id) : null;

                    $.ajax({
                        url: '{{ route('process_stamp') }}',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        data: {
                            station: stampId,
                        },
                        success: function(response) {
                            console.log(response);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error sending QR Code message:', error);
                        }
                    });
                    const text = document.querySelector(".success-text");
                    text.classList.remove("d-none");
                    const button = document.querySelector(".stamp-btn");
                    if (button) {
                        button.removeAttribute("disabled");
                        button.style.pointerEvents = "auto";
                        console.log('test');
                    }
                } else {
                    console.log('invalid touchpoint');
                }

            }

            document.addEventListener("pointerdown", (event) => {
                if (event.pointerType === "mouse") return;
                if (isInside(event, box)) {
                    activeInside.set(event.pointerId, event);
                }
                updateDisplay();
            });

            document.addEventListener("pointermove", (event) => {
                if (event.pointerType === "mouse") return;

                if (activeInside.has(event.pointerId) && !isInside(event, box)) {
                    activeInside.delete(event.pointerId);
                } else if (!activeInside.has(event.pointerId) && isInside(event, box)) {
                    activeInside.set(event.pointerId, event);
                }

                updateDisplay();
            });

            document.addEventListener("pointerup", (event) => {
                activeInside.delete(event.pointerId);
                updateDisplay();
            });

            document.addEventListener("pointercancel", (event) => {
                activeInside.delete(event.pointerId);
                updateDisplay();
            });
        });
    </script>
</x-app-layout>
