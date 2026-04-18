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

        /* ── Ping indicator ── */
        .ping-bar {
            position: fixed;
            top: 1rem;
            right: 1.2rem;
            z-index: 100;
        }

        .ping-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transition: background 0.3s;
        }

        .ping-dot.ok {
            background: #4ade80;
        }

        .ping-dot.err {
            background: #f87171;
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
            flex-wrap: wrap;
            justify-content: center;
            gap: 1rem;
            padding-bottom: 2vh;
            width: 100%;
        }

        .btn-pill {
            padding: 0.75rem 1.5rem;
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

        {{-- Ping indicator --}}
        <div class="ping-bar">
            <span class="ping-dot" id="ping-dot"></span>
        </div>

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

            {{-- Playing: pause + restart + done --}}
            <div id="ctrl-playing" style="display:none; gap:1rem; flex-wrap:wrap; justify-content:center;">
                <button class="btn-pill" onclick="pausePlayer()">Pause</button>
                <button class="btn-pill" onclick="restartPlayer()">Restart</button>
                <button class="btn-pill" onclick="donePlayer()">Done</button>
            </div>

            {{-- Done: done button --}}
            <div id="ctrl-done" style="display:none;">
                <button class="btn-pill" onclick="donePlayer()">Done</button>
            </div>
        </div>

    </div>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        const states = ['idle', 'playing', 'done'];

        function showState(name) {
            states.forEach(s => {
                document.getElementById('state-' + s).classList.toggle('active', s === name);
                const ctrl = document.getElementById('ctrl-' + s);
                if (ctrl) ctrl.style.display = s === name ? (s === 'idle' ? 'block' : 'flex') : 'none';
            });
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
            postAction('{{ route('player.play') }}');
            showState('playing');
        }

        function pausePlayer() {
            // Change button to Resume
            const btn = document.querySelector('#ctrl-playing .btn-pill');
            btn.textContent = 'Resume';
            btn.onclick = resumePlayer;
            postAction('{{ route('player.pause') }}');
        }

        function resumePlayer() {
            // Restore button to Pause
            const btn = document.querySelector('#ctrl-playing .btn-pill');
            btn.textContent = 'Pause';
            btn.onclick = pausePlayer;
            postAction('{{ route('player.resume') }}');
        }

        function resetPauseBtn() {
            const btn = document.querySelector('#ctrl-playing .btn-pill');
            if (btn) {
                btn.textContent = 'Pause';
                btn.onclick = pausePlayer;
            }
        }

        function restartPlayer() {
            postAction('{{ route('player.restart') }}');
            resetPauseBtn();
        }

        function donePlayer() {
            postAction('{{ route('player.done') }}');
            resetPauseBtn();
            showState('idle');
        }

        function doPing() {
            const dot = document.getElementById('ping-dot');
            dot.className = 'ping-dot';
            fetch('{{ route('player.ping') }}', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(r => r.json())
                .then(() => {
                    dot.className = 'ping-dot ok';
                })
                .catch(() => {
                    dot.className = 'ping-dot err';
                });
        }

        // Auto-ping on page load
        doPing();

        // ── Pusher listener: sync with Windows app ──
        // The Windows app calls GET /player/callback?type=player-ended (or player-paused)
        // Laravel rebroadcasts it, and we react here to keep both sides in sync.
        const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}'
        });

        const playerChannel = pusher.subscribe('baby-channel');

        playerChannel.bind('baby-event', function(data) {
            const dot = document.getElementById('ping-dot');

            if (data.type === 'player-ended') {
                // Windows app finished — go to done
                showState('done');
            } else if (data.type === 'player-restarted') {
                // Windows app restarted
                resetPauseBtn();
            } else if (data.type === 'player-ping') {
                dot.className = 'ping-dot ok';
            }
            // player-pause / player-restart from our own buttons are ignored here
            // because postAction uses toOthers() on the server — but as a safety net
            // we simply don't react to them on this side.
        });

        pusher.connection.bind('connected', function() {
            document.getElementById('ping-dot').className = 'ping-dot ok';
        });
        pusher.connection.bind('disconnected', function() {
            document.getElementById('ping-dot').className = 'ping-dot err';
        });
        pusher.connection.bind('error', function() {
            document.getElementById('ping-dot').className = 'ping-dot err';
        });
    </script>
</x-guest-layout>
