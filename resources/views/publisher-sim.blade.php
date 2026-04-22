<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Publisher Simulator</title>
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 24px;
            padding: 32px;
        }

        h1 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #fff;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #1e293b;
            padding: 6px 12px;
            border-radius: 99px;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: .04em;
        }

        .dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #475569;
            transition: background .3s;
        }

        .dot.ok {
            background: #4ade80;
        }

        .dot.err {
            background: #f87171;
        }

        /* preview */
        .preview-wrap {
            width: min(90vw, 360px);
            aspect-ratio: 9/16;
            background: #111827;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #475569;
            font-size: .9rem;
            position: relative;
        }

        .preview-wrap img,
        .preview-wrap video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .preview-wrap canvas {
            display: none;
        }

        /* log */
        .log {
            width: min(90vw, 440px);
            max-height: 200px;
            overflow-y: auto;
            background: #1e293b;
            border-radius: 8px;
            padding: 12px;
            font-family: monospace;
            font-size: 0.78rem;
            color: #94a3b8;
        }

        .log .entry {
            padding: 2px 0;
            border-bottom: 1px solid #334155;
        }

        .log .entry.ok {
            color: #4ade80;
        }

        .log .entry.err {
            color: #f87171;
        }

        .log .entry.info {
            color: #60a5fa;
        }

        /* buttons */
        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
        }

        button,
        label.btn {
            padding: 10px 22px;
            border-radius: 8px;
            font-weight: 700;
            font-size: .85rem;
            cursor: pointer;
            border: none;
            outline: none;
            transition: opacity .15s;
        }

        button:disabled {
            opacity: .4;
            cursor: not-allowed;
        }

        .btn-primary {
            background: #6366f1;
            color: #fff;
        }

        .btn-secondary {
            background: #334155;
            color: #e2e8f0;
        }

        input[type=file] {
            display: none;
        }
    </style>
</head>

<body>
    <h1>Publisher Simulator</h1>

    <div class="badge">
        <span class="dot" id="pdot"></span>
        <span id="pstatus">connecting…</span>
    </div>

    {{-- preview: webcam or image --}}
    <div class="preview-wrap" id="previewWrap">
        <video id="cameraFeed" autoplay playsinline muted></video>
        <img id="previewImg" src="" alt="" style="display:none;" />
        <canvas id="snapCanvas"></canvas>
        <span id="noPreview" style="display:none;">No source</span>
    </div>

    <div class="actions">
        <button class="btn-primary" id="btnCamera">Use Webcam</button>
        <label class="btn btn-secondary" for="fileInput">Pick Image</label>
        <input type="file" id="fileInput" accept="image/*" />
        <label class="btn btn-secondary" for="videoFileInput">Pick Video</label>
        <input type="file" id="videoFileInput" accept="video/*" />
        <button class="btn-primary" id="btnManual">Upload Now (manual)</button>
    </div>

    <div class="log" id="log">
        <div class="entry info">Ready.</div>
    </div>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        // ── helpers ──────────────────────────────────────────────────
        const logEl = document.getElementById('log');

        function log(msg, type = 'info') {
            const d = document.createElement('div');
            d.className = 'entry ' + type;
            d.textContent = '[' + new Date().toLocaleTimeString() + '] ' + msg;
            logEl.prepend(d);
        }

        const dot = document.getElementById('pdot');
        const status = document.getElementById('pstatus');

        // ── Webcam ───────────────────────────────────────────────────
        const videoEl = document.getElementById('cameraFeed');
        const previewImg = document.getElementById('previewImg');
        const canvas = document.getElementById('snapCanvas');
        let stream = null;
        let pickedFile = null; // File object from input

        document.getElementById('btnCamera').addEventListener('click', async () => {
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'environment'
                    },
                    audio: false
                });
                videoEl.srcObject = stream;
                videoEl.style.display = 'block';
                previewImg.style.display = 'none';
                pickedFile = null;
                log('Webcam started', 'ok');
            } catch (e) {
                log('Webcam error: ' + e.message, 'err');
            }
        });

        document.getElementById('fileInput').addEventListener('change', (e) => {
            const f = e.target.files[0];
            if (!f) return;
            pickedFile = f;
            pickedVideoFile = null;
            // stop webcam
            if (stream) {
                stream.getTracks().forEach(t => t.stop());
                stream = null;
            }
            videoEl.style.display = 'none';
            previewImg.src = URL.createObjectURL(f);
            previewImg.style.display = 'block';
            log('Image selected: ' + f.name, 'ok');
        });

        document.getElementById('videoFileInput').addEventListener('change', (e) => {
            const f = e.target.files[0];
            if (!f) return;
            pickedVideoFile = f;
            pickedFile = null;
            if (stream) {
                stream.getTracks().forEach(t => t.stop());
                stream = null;
            }
            previewImg.style.display = 'none';
            videoEl.src = URL.createObjectURL(f);
            videoEl.style.display = 'block';
            videoEl.muted = false;
            log('Video selected: ' + f.name, 'ok');
        });

        // ── Capture logic ─────────────────────────────────────────────
        let pickedVideoFile = null;

        function getBlob() {
            return new Promise((resolve, reject) => {
                if (pickedFile) {
                    resolve(pickedFile);
                    return;
                }
                if (stream && videoEl.readyState >= 2) {
                    canvas.width = videoEl.videoWidth || 720;
                    canvas.height = videoEl.videoHeight || 1280;
                    canvas.getContext('2d').drawImage(videoEl, 0, 0);
                    canvas.toBlob(b => b ? resolve(b) : reject('canvas empty'), 'image/jpeg', 0.92);
                    return;
                }
                reject('No webcam stream or file selected — click "Use Webcam" or "Pick Image" first.');
            });
        }

        async function doCapture(reason = 'manual') {
            log('Capture triggered (' + reason + ')…');
            let blob;
            try {
                blob = await getBlob();
            } catch (e) {
                log('Could not get image: ' + e, 'err');
                return;
            }

            const fd = new FormData();
            fd.append('file', blob, 'capture-' + Date.now() + '.jpg');

            try {
                const res = await fetch('/api/upload-capture', {
                    method: 'POST',
                    body: fd
                });
                const data = await res.json();
                if (data.ok) {
                    log('Uploaded → ' + data.url, 'ok');
                } else {
                    log('Upload failed: ' + JSON.stringify(data), 'err');
                }
            } catch (e) {
                log('Upload error: ' + e.message, 'err');
            }
        }

        async function doVideoCapture(reason = 'manual') {
            if (!pickedVideoFile) {
                log('No video selected — click "Pick Video" first.', 'err');
                return;
            }
            log('Video upload triggered (' + reason + ')…');
            const fd = new FormData();
            const ext = pickedVideoFile.name.split('.').pop() || 'mp4';
            fd.append('file', pickedVideoFile, 'video-' + Date.now() + '.' + ext);
            try {
                const res = await fetch('/api/upload-video', {
                    method: 'POST',
                    body: fd
                });
                const data = await res.json();
                if (data.ok) {
                    log('Video uploaded → ' + data.url, 'ok');
                } else {
                    log('Video upload failed: ' + JSON.stringify(data), 'err');
                }
            } catch (e) {
                log('Video upload error: ' + e.message, 'err');
            }
        }

        document.getElementById('btnManual').addEventListener('click', () => {
            if (pickedVideoFile) {
                doVideoCapture('manual');
            } else {
                doCapture('manual');
            }
        });

        // ── Pusher ────────────────────────────────────────────────────
        const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
            forceTLS: true,
        });

        pusher.connection.bind('connected', () => {
            dot.className = 'dot ok';
            status.textContent = 'connected';
            log('Pusher connected', 'ok');
        });
        pusher.connection.bind('disconnected', () => {
            dot.className = 'dot err';
            status.textContent = 'disconnected';
            log('Pusher disconnected', 'err');
        });
        pusher.connection.bind('error', (e) => {
            dot.className = 'dot err';
            log('Pusher error: ' + JSON.stringify(e), 'err');
        });

        const ch = pusher.subscribe('camera-control');
        ch.bind('capture', (data) => {
            const evtMode = data && data.mode ? data.mode : 'photo';
            log('← capture event received (mode: ' + evtMode + ')', 'info');
            if (evtMode === 'video') {
                doVideoCapture('pusher');
            } else {
                doCapture('pusher');
            }
        });
    </script>
</body>

</html>
