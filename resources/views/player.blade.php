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

        /* Portrait preview */
        .preview-portrait {
            width: 88%;
            max-width: 420px;
            aspect-ratio: 9 / 16;
            background: #111;
            border-radius: 10px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .preview-portrait img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .no-capture {
            color: #aaa;
            font-weight: 600;
        }

        .icon-btn {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #fff;
            width: 56px;
            height: 56px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform .12s ease, background .12s ease;
        }

        .icon-btn:hover {
            transform: scale(1.05);
            background: rgba(255, 255, 255, 0.12);
        }

        .icon-btn.primary {
            width: 88px;
            height: 88px;
            background: #ffffff;
            color: #111827;
            border: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 8px 24px rgba(2, 6, 23, 0.12);
        }

        .icon-btn.primary:hover {
            transform: scale(1.03);
        }

        /* overlay for countdown / waiting */
        .capture-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 12px;
            background: rgba(0, 0, 0, 0.35);
            color: #fff;
            z-index: 5;
        }

        .countdown-number {
            font-weight: 900;
            font-size: clamp(36px, 12vw, 96px);
            letter-spacing: -0.02em;
        }

        .waiting-text {
            font-size: 16px;
            opacity: 0.95;
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

        {{-- Idle state removed — player auto-starts into Playing state --}}

        {{-- ── STATE: PLAYING ── --}}
        <div id="state-playing" class="state" style="gap: 2vh;">
            <div class="ring-container">
                <div
                    style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;flex-direction:column;">
                    <div id="captureBox" class="preview-portrait">
                        <img id="latestCapture" src="" alt="Latest capture" />
                        <div id="noCapture" class="no-capture">No capture yet</div>
                        <div id="overlay" class="capture-overlay" style="display:none;">
                            <div id="countdownNumber" class="countdown-number"></div>
                            <div id="waitingText" class="waiting-text" style="display:none;">Waiting for capture…</div>
                        </div>
                    </div>
                    <div style="margin-top:12px;display:flex;gap:12px;">
                        <button id="btnCapture" class="icon-btn primary" title="Capture">
                            <!-- camera icon (dark on white) -->
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 7H5L7 4H17L19 7H21V20H3V7Z" stroke="currentColor" stroke-width="1.6"
                                    stroke-linejoin="round" />
                                <circle cx="12" cy="13" r="3.5" stroke="currentColor"
                                    stroke-width="1.6" />
                            </svg>
                        </button>
                        <button id="btnRetake" class="icon-btn" title="Retake" style="display:none;">
                            <!-- retake/refresh icon -->
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M21 12A9 9 0 1 0 6.3 4.6" stroke="white" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M21 3v6h-6" stroke="white" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <p class="playing-sub">Latest capture preview</p>
        </div>

        {{-- ── STATE: DONE ── --}}
        <div id="state-done" class="state">
            <p class="thankyou-text">Thank You</p>
        </div>

        {{-- Bottom controls removed (capture-only UI) --}}

    </div>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        // ── Mode ──────────────────────────────────────────────────────
        const mode = new URLSearchParams(window.location.search).get('mode') || 'photo';

        // ── State machine ─────────────────────────────────────────────
        const states = ['playing', 'done'];

        function showState(name) {
            states.forEach(s => {
                const el = document.getElementById('state-' + s);
                if (el) el.classList.toggle('active', s === name);
            });
        }
        showState('playing');

        // ── Pusher ────────────────────────────────────────────────────
        const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
            forceTLS: true,
        });

        // Ping dot tracks connection
        const pingDot = document.getElementById('ping-dot');
        pusher.connection.bind('connected', () => pingDot.className = 'ping-dot ok');
        pusher.connection.bind('disconnected', () => pingDot.className = 'ping-dot err');
        pusher.connection.bind('error', () => pingDot.className = 'ping-dot err');

        const cameraChannel = pusher.subscribe('camera-control');

        // ── Capture state ─────────────────────────────────────────────
        let lastKnownUrl = null;
        let pusherResolve = null; // resolves waitForNewCapture instantly

        const imgEl = document.getElementById('latestCapture');
        const noCaptureEl = document.getElementById('noCapture');
        const overlay = document.getElementById('overlay');
        const countdownEl = document.getElementById('countdownNumber');
        const waitingEl = document.getElementById('waitingText');
        const btnCaptureEl = document.getElementById('btnCapture');
        const btnRetakeEl = document.getElementById('btnRetake');

        function updateCapture(url) {
            if (!url) {
                imgEl.style.display = 'none';
                imgEl.src = '';
                noCaptureEl.style.display = 'block';
                if (btnCaptureEl) btnCaptureEl.style.display = '';
                if (btnRetakeEl) btnRetakeEl.style.display = 'none';
                lastKnownUrl = null;
                return;
            }
            lastKnownUrl = url;
            imgEl.src = url + '?_=' + Date.now();
            imgEl.style.display = 'block';
            noCaptureEl.style.display = 'none';
            overlay.style.display = 'none';
            if (btnCaptureEl) btnCaptureEl.style.display = 'none';
            if (btnRetakeEl) btnRetakeEl.style.display = '';
        }

        async function fetchLatest() {
            try {
                const res = await fetch('/api/captures/latest');
                if (!res.ok) return null;
                const data = await res.json();
                return (data && data.url) ? data.url : null;
            } catch {
                return null;
            }
        }

        // Pusher fires this when Windows app has uploaded
        cameraChannel.bind('capture:uploaded', (data) => {
            const url = data && data.url ? data.url : null;
            if (url && url !== lastKnownUrl) {
                updateCapture(url);
                if (pusherResolve) {
                    pusherResolve(url);
                    pusherResolve = null;
                }
            }
        });

        // ── Countdown ─────────────────────────────────────────────────
        function startCountdown(seconds) {
            return new Promise(resolve => {
                overlay.style.display = 'flex';
                waitingEl.style.display = 'none';
                let s = seconds;
                countdownEl.textContent = s;
                const iv = setInterval(() => {
                    s -= 1;
                    if (s > 0) {
                        countdownEl.textContent = s;
                    } else {
                        clearInterval(iv);
                        countdownEl.textContent = '';
                        resolve();
                    }
                }, 1000);
            });
        }

        // ── Wait for upload (Pusher-first, poll as fallback) ──────────
        function waitForNewCapture(prevUrl, timeoutSec = 25) {
            return new Promise(async resolve => {
                // Pusher fast path
                pusherResolve = (url) => resolve(url);

                // Polling fallback
                const deadline = Date.now() + timeoutSec * 1000;
                waitingEl.style.display = 'block';
                while (Date.now() < deadline) {
                    await new Promise(r => setTimeout(r, 900));
                    const url = await fetchLatest();
                    if (url && url !== prevUrl) {
                        if (pusherResolve) {
                            pusherResolve = null;
                        }
                        resolve(url);
                        return;
                    }
                }
                // timed out
                pusherResolve = null;
                resolve(null);
            });
        }

        // ── Button handlers ───────────────────────────────────────────
        if (btnCaptureEl) btnCaptureEl.addEventListener('click', async () => {
            btnCaptureEl.disabled = true;
            const prev = lastKnownUrl;

            await startCountdown(3);
            waitingEl.textContent = 'Waiting for capture…';

            // tell the Windows app to capture
            try {
                await fetch('/api/trigger-capture', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        mode
                    }),
                });
            } catch (e) {
                console.warn('trigger failed', e);
            }

            const newUrl = await waitForNewCapture(prev, 25);
            if (newUrl) {
                updateCapture(newUrl);
            } else {
                // timeout — reset UI
                overlay.style.display = 'none';
                waitingEl.textContent = 'Waiting for capture…';
            }
            btnCaptureEl.disabled = false;
        });

        if (btnRetakeEl) btnRetakeEl.addEventListener('click', () => updateCapture(''));

        // ── On load: show last capture if one exists ──────────────────
        fetchLatest().then(url => {
            if (url) updateCapture(url);
        });
    </script>
</x-guest-layout>
