<x-app-layout>
    @push('styles')
        <style>
            /* ── Layout ─────────────────────────────────────────────── */
            body {
                background: #1a0010;
                color: #e2e8f0;
                font-family: 'Poppins', sans-serif;
            }

            .mon-wrap {
                padding: 0.75rem 0.75rem 2rem;
                max-width: 1200px;
                margin: 0 auto;
            }

            @media (min-width: 640px) {
                .mon-wrap {
                    padding: 1.5rem 1.5rem 2rem;
                }
            }

            /* ── Header ─────────────────────────────────────────────── */
            .mon-header {
                display: grid;
                grid-template-columns: 1fr;
                gap: 0.6rem;
                margin-bottom: 1rem;
            }

            @media (min-width: 640px) {
                .mon-header {
                    grid-template-columns: 1fr auto auto;
                    align-items: center;
                }
            }

            .mon-header h1 {
                font-size: 1.25rem;
                font-weight: 700;
                letter-spacing: -0.02em;
                color: #000000;
                margin: 0;
            }

            @media (min-width: 640px) {
                .mon-header h1 {
                    font-size: 1.5rem;
                }
            }

            .mon-header .subtitle {
                font-size: 0.78rem;
                color: #94a3b8;
                margin-top: 3px;
            }

            /* ── Header actions ─────────────────────────────────────── */
            .mon-header-actions {
                display: flex;
                gap: 0.6rem;
                flex-wrap: wrap;
                align-items: center;
            }

            /* ── Clock ──────────────────────────────────────────────── */
            .mon-clock {
                font-size: 0.75rem;
                color: #64748b;
                text-align: left;
            }

            @media (min-width: 640px) {
                .mon-clock {
                    text-align: right;
                }
            }

            .mon-clock span {
                display: block;
                font-size: 0.95rem;
                color: #cbd5e1;
                font-variant-numeric: tabular-nums;
            }

            /* ── Grid ───────────────────────────────────────────────── */
            .mon-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 0.5rem;
            }

            @media (min-width: 900px) {
                .mon-grid {
                    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                    gap: 0.75rem;
                }
            }

            /* ── Card ───────────────────────────────────────────────── */
            .mon-card {
                background: #13141f;
                border: 1px solid #222436;
                border-radius: 10px;
                padding: 0.65rem 0.85rem;
                display: flex;
                flex-direction: column;
                gap: 0.35rem;
                transition: border-color 0.3s;
            }

            @media (min-width: 640px) {
                .mon-card {
                    border-radius: 14px;
                    padding: 1rem 1.25rem;
                    gap: 0.5rem;
                }
            }

            .mon-card.ok {
                border-color: #1a4731;
            }

            .mon-card.warn {
                border-color: #4a3a10;
            }

            .mon-card.fail {
                border-color: #4a1a1a;
            }

            .mon-card-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .mon-card-title {
                font-size: 0.8rem;
                font-weight: 600;
                color: #94a3b8;
                text-transform: uppercase;
                letter-spacing: 0.07em;
            }

            .mon-card-icon {
                font-size: 1.1rem;
                color: #475569;
            }

            /* ── Badge ──────────────────────────────────────────────── */
            .mon-badge {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                font-size: 0.78rem;
                font-weight: 600;
                padding: 3px 10px;
                border-radius: 999px;
            }

            .mon-badge.ok {
                background: #052e16;
                color: #4ade80;
            }

            .mon-badge.fail {
                background: #2d0a0a;
                color: #f87171;
            }

            .mon-badge.warn {
                background: #27190a;
                color: #fbbf24;
            }

            .mon-badge.idle {
                background: #1a1f2e;
                color: #94a3b8;
            }

            .mon-badge.pulse::before {
                content: '';
                width: 7px;
                height: 7px;
                border-radius: 50%;
                display: inline-block;
                background: currentColor;
                animation: pulse 1.4s infinite;
            }

            @keyframes pulse {

                0%,
                100% {
                    opacity: 1;
                    transform: scale(1);
                }

                50% {
                    opacity: 0.4;
                    transform: scale(0.8);
                }
            }

            /* ── Card stat value ────────────────────────────────────── */
            .mon-stat {
                font-size: 1.35rem;
                font-weight: 700;
                color: #f1f5f9;
                line-height: 1.1;
            }

            @media (min-width: 640px) {
                .mon-stat {
                    font-size: 1.75rem;
                }
            }

            .mon-stat-label {
                font-size: 0.72rem;
                color: #94a3b8;
            }

            .mon-detail {
                font-size: 0.73rem;
                color: #94a3b8;
                margin-top: 2px;
                word-break: break-all;
            }

            .mon-detail a {
                color: #60a5fa;
                text-decoration: none;
            }

            .mon-detail a:hover {
                text-decoration: underline;
            }

            .mon-detail code {
                background: #1e2133;
                color: #a5b4fc;
                padding: 1px 5px;
                border-radius: 4px;
                font-size: 0.72rem;
            }

            /* ── Event Log ──────────────────────────────────────────── */
            .mon-log-card {
                grid-column: 1 / -1;
            }

            .mon-log {
                list-style: none;
                padding: 0;
                margin: 0;
                max-height: 140px;
                overflow-y: auto;
                display: flex;
                flex-direction: column;
                gap: 3px;
            }

            @media (min-width: 640px) {
                .mon-log {
                    max-height: 220px;
                }
            }

            .mon-log li {
                font-size: 0.75rem;
                padding: 5px 8px;
                border-radius: 6px;
                background: #0d0f18;
                display: flex;
                gap: 0.6rem;
                flex-wrap: wrap;
            }

            .mon-log li .log-time {
                color: #64748b;
                min-width: 68px;
                flex-shrink: 0;
            }

            .mon-log li .log-event {
                color: #cbd5e1;
            }

            .mon-log li.highlight {
                background: #0e1f15;
            }

            .mon-log li.highlight .log-event {
                color: #4ade80;
            }

            /* ── Buttons ────────────────────────────────────────────── */
            .mon-btn {
                padding: 0.45rem 1rem;
                border-radius: 8px;
                font-size: 0.78rem;
                font-weight: 600;
                cursor: pointer;
                border: none;
                transition: opacity 0.15s, transform 0.1s;
                white-space: nowrap;
            }

            .mon-btn:hover {
                opacity: 0.85;
            }

            .mon-btn:active {
                transform: scale(0.97);
            }

            .mon-btn.primary {
                background: #3b82f6;
                color: #fff;
            }

            .mon-btn.ghost {
                background: #1e2133;
                color: #cbd5e1;
                border: 1px solid #2d3352;
            }

            /* ── Section label ──────────────────────────────────────── */
            .mon-section-label {
                font-size: 0.65rem;
                font-weight: 700;
                letter-spacing: 0.1em;
                text-transform: uppercase;
                color: #64748b;
                margin: 0.9rem 0 0.35rem;
            }

            /* ── Modal overrides (undo dark body theme) ──────────────── */
            #modal-reload-confirm .modal-content,
            #modal-reload-confirm .modal-content * {
                color: #212529;
            }

            #modal-reload-confirm .modal-content {
                background: #fff;
            }

            #modal-reload-confirm .modal-header {
                background: #fff;
                border-bottom: 1px solid #dee2e6;
            }

            #modal-reload-confirm .modal-body {
                background: #fff;
            }

            #modal-reload-confirm .modal-body p,
            #modal-reload-confirm .modal-body strong,
            #modal-reload-confirm .modal-body span,
            #modal-reload-confirm .modal-body small {
                color: #212529;
            }

            #modal-reload-confirm .modal-footer {
                background: #fff;
                border-top: 1px solid #dee2e6;
            }

            #modal-reload-confirm .modal-title,
            #modal-reload-confirm .modal-title i {
                color: #212529;
            }

            #modal-reload-confirm .btn-close {
                filter: none;
            }
        </style>
    @endpush

    <div class="mon-wrap">

        {{-- Header --}}
        <div class="mon-header">
            <div>
                <h1>System Monitoring</h1>
                <div class="subtitle">Camera Controls · Live status dashboard</div>
            </div>
            <div class="mon-header-actions">
                <button class="mon-btn primary" id="btn-reload-app" title="Trigger a reload in the Camera Controls app"
                    data-bs-toggle="modal" data-bs-target="#modal-reload-confirm">
                    <i class="fa fa-rotate-right"></i> Trigger Reload
                </button>
                <button class="mon-btn ghost" id="btn-refresh-status">
                    <i class="fa fa-sync"></i> Refresh
                </button>
            </div>
            <div class="mon-clock">
                Server time
                <span id="mon-server-time">—</span>
            </div>
        </div>

        {{-- Pusher / WebSocket section --}}
        <div class="mon-section-label">Connections</div>
        <div class="mon-grid" id="mon-connections">

            <div class="mon-card" id="card-pusher">
                <div class="mon-card-head">
                    <span class="mon-card-title">Pusher</span>
                    <span class="mon-card-icon"><i class="fa fa-bolt"></i></span>
                </div>
                <div>
                    <span class="mon-badge idle pulse" id="badge-pusher">Connecting…</span>
                </div>
                <div class="mon-detail" id="detail-pusher">Subscribing to <code>camera-control</code></div>
            </div>

            <div class="mon-card" id="card-obs">
                <div class="mon-card-head">
                    <span class="mon-card-title">OBS</span>
                    <span class="mon-card-icon"><i class="fa fa-video"></i></span>
                </div>
                <div>
                    <span class="mon-badge idle" id="badge-obs">Unknown</span>
                </div>
                <div class="mon-detail" id="detail-obs">Waiting for events…</div>
            </div>

            <div class="mon-card" id="card-cam">
                <div class="mon-card-head">
                    <span class="mon-card-title">Camera</span>
                    <span class="mon-card-icon"><i class="fa fa-camera"></i></span>
                </div>
                <div>
                    <span class="mon-badge idle" id="badge-cam">Unknown</span>
                </div>
                <div class="mon-detail" id="detail-cam">Waiting for events…</div>
            </div>

            <div class="mon-card" id="card-recording">
                <div class="mon-card-head">
                    <span class="mon-card-title">Recording</span>
                    <span class="mon-card-icon"><i class="fa fa-circle"></i></span>
                </div>
                <div>
                    <span class="mon-badge idle" id="badge-recording">Idle</span>
                </div>
                <div class="mon-detail" id="detail-recording">—</div>
            </div>

            <div class="mon-card" id="card-audio">
                <div class="mon-card-head">
                    <span class="mon-card-title">Audio Source</span>
                    <span class="mon-card-icon"><i class="fa fa-volume-up"></i></span>
                </div>
                <div>
                    <span class="mon-badge idle" id="badge-audio">Unknown</span>
                </div>
                <div class="mon-detail" id="detail-audio">Waiting for events…</div>
            </div>

        </div>

        {{-- Server / uploads section --}}
        <div class="mon-section-label">Server Storage</div>
        <div class="mon-grid" id="mon-storage">

            <div class="mon-card" id="card-photos">
                <div class="mon-card-head">
                    <span class="mon-card-title">Photos</span>
                    <span class="mon-card-icon"><i class="fa fa-image"></i></span>
                </div>
                <div class="mon-stat" id="stat-photos">—</div>
                <div class="mon-stat-label">captures on server</div>
                <div class="mon-detail" id="detail-photo-latest">—</div>
            </div>

            <div class="mon-card" id="card-videos">
                <div class="mon-card-head">
                    <span class="mon-card-title">Videos</span>
                    <span class="mon-card-icon"><i class="fa fa-film"></i></span>
                </div>
                <div class="mon-stat" id="stat-videos">—</div>
                <div class="mon-stat-label">videos on server</div>
                <div class="mon-detail" id="detail-video-latest">—</div>
            </div>

            <div class="mon-card" id="card-server-api">
                <div class="mon-card-head">
                    <span class="mon-card-title">Server API</span>
                    <span class="mon-card-icon"><i class="fa fa-server"></i></span>
                </div>
                <div>
                    <span class="mon-badge idle pulse" id="badge-api">Polling…</span>
                </div>
                <div class="mon-detail" id="detail-api">Last poll: —</div>
            </div>

        </div>

        {{-- Event log --}}
        <div class="mon-section-label">Event Log</div>
        <div class="mon-grid">
            <div class="mon-card mon-log-card">
                <div class="mon-card-head">
                    <span class="mon-card-title">Live Events <span
                            style="opacity:.4;font-weight:400">(Pusher)</span></span>
                    <button class="mon-btn ghost" id="btn-clear-log"
                        style="padding:2px 10px;font-size:0.7rem">Clear</button>
                </div>
                <ul class="mon-log" id="mon-log">
                    <li><span class="log-time">—</span><span class="log-event">Waiting for events…</span></li>
                </ul>
            </div>
        </div>

        {{-- Reload confirmation modal --}}
        <div class="modal fade" id="modal-reload-confirm" tabindex="-1" aria-labelledby="reloadModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-white text-dark">
                        <h5 class="modal-title" id="reloadModalLabel">
                            <i class="fa fa-rotate-right me-2"></i>Reload Camera App?
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2 small">This will send a <strong>reload</strong> command to the Camera Controls
                            desktop app via Pusher. The app will refresh immediately.</p>
                        <div id="reload-modal-status" class="d-none alert mb-0 py-2 small"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="btn-confirm-reload">
                            <i class="fa fa-rotate-right me-1"></i> Reload Now
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
        <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
        <script>
            (function() {
                'use strict';

                // ── Config ────────────────────────────────────────────────
                const PUSHER_KEY = 'a1e22a2a180cb0de8d72';
                const PUSHER_CLUSTER = 'ap1';
                const CHANNEL_NAME = 'camera-control';
                const STATUS_URL = '/api/monitor/status';
                const POLL_MS = 5000;

                // ── Helpers ───────────────────────────────────────────────
                function setBadge(id, cls, text) {
                    const el = document.getElementById(id);
                    if (!el) return;
                    el.className = 'mon-badge ' + cls;
                    el.textContent = text;
                }

                function setDetail(id, html) {
                    const el = document.getElementById(id);
                    if (el) el.innerHTML = html;
                }

                function setCard(id, cls) {
                    const el = document.getElementById(id);
                    if (el) {
                        el.className = 'mon-card ' + (cls || '');
                    }
                }

                function fmt(ts) {
                    if (!ts) return '—';
                    const d = new Date(ts * 1000);
                    const diff = Math.round((Date.now() - d.getTime()) / 1000);
                    if (diff < 60) return diff + 's ago';
                    if (diff < 3600) return Math.round(diff / 60) + 'm ago';
                    return d.toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }

                function now() {
                    return new Date().toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
                }

                // ── Event Log ─────────────────────────────────────────────
                const logEl = document.getElementById('mon-log');
                let logCount = 0;

                function addLog(event, detail, highlight) {
                    logCount++;
                    if (logEl.children.length === 1 && logEl.children[0].querySelector('.log-time').textContent === '—') {
                        logEl.innerHTML = '';
                    }
                    const li = document.createElement('li');
                    if (highlight) li.classList.add('highlight');
                    li.innerHTML = '<span class="log-time">' + now() + '</span>' +
                        '<span class="log-event"><strong>' + event + '</strong>' +
                        (detail ? ' — ' + detail : '') + '</span>';
                    logEl.prepend(li);
                    // Cap at 50 entries
                    while (logEl.children.length > 50) logEl.removeChild(logEl.lastChild);
                }
                document.getElementById('btn-clear-log').addEventListener('click', function() {
                    logEl.innerHTML =
                        '<li><span class="log-time">—</span><span class="log-event">Log cleared</span></li>';
                });

                // ── Pusher ────────────────────────────────────────────────
                const pusher = new Pusher(PUSHER_KEY, {
                    cluster: PUSHER_CLUSTER
                });
                const channel = pusher.subscribe(CHANNEL_NAME);

                pusher.connection.bind('connecting', function() {
                    setBadge('badge-pusher', 'idle pulse', 'Connecting…');
                    setCard('card-pusher', '');
                });
                pusher.connection.bind('connected', function() {
                    setBadge('badge-pusher', 'ok', '● Connected');
                    setDetail('detail-pusher', 'Channel: <code>' + CHANNEL_NAME + '</code> · socket ' + pusher
                        .connection.socket_id);
                    setCard('card-pusher', 'ok');
                    addLog('Pusher connected', 'socket ' + pusher.connection.socket_id, true);
                    // Ask the desktop app to re-send its current status
                    fetch('/api/monitor/broadcast', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            event: 'request_state',
                            data: {}
                        })
                    }).catch(function() {});
                });
                pusher.connection.bind('disconnected', function() {
                    setBadge('badge-pusher', 'fail', '✕ Disconnected');
                    setCard('card-pusher', 'fail');
                    addLog('Pusher disconnected');
                });
                pusher.connection.bind('failed', function() {
                    setBadge('badge-pusher', 'fail', '✕ Failed');
                    setCard('card-pusher', 'fail');
                    addLog('Pusher connection failed');
                });

                // ── Handle camera-control events ─────────────────────────
                // The Tauri app sends events on this channel; we map known ones to status cards.
                channel.bind_global(function(eventName, data) {
                    // Skip pusher internal events
                    if (eventName.startsWith('pusher:')) return;

                    const detail = typeof data === 'object' ? JSON.stringify(data) : String(data || '');
                    addLog(eventName, detail, true);

                    const ev = eventName.toLowerCase();

                    // OBS status
                    if (ev.includes('obs:connected') || ev === 'obs_connected') {
                        setBadge('badge-obs', 'ok pulse', '● Connected');
                        setDetail('detail-obs', 'OBS WebSocket connected');
                        setCard('card-obs', 'ok');
                    } else if (ev.includes('obs:disconnected') || ev === 'obs_disconnected') {
                        setBadge('badge-obs', 'fail', '✕ Disconnected');
                        setDetail('detail-obs', 'OBS WebSocket disconnected');
                        setCard('card-obs', 'fail');
                    }

                    // Camera (DigiCam / OBS Capture)
                    if (ev.includes('camera:connected') || ev === 'camera_connected') {
                        const srcLabel = (data?.source === 'obs') ? 'OBS Capture' : 'DigiCamControl';
                        setBadge('badge-cam', 'ok', '● Connected');
                        setDetail('detail-cam', data?.name ? srcLabel + ' · ' + data.name : srcLabel +
                            ' connected');
                        setCard('card-cam', 'ok');
                    } else if (ev.includes('camera:disconnected') || ev === 'camera_disconnected') {
                        const srcLabel = (data?.source === 'obs') ? 'OBS Capture' : 'DigiCamControl';
                        setBadge('badge-cam', 'fail', '✕ Disconnected');
                        setDetail('detail-cam', srcLabel + ' disconnected');
                        setCard('card-cam', 'fail');
                    }

                    // Live feed
                    if (ev === 'feed_started') {
                        setBadge('badge-obs', 'ok pulse', '● Feed Active');
                        setDetail('detail-obs', data?.device ? 'Source: ' + data.device : 'Live feed running');
                        setCard('card-obs', 'ok');
                    } else if (ev === 'feed_stopped') {
                        setBadge('badge-obs', 'warn', '◌ Feed Off');
                        setDetail('detail-obs', 'Live feed stopped');
                        setCard('card-obs', 'warn');
                    }

                    // Recording
                    if (ev.includes('recording:started') || ev === 'recording_started') {
                        setBadge('badge-recording', 'fail pulse', '● Recording');
                        setDetail('detail-recording', 'Started at ' + now());
                        setCard('card-recording', 'fail');
                    } else if (ev.includes('recording:stopped') || ev === 'recording_stopped') {
                        setBadge('badge-recording', 'ok', '✓ Stopped');
                        setDetail('detail-recording', 'Stopped at ' + now());
                        setCard('card-recording', 'ok');
                    }

                    // Audio source
                    if (ev === 'audio_source_ok') {
                        setBadge('badge-audio', 'ok pulse', '● Playing');
                        setDetail('detail-audio', data?.source ? 'Source: ' + data.source : 'Audio triggered OK');
                        setCard('card-audio', 'ok');
                    } else if (ev === 'audio_source_fail') {
                        setBadge('badge-audio', 'fail', '✕ Failed');
                        setDetail('detail-audio', (data?.source ? data.source + ' — ' : '') + (data?.error ||
                            'Trigger failed'));
                        setCard('card-audio', 'fail');
                    } else if (ev === 'audio_source_none') {
                        setBadge('badge-audio', 'warn', '◌ Not Set');
                        setDetail('detail-audio', 'No media source configured');
                        setCard('card-audio', 'warn');
                    } else if (ev === 'audio_configured') {
                        if (data?.source) {
                            setBadge('badge-audio', 'idle', '○ Configured');
                            setDetail('detail-audio', 'Source: ' + data.source + ' (plays on record start)');
                            setCard('card-audio', '');
                        } else {
                            setBadge('badge-audio', 'warn', '◌ Not Set');
                            setDetail('detail-audio', 'No media source configured in Settings → OBS');
                            setCard('card-audio', 'warn');
                        }
                    }

                    // Capture / upload
                    if (ev.includes('capture') || ev.includes('photo') || ev.includes('upload')) {
                        // Trigger a quick status refresh so counts update
                        fetchStatus();
                    }
                });

                // ── Server status polling ─────────────────────────────────
                function fetchStatus() {
                    fetch(STATUS_URL)
                        .then(function(r) {
                            if (!r.ok) throw new Error('HTTP ' + r.status);
                            return r.json();
                        })
                        .then(function(data) {
                            // ── Hydrate connection state from cache (survives page reload) ──
                            const ms = data.monitor_state || {};
                            if (ms.camera === 'connected') {
                                const srcLabel = (ms.camera_source === 'obs') ? 'OBS Capture' : 'DigiCamControl';
                                setBadge('badge-cam', 'ok', '● Connected');
                                setDetail('detail-cam', ms.camera_name ? srcLabel + ' · ' + ms.camera_name :
                                    srcLabel + ' connected');
                                setCard('card-cam', 'ok');
                            } else if (ms.camera === 'disconnected') {
                                const srcLabel = (ms.camera_source === 'obs') ? 'OBS Capture' : 'DigiCamControl';
                                setBadge('badge-cam', 'fail', '✕ Disconnected');
                                setDetail('detail-cam', srcLabel + ' disconnected');
                                setCard('card-cam', 'fail');
                            }
                            if (ms.obs === 'connected') {
                                setBadge('badge-obs', 'ok pulse', '● Connected');
                                setDetail('detail-obs', ms.obs_scene ? 'Scene: ' + ms.obs_scene :
                                    'OBS WebSocket connected');
                                setCard('card-obs', 'ok');
                            } else if (ms.obs === 'disconnected') {
                                setBadge('badge-obs', 'fail', '✕ Disconnected');
                                setDetail('detail-obs', 'OBS WebSocket disconnected');
                                setCard('card-obs', 'fail');
                            }
                            if (ms.feed === 'started') {
                                setBadge('badge-obs', 'ok pulse', '● Feed Active');
                                setDetail('detail-obs', ms.feed_device ? 'Source: ' + ms.feed_device :
                                    'Live feed running');
                                setCard('card-obs', 'ok');
                            } else if (ms.feed === 'stopped') {
                                if (ms.obs !== 'connected') {
                                    setBadge('badge-obs', 'warn', '◌ Feed Off');
                                    setDetail('detail-obs', 'Live feed stopped');
                                    setCard('card-obs', 'warn');
                                }
                            }
                            if (ms.recording === 'started') {
                                setBadge('badge-recording', 'fail pulse', '● Recording');
                                setCard('card-recording', 'fail');
                            } else if (ms.recording === 'stopped') {
                                setBadge('badge-recording', 'ok', '✓ Stopped');
                                setCard('card-recording', 'ok');
                            }

                            // Audio source hydration
                            if (ms.audio_last_trigger === 'ok') {
                                setBadge('badge-audio', 'ok', '● Playing');
                                setDetail('detail-audio', ms.audio_source ? 'Source: ' + ms.audio_source :
                                    'Audio triggered OK');
                                setCard('card-audio', 'ok');
                            } else if (ms.audio_last_trigger === 'fail') {
                                setBadge('badge-audio', 'fail', '✕ Failed');
                                setDetail('detail-audio', (ms.audio_source ? ms.audio_source + ' — ' : '') + (ms
                                    .audio_last_error || 'Trigger failed'));
                                setCard('card-audio', 'fail');
                            } else if (ms.audio_last_trigger === 'none' || (ms.audio_source === '' && ms
                                    .audio_last_trigger !== undefined)) {
                                setBadge('badge-audio', 'warn', '◌ Not Set');
                                setDetail('detail-audio', 'No media source configured in Settings → OBS');
                                setCard('card-audio', 'warn');
                            } else if (ms.audio_source) {
                                setBadge('badge-audio', 'idle', '○ Configured');
                                setDetail('detail-audio', 'Source: ' + ms.audio_source + ' (plays on record start)');
                                setCard('card-audio', '');
                            }

                            // Server time
                            const st = document.getElementById('mon-server-time');
                            if (st) st.textContent = new Date(data.server_time).toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit',
                                second: '2-digit'
                            });

                            // Photos
                            document.getElementById('stat-photos').textContent = data.photos.count;
                            setDetail('detail-photo-latest', data.photos.latest_ts ?
                                'Latest: ' + fmt(data.photos.latest_ts) + (data.photos.latest_url ? ' · <a href="' +
                                    data.photos.latest_url + '" target="_blank">view</a>' : '') :
                                'No photos yet');
                            setCard('card-photos', data.photos.count > 0 ? 'ok' : '');

                            // Videos
                            document.getElementById('stat-videos').textContent = data.videos.count;
                            setDetail('detail-video-latest', data.videos.latest_ts ?
                                'Latest: ' + fmt(data.videos.latest_ts) + (data.videos.latest_url ? ' · <a href="' +
                                    data.videos.latest_url + '" target="_blank">view</a>' : '') :
                                'No videos yet');
                            setCard('card-videos', data.videos.count > 0 ? 'ok' : '');

                            // API badge
                            setBadge('badge-api', 'ok', '● Online');
                            setDetail('detail-api', 'Last poll: ' + now());
                            setCard('card-server-api', 'ok');
                        })
                        .catch(function(err) {
                            setBadge('badge-api', 'fail', '✕ Error');
                            setDetail('detail-api', err.message);
                            setCard('card-server-api', 'fail');
                            addLog('API poll failed', err.message);
                        });
                }

                fetchStatus();
                setInterval(fetchStatus, POLL_MS);

                // ── Trigger reload ────────────────────────────────────────
                document.getElementById('btn-confirm-reload').addEventListener('click', async function() {
                    const btn = this;
                    const statusEl = document.getElementById('reload-modal-status');
                    btn.disabled = true;
                    btn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Sending…';
                    statusEl.className = 'd-none alert mb-0 py-2 small';

                    try {
                        const res = await fetch('/api/monitor/broadcast', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                            },
                            body: JSON.stringify({
                                event: 'reload',
                                data: {}
                            }),
                        });
                        const json = await res.json();
                        if (res.ok && json.ok) {
                            statusEl.className = 'alert alert-success mb-0 py-2 small';
                            statusEl.textContent = '✓ Reload command sent.';
                            addLog('Reload trigger sent', '', true);
                        } else {
                            throw new Error(json.error || 'Unknown error');
                        }
                    } catch (err) {
                        statusEl.className = 'alert alert-danger mb-0 py-2 small';
                        statusEl.textContent = '✗ Failed: ' + err.message;
                        addLog('Reload trigger failed', err.message);
                    } finally {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-rotate-right me-1"></i> Reload Now';
                    }
                });

                document.getElementById('btn-refresh-status').addEventListener('click', function() {
                    fetchStatus();
                    addLog('Manual status refresh');
                });

            })();
        </script>
    @endpush
</x-app-layout>
