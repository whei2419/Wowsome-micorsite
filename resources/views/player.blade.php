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

        /* Tighter vertical rhythm on capture page (avoids huge empty bands) */
        .player-wrapper.player-page {
            justify-content: flex-start;
            gap: clamp(0.5rem, 2.5vh, 1.75rem);
            padding-top: max(1rem, env(safe-area-inset-top));
            padding-bottom: max(1.5rem, env(safe-area-inset-bottom));
        }

        .player-wrapper.player-page .player-logo {
            flex-shrink: 0;
        }

        .player-wrapper.player-page #state-playing.active {
            flex: 1;
            justify-content: center;
            min-height: 0;
        }

        /* ── Ping indicator ── */
        .ping-bar {
            position: fixed;
            top: 1rem;
            right: 1.2rem;
            z-index: 100;
        }

        .back-bar {
            position: fixed;
            top: max(0.2rem, env(safe-area-inset-top));
            left: 0.75rem;
            z-index: 100;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            border-radius: 50%;
            text-decoration: none;
            color: #0f172a;
            font-size: 1rem;
            background: #ffffff;
            border: none;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.28);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .back-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 26px rgba(0, 0, 0, 0.32);
        }

        .back-btn:active {
            transform: scale(0.97);
        }

        .back-btn:focus-visible {
            outline: 2px solid rgba(255, 255, 255, 0.98);
            outline-offset: 2px;
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
        .player-capture-stack {
            width: min(94vw, 460px);
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .ring-container {
            position: relative;
            width: 100%;
            max-width: 460px;
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

        /* Portrait preview — glass frame (works on photo or solid/gradient bg) */
        .preview-portrait {
            width: 100%;
            max-width: 380px;
            aspect-ratio: 9 / 16;
            background: linear-gradient(160deg,
                    rgba(255, 255, 255, 0.14) 0%,
                    rgba(255, 255, 255, 0.04) 45%,
                    rgba(0, 0, 0, 0.18) 100%);
            border-radius: 20px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow:
                0 0 0 1px rgba(255, 255, 255, 0.22),
                inset 0 1px 0 rgba(255, 255, 255, 0.2),
                0 20px 50px rgba(0, 0, 0, 0.28);
        }

        .preview-image {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 2;
            display: none;
        }

        .preview-image.is-visible {
            display: block;
        }

        .no-capture {
            position: relative;
            z-index: 1;
            pointer-events: none;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 1rem;
        }

        .no-capture__hint {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 0.45rem;
            color: rgba(255, 255, 255, 0.88);
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
            max-width: 220px;
        }

        .no-capture__hint i {
            font-size: 1.2rem;
            opacity: 0.92;
        }

        .no-capture__hint strong {
            font-size: 0.98rem;
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        .no-capture__hint span {
            font-size: 0.8rem;
            opacity: 0.86;
        }

        .shutter-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-top: 0.35rem;
        }

        /* Classic lens-style shutter + Font Awesome */
        .shutter-btn {
            --shutter: min(23vw, 96px);
            position: relative;
            width: var(--shutter);
            height: var(--shutter);
            padding: 0;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            background: transparent;
            color: #0f172a;
            transition: transform 0.22s cubic-bezier(0.34, 1.45, 0.64, 1), filter 0.2s ease;
            filter: drop-shadow(0 14px 28px rgba(0, 0, 0, 0.35));
        }

        .shutter-btn__outer {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: conic-gradient(from 210deg,
                    #ffffff 0%,
                    #f1f5f9 35%,
                    #e2e8f0 55%,
                    #ffffff 100%);
            box-shadow:
                0 0 0 3px rgba(255, 255, 255, 0.95),
                0 0 0 5px rgba(15, 23, 42, 0.14),
                inset 0 2px 2px rgba(255, 255, 255, 0.9),
                inset 0 -3px 8px rgba(15, 23, 42, 0.08);
        }

        .shutter-btn__inner {
            position: absolute;
            inset: 13%;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: clamp(1.35rem, 5.5vw, 1.65rem);
            line-height: 1;
            background: radial-gradient(circle at 32% 28%, #ffffff 0%, #f8fafc 42%, #e8edf4 100%);
            box-shadow:
                inset 0 2px 3px rgba(255, 255, 255, 1),
                inset 0 -4px 10px rgba(15, 23, 42, 0.1);
        }

        .shutter-btn__badge {
            position: absolute;
            right: -4px;
            bottom: -2px;
            width: 28px;
            height: 28px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.78rem;
            background: #16a34a;
            color: #fff;
            border: 2px solid rgba(255, 255, 255, 0.95);
            box-shadow: 0 5px 14px rgba(0, 0, 0, 0.28);
        }

        .shutter-btn:hover {
            transform: scale(1.06);
            filter: drop-shadow(0 18px 36px rgba(0, 0, 0, 0.38));
        }

        .shutter-btn:active {
            transform: scale(0.94);
            filter: drop-shadow(0 8px 18px rgba(0, 0, 0, 0.3));
        }

        .shutter-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
            filter: grayscale(0.15) drop-shadow(0 6px 14px rgba(0, 0, 0, 0.2));
        }

        .shutter-btn:focus-visible {
            outline: 3px solid rgba(255, 255, 255, 0.95);
            outline-offset: 5px;
        }

        .icon-btn {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.22);
            color: #fff;
            width: 52px;
            height: 52px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.2rem;
            line-height: 1;
            transition: transform 0.15s ease, background 0.15s ease, border-color 0.15s ease;
        }

        .icon-btn:hover {
            transform: scale(1.06);
            background: rgba(255, 255, 255, 0.16);
            border-color: rgba(255, 255, 255, 0.4);
        }

        .icon-btn:active {
            transform: scale(0.96);
        }

        .icon-btn:focus-visible {
            outline: 2px solid rgba(255, 255, 255, 0.95);
            outline-offset: 3px;
        }

        /* overlay: countdown / waiting / error */
        .capture-overlay {
            position: absolute;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 1rem;
            background: rgba(2, 6, 12, 0.72);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: #fff;
            z-index: 5;
        }

        .capture-overlay.is-visible {
            display: flex;
        }

        .capture-card {
            width: 100%;
            max-width: 280px;
            padding: 1.5rem 1.25rem;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.14);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.35);
            text-align: center;
        }

        /* Countdown should be number-only, no surrounding card box */
        .capture-overlay.phase-countdown .capture-card {
            max-width: none;
            padding: 0;
            background: transparent;
            border: 0;
            box-shadow: none;
        }

        .capture-phase {
            display: none;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }

        .capture-phase.is-active {
            display: flex;
        }

        .capture-phase .shutter-btn {
            margin-top: 0.35rem;
        }

        .countdown-number {
            font-weight: 900;
            font-size: clamp(48px, 14vw, 88px);
            line-height: 1;
            letter-spacing: -0.03em;
            font-variant-numeric: tabular-nums;
        }

        .capture-spinner {
            width: 44px;
            height: 44px;
            border: 3px solid rgba(255, 255, 255, 0.18);
            border-top-color: #fff;
            border-radius: 50%;
            animation: capture-spin 0.75s linear infinite;
        }

        @keyframes capture-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .waiting-elapsed {
            font-size: 0.8rem;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
            color: rgba(255, 255, 255, 0.5);
            letter-spacing: 0.04em;
        }

        .capture-error-title {
            font-size: 1.05rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: 0.02em;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
    </style>

    <div class="player-wrapper player-page"
        style="background: url('{{ asset('images/brand/Armani POY_second_1_5x.webp') }}') center center / cover no-repeat;">

        {{-- Ping indicator --}}
        <div class="back-bar">
            <a href="{{ route('start') }}" class="back-btn" aria-label="Back to start">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            </a>
        </div>

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
                <div class="player-capture-stack">
                    <div id="captureBox" class="preview-portrait" aria-busy="false">
                        {{-- Transparent 1×1 GIF: valid src so browsers never show a broken-image icon --}}
                        <img id="latestCapture" class="preview-image"
                            src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                            alt="Photo from booth" decoding="async" aria-hidden="true" />
                        <div id="noCapture" class="no-capture" aria-hidden="true">
                            <div class="no-capture__hint">
                                <i class="fa-regular fa-image" aria-hidden="true"></i>
                                <strong>No photo yet</strong>
                                <span>Tap the shutter button to capture</span>
                            </div>
                        </div>
                        <div id="overlay" class="capture-overlay" aria-hidden="true">
                            <div class="capture-card">
                                <div id="phaseCountdown" class="capture-phase">
                                    <div id="countdownNumber" class="countdown-number" aria-live="polite"></div>
                                </div>
                                <div id="phaseWaiting" class="capture-phase">
                                    <div class="capture-spinner" aria-hidden="true"></div>
                                    <p id="waitingElapsed" class="waiting-elapsed">0s · 25s left</p>
                                </div>
                                <div id="phaseError" class="capture-phase">
                                    <p class="capture-error-title">Timed out</p>
                                    <button type="button" id="btnRetry" class="shutter-btn" title="Try capture again"
                                        aria-label="Try capture again">
                                        <span class="shutter-btn__outer" aria-hidden="true"></span>
                                        <span class="shutter-btn__inner">
                                            <i class="fa-solid fa-arrow-rotate-left" aria-hidden="true"></i>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="shutter-row">
                        <button type="button" id="btnCapture" class="shutter-btn" title="Capture photo"
                            aria-label="Capture photo from booth">
                            <span class="shutter-btn__outer" aria-hidden="true"></span>
                            <span class="shutter-btn__inner">
                                <i class="fa-solid fa-camera" aria-hidden="true"></i>
                            </span>
                            <span class="shutter-btn__badge" aria-hidden="true">
                                <i class="fa-solid fa-bolt"></i>
                            </span>
                        </button>
                        <button type="button" id="btnRetake" class="icon-btn" title="Retake photo"
                            aria-label="Retake photo" style="display:none;">
                            <i class="fa-solid fa-arrow-rotate-left" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── STATE: DONE ── --}}
        <div id="state-done" class="state">
            <p class="thankyou-text">Thank You</p>
        </div>

        {{-- Bottom controls removed (capture-only UI) --}}

        <span id="captureAnnounce" class="sr-only" aria-live="polite" aria-atomic="true"></span>

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

        const pingDot = document.getElementById('ping-dot');
        pusher.connection.bind('connected', () => pingDot.className = 'ping-dot ok');
        pusher.connection.bind('disconnected', () => pingDot.className = 'ping-dot err');
        pusher.connection.bind('error', () => pingDot.className = 'ping-dot err');

        const cameraChannel = pusher.subscribe('camera-control');

        // ── Capture state ─────────────────────────────────────────────
        let lastKnownUrl = null;
        let pusherResolve = null;
        let waitTickInterval = null;

        const imgEl = document.getElementById('latestCapture');
        const noCaptureEl = document.getElementById('noCapture');
        const overlay = document.getElementById('overlay');
        const captureBox = document.getElementById('captureBox');
        const phaseCountdown = document.getElementById('phaseCountdown');
        const phaseWaiting = document.getElementById('phaseWaiting');
        const phaseError = document.getElementById('phaseError');
        const countdownEl = document.getElementById('countdownNumber');
        const waitingElapsed = document.getElementById('waitingElapsed');
        const btnCaptureEl = document.getElementById('btnCapture');
        const btnRetakeEl = document.getElementById('btnRetake');
        const btnRetry = document.getElementById('btnRetry');
        const captureAnnounce = document.getElementById('captureAnnounce');
        const TRANSPARENT_PIXEL =
            'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

        function announce(msg) {
            if (captureAnnounce) captureAnnounce.textContent = msg;
        }

        function setPhase(name) {
            if (phaseCountdown) phaseCountdown.classList.toggle('is-active', name === 'countdown');
            if (phaseWaiting) phaseWaiting.classList.toggle('is-active', name === 'waiting');
            if (phaseError) phaseError.classList.toggle('is-active', name === 'error');
            if (overlay) {
                overlay.classList.toggle('phase-countdown', name === 'countdown');
                overlay.classList.toggle('phase-waiting', name === 'waiting');
                overlay.classList.toggle('phase-error', name === 'error');
            }
        }

        function setOverlayVisible(on) {
            if (!overlay) return;
            overlay.classList.toggle('is-visible', on);
            overlay.setAttribute('aria-hidden', on ? 'false' : 'true');
            if (captureBox) captureBox.setAttribute('aria-busy', on ? 'true' : 'false');
        }

        function hideCaptureOverlay() {
            setOverlayVisible(false);
            setPhase(null);
        }

        function clearWaitTick() {
            if (waitTickInterval) {
                clearInterval(waitTickInterval);
                waitTickInterval = null;
            }
        }

        function updateCapture(url) {
            if (!url) {
                imgEl.classList.remove('is-visible');
                imgEl.src = TRANSPARENT_PIXEL;
                imgEl.setAttribute('aria-hidden', 'true');
                noCaptureEl.style.display = 'block';
                if (btnCaptureEl) btnCaptureEl.style.display = '';
                if (btnRetakeEl) btnRetakeEl.style.display = 'none';
                lastKnownUrl = null;
                return;
            }
            lastKnownUrl = url;
            imgEl.src = url + '?_=' + Date.now();
            imgEl.classList.add('is-visible');
            imgEl.removeAttribute('aria-hidden');
            noCaptureEl.style.display = 'none';
            hideCaptureOverlay();
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

        function startCountdown(seconds) {
            return new Promise(resolve => {
                setOverlayVisible(true);
                setPhase('countdown');
                announce('Starting ' + seconds + ' second countdown');
                let s = seconds;
                countdownEl.textContent = String(s);
                const iv = setInterval(() => {
                    s -= 1;
                    if (s > 0) {
                        countdownEl.textContent = String(s);
                    } else {
                        clearInterval(iv);
                        countdownEl.textContent = '';
                        resolve();
                    }
                }, 1000);
            });
        }

        function waitForNewCapture(prevUrl, timeoutSec = 25) {
            return new Promise(async (resolve) => {
                setPhase('waiting');
                if (waitingElapsed) waitingElapsed.textContent = '0s · ' + timeoutSec + 's left';

                const start = Date.now();
                const tick = () => {
                    const elapsedSec = Math.floor((Date.now() - start) / 1000);
                    const left = Math.max(0, timeoutSec - elapsedSec);
                    if (waitingElapsed) {
                        waitingElapsed.textContent = elapsedSec + 's · ' + left + 's left';
                    }
                };
                tick();
                clearWaitTick();
                waitTickInterval = setInterval(tick, 400);

                pusherResolve = (url) => {
                    clearWaitTick();
                    resolve(url);
                };

                const deadline = Date.now() + timeoutSec * 1000;
                while (Date.now() < deadline) {
                    await new Promise(r => setTimeout(r, 900));
                    const url = await fetchLatest();
                    if (url && url !== prevUrl) {
                        pusherResolve = null;
                        clearWaitTick();
                        resolve(url);
                        return;
                    }
                }
                pusherResolve = null;
                clearWaitTick();
                resolve(null);
            });
        }

        if (btnCaptureEl) btnCaptureEl.addEventListener('click', async () => {
            btnCaptureEl.disabled = true;
            const prev = lastKnownUrl;

            await startCountdown(3);
            announce('Triggering booth capture');

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
                announce('Photo received');
            } else {
                setOverlayVisible(true);
                setPhase('error');
                announce('Capture timed out');
            }
            btnCaptureEl.disabled = false;
        });

        if (btnRetakeEl) btnRetakeEl.addEventListener('click', () => updateCapture(''));

        if (btnRetry) btnRetry.addEventListener('click', () => {
            hideCaptureOverlay();
            announce('Dismissed error');
            if (btnCaptureEl) btnCaptureEl.focus();
        });

        fetchLatest().then(url => {
            if (url) updateCapture(url);
        });
    </script>
</x-guest-layout>
