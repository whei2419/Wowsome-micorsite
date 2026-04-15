<x-guest-layout>
    <style>
        * {
            font-family: 'PlusJakartaSans', sans-serif;
        }

        .player-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            padding: 5vh 5vw;
        }

        /* ── Logo ── */
        .player-logo .logo {
            max-width: 200px;
            width: auto;
            height: auto;
        }

        /* ── Shared: hide states ── */
        .state {
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1;
            width: 100%;
        }

        .state.active {
            display: flex;
        }

        /* ── State: idle ── */
        .btn-circle-start {
            width: min(55vw, 55vh);
            height: min(55vw, 55vh);
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 2.5px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 40px rgba(255, 255, 255, 0.15), 0 8px 32px rgba(0, 0, 0, 0.3);
            color: #ffffff;
            font-size: clamp(1.4rem, 5vw, 2.2rem);
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-circle-start:hover {
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 0 60px rgba(255, 255, 255, 0.25), 0 12px 40px rgba(0, 0, 0, 0.4);
            transform: scale(1.05);
            color: #fff;
        }

        .btn-circle-start:active {
            transform: scale(0.96);
        }

        /* ── State: playing ── */
        .ring-container {
            position: relative;
            width: min(55vw, 55vh);
            height: min(55vw, 55vh);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ring-container svg {
            width: 100%;
            height: 100%;
            overflow: visible;
        }

        .ring-label {
            position: absolute;
            color: #fff;
            font-size: clamp(1.2rem, 4vw, 1.8rem);
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            text-align: center;
            pointer-events: none;
        }

        .playing-sub {
            color: rgba(255, 255, 255, 0.85);
            font-size: clamp(0.85rem, 2.5vw, 1.1rem);
            margin-top: 2vh;
            letter-spacing: 0.05em;
        }

        /* ── State: done ── */
        .thankyou-text {
            color: #ffffff;
            font-size: clamp(2rem, 8vw, 3.5rem);
            font-weight: 800;
            letter-spacing: 0.06em;
        }

        /* ── Bottom controls ── */
        .bottom-controls {
            display: flex;
            gap: 1.5rem;
            padding-bottom: 2vh;
        }

        .btn-pill {
            padding: 0.75rem 2.5rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: clamp(0.85rem, 2.5vw, 1rem);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            border: 1.5px solid rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            transition: all 0.25s ease;
        }

        .btn-pill:hover {
            background: rgba(255, 255, 255, 0.22);
            border-color: rgba(255, 255, 255, 0.7);
            transform: translateY(-1px);
        }

        .btn-pill:active {
            transform: scale(0.97);
        }
    </style>

    <div class="player-wrapper"
        style="background: url('{{ asset('images/brand/Armani POY_second_1_5x.webp') }}') center center / cover no-repeat;">

        {{-- Logo (always visible) --}}
        <div class="player-logo animate-entry">
            <img src="{{ asset('images/brand/logo.webp') }}" alt="Brand Logo" class="logo" />
        </div>

        {{-- ── STATE: IDLE ── --}}
        <div id="state-idle" class="state active">
            <button class="btn-circle-start" onclick="startPlayer()">Start</button>
        </div>

        {{-- ── STATE: PLAYING ── --}}
        <div id="state-playing" class="state" style="gap: 2vh;">
            <div class="ring-container">
                <svg viewBox="0 0 200 200">
                    <defs>
                        <radialGradient id="orangeGrad" cx="50%" cy="50%" r="50%">
                            <stop offset="0%" stop-color="#ffaa60" />
                            <stop offset="100%" stop-color="#c84b38" />
                        </radialGradient>
                        <filter id="glow" x="-30%" y="-30%" width="160%" height="160%">
                            <feGaussianBlur stdDeviation="5" result="blur" />
                            <feMerge>
                                <feMergeNode in="blur" />
                                <feMergeNode in="SourceGraphic" />
                            </feMerge>
                        </filter>
                    </defs>
                    {{-- Track --}}
                    <circle cx="100" cy="100" r="88" fill="none" stroke="rgba(255,255,255,0.12)"
                        stroke-width="6" />
                    {{-- Progress ring --}}
                    <circle id="progress-ring" cx="100" cy="100" r="88" fill="none"
                        stroke="rgba(255,255,255,0.9)" stroke-width="6" stroke-linecap="round" stroke-dasharray="552.92"
                        stroke-dashoffset="552.92" transform="rotate(-90 100 100)" />
                    {{-- Inner glow ball --}}
                    <circle cx="100" cy="100" r="76" fill="url(#orangeGrad)" filter="url(#glow)" />
                </svg>
                <span class="ring-label">PLAYING</span>
            </div>
            <p class="playing-sub">Your Jam is playing now!</p>
        </div>

        {{-- ── STATE: DONE ── --}}
        <div id="state-done" class="state">
            <p class="thankyou-text">Thank You</p>
        </div>

        {{-- Bottom controls (change per state) --}}
        <div class="bottom-controls">
            {{-- Idle: spacer --}}
            <div id="ctrl-idle" style="height:3rem;"></div>

            {{-- Playing: stop + restart --}}
            <div id="ctrl-playing" style="display:none; gap:1.5rem;">
                <button class="btn-pill" onclick="stopPlayer()">Stop</button>
                <button class="btn-pill" onclick="restartPlayer()">Restart</button>
            </div>

            {{-- Done: done button --}}
            <div id="ctrl-done" style="display:none;">
                <button class="btn-pill" onclick="donePlayer()">Done</button>
            </div>
        </div>

    </div>

    <script>
        const DURATION = 60; // seconds — match video length
        const CIRCUMFERENCE = 2 * Math.PI * 88; // 552.92
        const ring = document.getElementById('progress-ring');
        let timer = null;
        let startTime = null;

        const states = ['idle', 'playing', 'done'];

        function showState(name) {
            states.forEach(s => {
                document.getElementById('state-' + s).classList.toggle('active', s === name);
                const ctrl = document.getElementById('ctrl-' + s);
                if (ctrl) ctrl.style.display = s === name ? (s === 'idle' ? 'block' : 'flex') : 'none';
            });
        }

        function resetRing() {
            ring.style.strokeDashoffset = CIRCUMFERENCE;
        }

        function startProgress() {
            startTime = Date.now();
            clearInterval(timer);
            timer = setInterval(() => {
                const elapsed = (Date.now() - startTime) / 1000;
                const progress = Math.min(elapsed / DURATION, 1);
                ring.style.strokeDashoffset = CIRCUMFERENCE * (1 - progress);
                if (progress >= 1) {
                    clearInterval(timer);
                    showState('done');
                }
            }, 50);
        }

        function postAction(url, body = {}) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(body),
            });
        }

        function startPlayer() {
            postAction('{{ route('player.play') }}', {
                duration: DURATION
            });
            showState('playing');
            resetRing();
            startProgress();
        }

        function stopPlayer() {
            clearInterval(timer);
            postAction('{{ route('player.stop') }}');
            showState('done');
        }

        function restartPlayer() {
            postAction('{{ route('player.restart') }}', {
                duration: DURATION
            });
            resetRing();
            startProgress();
        }

        function donePlayer() {
            resetRing();
            showState('idle');
        }
    </script>
</x-guest-layout>
