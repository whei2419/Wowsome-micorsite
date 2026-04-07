<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Live Feed</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flipclock@0.7.8/compiled/flipclock.css" />
    @vite(['resources/sass/app.scss'])
    </body>

    <!-- Pusher -->
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <style>
        @font-face {
            font-family: 'Stella Demo';
            src: url('{{ asset('images/font/Stella Demo.otf') }}') format('opentype');
            font-weight: normal;
            font-style: normal;
        }


        html,
        body {
            width: 100vw;
            height: 100svh;
            overflow: hidden;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        #aquarium-container {
            position: relative;
            width: 100%;
            height: 100%;
            margin: 0;
            background: none !important;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #aquarium-container canvas,
        #aquarium-container video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: transparent !important;
            pointer-events: none;
            /* optional: lets clicks pass through */
        }

        #aquarium-container video {
            z-index: 1;
            pointer-events: none;
        }

        #aquarium-container canvas {
            z-index: 2;
        }

        .clock-container {
            position: absolute;
            top: 6vh;
            right: 20px;
            z-index: 999;
            width: 300px;
            height: 100px;
        }

        .clock-container p {
            color: white;
            font-size: 29px;
            text-align: center;
        }

        .branding {
            position: absolute;
            top: 9vh;
            left: 50%;
            z-index: 999;
            width: 400px;
            height: 150px;
            transform: translate(-50%, -50%);
        }

        .branding img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            margin: 0 auto;
        }

        .flip-clock-wrapper {
            font-size: 18px !important;
            /* adjust as needed */
            transform: scale(0.8);
            /* adjust scale for overall size */
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div class="branding">
        <img class="logo" src="{{ asset('images/brand/logo.webp') }}" alt="Brand Logo" />
    </div>
    <div id="aquarium-container">
        <video id="aquarium-bg-video" src="{{ asset('video/1080 x 1920 underworld.mp4') }}" width="1080"
            height="1920" autoplay loop muted playsinline></video>
    </div>
    {{-- <div class="clock-container">
        <p>Total Pledge</p>
        <div class="d-flex justify-content-center clock" data-url="{{ route('pledge.counter') }}">dd</div>
    </div> --}}

    <script>
        window.ASSET_BASE = "{{ asset('') }}".replace(/\/$/, '');
    </script>

    @vite('resources/js/aquariumVip.js')

</html>
