<div class="clock d-flex justify-content-center" data-url="{{ route('pledge.counter') }}">dd</div>

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flipclock@0.7.8/compiled/flipclock.css" />
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flipclock@0.7.8/compiled/flipclock.min.js"></script>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script>
        var clock;
        $(document).ready(function() {

            console.log("Document is ready, initializing FlipClock...");
            clock = $('.clock').FlipClock(0, {
                clockFace: 'Counter',
                autoStart: false, // Do not start automatically
                minimumDigits: 4
            });
            fetchAndAnimateCounter();
        });
        // Expose a function to set the counter value manually
        function setCounterValue(value) {
            if (clock) {
                clock.setValue(value);
            }
        }
        // Fetch and animate the counter value from backend
        function fetchAndAnimateCounter() {
            $.ajax({
                url: '{{ route('pledge.counter') }}',
                type: 'GET',
                success: function(res) {
                    if (res && res.count) {
                        setCounterValue(res.count);
                        clock.start(); // Start the clock after setting the value
                    } else {
                        console.error("Invalid response from server:", res);
                    }
                },
                error: function() {
                    console.error("Failed to fetch counter value.");
                }
            });
        }

        // Pusher real-time update
        Pusher.logToConsole = false;
        const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
            cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
            encrypted: true
        });
        const channel = pusher.subscribe('baby-channel');
        channel.bind('baby-event', function(data) {
            fetchAndAnimateCounter();
        });
    </script>
@endpush
