<x-guest-layout>
    @php
        $mode ??= 'qr';
        $showBack ??= false;
    @endphp

    <style>
        * {
            font-family: 'PlusJakartaSans', sans-serif;
        }

        .gallery-page {
            min-height: 100vh;
            padding: 2rem 1.25rem;
            background: url('{{ asset('images/brand/Vector.png') }}') center center / cover no-repeat fixed;
            color: #fff;
        }

        .gallery-wrap {
            width: min(1100px, 100%);
            margin: 0 auto;
        }

        .gallery-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.2rem;
            flex-wrap: wrap;
        }

        .gallery-title {
            margin: 0;
            font-size: clamp(1.2rem, 3vw, 1.8rem);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-weight: 800;
        }

        .gallery-back {
            text-decoration: none;
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 999px;
            padding: 0.55rem 1rem;
            font-weight: 700;
            font-size: 0.82rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: rgba(255, 255, 255, 0.08);
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 0.7rem;
        }

        .gallery-item {
            aspect-ratio: 9 / 16;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.06);
            cursor: pointer;
            position: relative;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .gallery-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .gallery-item__share {
            position: absolute;
            bottom: 8px;
            right: 8px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.92);
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            opacity: 0;
            transition: opacity 0.15s ease;
        }

        .gallery-item:hover .gallery-item__share {
            opacity: 1;
        }

        .gallery-empty {
            margin-top: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 14px;
            padding: 1.2rem;
            text-align: center;
            background: rgba(255, 255, 255, 0.06);
            font-weight: 600;
        }

        /* ── Toggle ── */
        .gallery-toggle {
            display: flex;
            gap: 0.35rem;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 999px;
            padding: 0.25rem;
        }

        .gallery-toggle__btn {
            padding: 0.45rem 1.1rem;
            border-radius: 999px;
            border: none;
            background: transparent;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .gallery-toggle__btn.is-active {
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
        }

        .gallery-toggle__btn:hover:not(.is-active) {
            color: rgba(255, 255, 255, 0.85);
        }

        /* ── Video items ── */
        .gallery-item video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            pointer-events: none;
        }

        .gallery-item__play {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: rgba(255, 255, 255, 0.85);
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
            transition: opacity 0.15s ease;
        }

        .gallery-item:hover .gallery-item__play {
            opacity: 0;
        }

        /* hidden section */
        .gallery-section {
            display: none;
        }

        .gallery-section.is-active {
            display: block;
        }

        .gallery-loading {
            margin-top: 1.5rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.9rem;
            letter-spacing: 0.06em;
        }

        /* ── QR Modal ── */
        .qr-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 12, 0.8);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .qr-backdrop.is-open {
            display: flex;
        }

        .qr-card {
            background: #fff;
            border-radius: 22px;
            padding: 2rem 1.75rem 1.5rem;
            text-align: center;
            width: min(92vw, 380px);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.45);
            position: relative;
        }

        .qr-card__close {
            position: absolute;
            top: 0.75rem;
            right: 0.85rem;
            width: 34px;
            height: 34px;
            border: none;
            border-radius: 50%;
            background: #f1f5f9;
            color: #374151;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-card__close:hover {
            background: #e2e8f0;
        }

        .qr-canvas-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .qr-canvas-wrap canvas,
        .qr-canvas-wrap img {
            border-radius: 8px;
        }

        .qr-label {
            font-size: 0.8rem;
            color: #6b7280;
            margin-bottom: 1rem;
            line-height: 1.4;
        }

        /* ── Print Modal ── */
        .print-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 12, 0.85);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .print-backdrop.is-open {
            display: flex;
        }

        .print-card {
            background: #fff;
            border-radius: 22px;
            padding: 1.5rem 1.5rem 1.25rem;
            text-align: center;
            width: min(92vw, 400px);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.45);
            position: relative;
        }

        .print-card__close {
            position: absolute;
            top: 0.75rem;
            right: 0.85rem;
            width: 34px;
            height: 34px;
            border: none;
            border-radius: 50%;
            background: #f1f5f9;
            color: #374151;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .print-card__close:hover {
            background: #e2e8f0;
        }

        .print-preview {
            width: 100%;
            aspect-ratio: 9 / 16;
            border-radius: 12px;
            overflow: hidden;
            background: #f1f5f9;
            margin-bottom: 1rem;
        }

        .print-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .print-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
        }

        .print-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.7rem 1.75rem;
            border-radius: 999px;
            border: none;
            background: #0f172a;
            color: #fff;
            font-weight: 700;
            font-size: 0.9rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.15s ease;
        }

        .print-btn:hover {
            background: #1e293b;
            transform: translateY(-1px);
        }

        .print-btn:active {
            transform: scale(0.97);
        }

        /* ── Print-only area ── */
        #printArea {
            display: none;
            position: absolute;
            width: 0;
            height: 0;
            overflow: hidden;
            pointer-events: none;
        }

        @media print {
            * {
                visibility: hidden;
            }

            #printArea,
            #printArea * {
                visibility: visible;
            }

            #printArea {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                display: flex !important;
                align-items: center;
                justify-content: center;
                background: white;
            }

            #printArea img {
                max-width: 100%;
                max-height: 100vh;
                object-fit: contain;
            }
        }
    </style>

    <div class="gallery-page">
        <div class="gallery-wrap">
            <div class="gallery-top">
                <h1 class="gallery-title">Gallery
                    @if ($mode === 'printer')
                        <span
                            style="font-size:0.55em;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);border-radius:999px;padding:0.2em 0.7em;letter-spacing:0.05em;vertical-align:middle;margin-left:0.5em;">PRINTER</span>
                    @endif
                </h1>
                <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
                    <div class="gallery-toggle" role="group" aria-label="Media type">
                        <button class="gallery-toggle__btn is-active" id="btnShowPhotos" aria-pressed="true">
                            <i class="fa-solid fa-image" aria-hidden="true"></i> Photos
                        </button>
                        <button class="gallery-toggle__btn" id="btnShowVideos" aria-pressed="false"
                            {!! $mode === 'printer' ? 'style="display:none"' : '' !!}>
                            <i class="fa-solid fa-film" aria-hidden="true"></i> Videos
                        </button>
                    </div>
                    @if ($showBack)
                        <a href="{{ route('start') }}" class="gallery-back">Back</a>
                    @endif
                </div>
            </div>

            {{-- ── Photos ── --}}
            <div class="gallery-section is-active" id="sectionPhotos">
                <div class="gallery-loading" id="loadingPhotos">Loading...</div>
            </div>

            {{-- ── Videos ── --}}
            <div class="gallery-section" id="sectionVideos">
                <div class="gallery-loading" id="loadingVideos">Loading...</div>
            </div>
        </div>
    </div>

    {{-- QR Modal --}}
    <div class="qr-backdrop" id="qrBackdrop" role="dialog" aria-modal="true" aria-label="Share photo">
        <div class="qr-card">
            <button class="qr-card__close" id="qrClose" aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="qr-canvas-wrap" id="qrCanvas"></div>
            <p class="qr-label" id="qrLabel">Scan to download on your phone</p>
        </div>
    </div>

    {{-- Print Modal --}}
    <div class="print-backdrop" id="printBackdrop" role="dialog" aria-modal="true" aria-label="Print photo">
        <div class="print-card">
            <button class="print-card__close" id="printClose" aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="print-preview">
                <img id="printPreviewImg" src="" alt="Print preview" />
            </div>
            <div class="print-actions">
                <button class="print-btn" id="printBtn">
                    <i class="fa-solid fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>

    {{-- Hidden area rendered during window.print() --}}
    <div id="printArea" aria-hidden="true">
        <img id="printAreaImg" src="" alt="" />
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"
        integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSi2jPvUfounding+CAkI7oS" crossorigin="anonymous"
        referrerpolicy="no-referrer"></script>
    <script>
        if (typeof QRCode === 'undefined') {
            const s = document.createElement('script');
            s.src = 'https://unpkg.com/qrcodejs@1.0.0/qrcode.min.js';
            document.head.appendChild(s);
        }
    </script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        const GALLERY_MODE = '{{ $mode }}';

        // ── Toggle ────────────────────────────────────────────────────
        const btnPhotos = document.getElementById('btnShowPhotos');
        const btnVideos = document.getElementById('btnShowVideos');
        const sectionPhotos = document.getElementById('sectionPhotos');
        const sectionVideos = document.getElementById('sectionVideos');

        function showTab(tab) {
            const isPhotos = tab === 'photos';
            btnPhotos.classList.toggle('is-active', isPhotos);
            btnVideos.classList.toggle('is-active', !isPhotos);
            btnPhotos.setAttribute('aria-pressed', isPhotos ? 'true' : 'false');
            btnVideos.setAttribute('aria-pressed', isPhotos ? 'false' : 'true');
            sectionPhotos.classList.toggle('is-active', isPhotos);
            sectionVideos.classList.toggle('is-active', !isPhotos);
        }

        btnPhotos.addEventListener('click', () => showTab('photos'));
        btnVideos.addEventListener('click', () => showTab('videos'));

        // ── QR Modal ─────────────────────────────────────────────────
        const backdrop = document.getElementById('qrBackdrop');
        const qrWrap = document.getElementById('qrCanvas');
        const closeBtn = document.getElementById('qrClose');
        const qrLabel = document.getElementById('qrLabel');

        function openQr(downloadUrl, type) {
            qrWrap.innerHTML = '';
            qrLabel.textContent = type === 'video' ?
                'Scan to download the video on your phone' :
                'Scan to download the photo on your phone';
            const qrSize = Math.min(320, Math.max(200, Math.round(window.innerWidth * 0.72)));

            function generate() {
                new QRCode(qrWrap, {
                    text: downloadUrl,
                    width: qrSize,
                    height: qrSize,
                    colorDark: '#0f172a',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M,
                });
            }
            if (typeof QRCode !== 'undefined') {
                generate();
            } else {
                const iv = setInterval(() => {
                    if (typeof QRCode !== 'undefined') {
                        clearInterval(iv);
                        generate();
                    }
                }, 80);
            }
            backdrop.classList.add('is-open');
            closeBtn.focus();
        }

        function closeQr() {
            backdrop.classList.remove('is-open');
            qrWrap.innerHTML = '';
        }

        closeBtn.addEventListener('click', closeQr);
        backdrop.addEventListener('click', e => {
            if (e.target === backdrop) closeQr();
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                closeQr();
                closePrint();
            }
        });

        // ── Print Modal ───────────────────────────────────────────────
        const printBackdrop = document.getElementById('printBackdrop');
        const printClose = document.getElementById('printClose');
        const printPreviewImg = document.getElementById('printPreviewImg');
        const printAreaImg = document.getElementById('printAreaImg');
        const printBtn = document.getElementById('printBtn');

        function openPrint(url) {
            printPreviewImg.src = url;
            printAreaImg.src = url;
            printBackdrop.classList.add('is-open');
            printClose.focus();
        }

        function closePrint() {
            printBackdrop.classList.remove('is-open');
            printPreviewImg.src = '';
            printAreaImg.src = '';
        }

        printClose.addEventListener('click', closePrint);
        printBackdrop.addEventListener('click', e => {
            if (e.target === printBackdrop) closePrint();
        });
        printBtn.addEventListener('click', () => window.print());

        // ── Build gallery item elements ───────────────────────────────
        function bindItemEvents(el, type) {
            if (GALLERY_MODE === 'printer') {
                el.addEventListener('click', () => openPrint(el.dataset.url));
                el.addEventListener('keydown', e => {
                    if (e.key === 'Enter' || e.key === ' ') openPrint(el.dataset.url);
                });
            } else {
                el.addEventListener('click', () => openQr(el.dataset.download, type));
                el.addEventListener('keydown', e => {
                    if (e.key === 'Enter' || e.key === ' ') openQr(el.dataset.download, type);
                });
            }
        }

        function bindVideoHover(el) {
            const vid = el.querySelector('video');
            if (!vid) return;
            el.addEventListener('mouseenter', () => vid.play().catch(() => {}));
            el.addEventListener('mouseleave', () => {
                vid.pause();
                vid.currentTime = 0;
            });
        }

        function buildPhotoItem(item) {
            const div = document.createElement('div');
            div.className = 'gallery-item';
            div.dataset.url = item.url;
            div.dataset.download = item.download;
            div.setAttribute('role', 'button');
            div.setAttribute('tabindex', '0');
            div.setAttribute('aria-label', GALLERY_MODE === 'printer' ? 'Print photo' : 'View and share photo');
            const img = document.createElement('img');
            img.src = item.url;
            img.alt = 'Captured photo';
            img.loading = 'lazy';
            img.decoding = 'async';
            const share = document.createElement('span');
            share.className = 'gallery-item__share';
            share.setAttribute('aria-hidden', 'true');
            share.innerHTML = GALLERY_MODE === 'printer' ? '<i class="fa-solid fa-print"></i>' :
                '<i class="fa-solid fa-qrcode"></i>';
            div.appendChild(img);
            div.appendChild(share);
            bindItemEvents(div, 'photo');
            return div;
        }

        function buildVideoItem(item) {
            const div = document.createElement('div');
            div.className = 'gallery-item';
            div.dataset.url = item.url;
            div.dataset.download = item.download;
            div.setAttribute('role', 'button');
            div.setAttribute('tabindex', '0');
            div.setAttribute('aria-label', GALLERY_MODE === 'printer' ? 'Print photo' : 'View and share video');
            const vid = document.createElement('video');
            vid.src = item.url;
            vid.muted = true;
            vid.playsInline = true;
            vid.preload = 'metadata';
            vid.loop = true;
            const play = document.createElement('span');
            play.className = 'gallery-item__play';
            play.setAttribute('aria-hidden', 'true');
            play.innerHTML = '<i class="fa-solid fa-circle-play"></i>';
            const share = document.createElement('span');
            share.className = 'gallery-item__share';
            share.setAttribute('aria-hidden', 'true');
            share.innerHTML = GALLERY_MODE === 'printer' ? '<i class="fa-solid fa-print"></i>' :
                '<i class="fa-solid fa-qrcode"></i>';
            div.appendChild(vid);
            div.appendChild(play);
            div.appendChild(share);
            bindItemEvents(div, 'video');
            bindVideoHover(div);
            return div;
        }

        // ── Render a full section ─────────────────────────────────────
        function renderSection(section, items, type) {
            section.innerHTML = '';
            if (!items || items.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'gallery-empty';
                empty.textContent = type === 'video' ? 'No videos yet.' : 'No photos yet.';
                section.appendChild(empty);
                return;
            }
            const grid = document.createElement('div');
            grid.className = 'gallery-grid';
            items.forEach(item => grid.appendChild(type === 'video' ? buildVideoItem(item) : buildPhotoItem(item)));
            section.appendChild(grid);
        }

        // ── Prepend a single new item (from WebSocket) ────────────────
        function prependItem(section, item, type) {
            const empty = section.querySelector('.gallery-empty, .gallery-loading');
            if (empty) section.innerHTML = '';
            let grid = section.querySelector('.gallery-grid');
            if (!grid) {
                grid = document.createElement('div');
                grid.className = 'gallery-grid';
                section.appendChild(grid);
            }
            const el = type === 'video' ? buildVideoItem(item) : buildPhotoItem(item);
            grid.insertBefore(el, grid.firstChild);
        }

        // ── Derive download URL from a storage URL ────────────────────
        function storageUrlToDownload(url, type) {
            const marker = '/storage/';
            const idx = url.indexOf(marker);
            if (idx === -1) return url;
            const filePath = url.substring(idx + marker.length);
            return type === 'video' ?
                '/videos/download?file=' + encodeURIComponent(filePath) :
                '/captures/download?file=' + encodeURIComponent(filePath);
        }

        // ── Load gallery via AJAX (no page reload) ────────────────────
        fetch('{{ route('gallery.items') }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.ok ? r.json() : Promise.reject(r.status))
            .then(data => {
                renderSection(sectionPhotos, data.photos, 'photo');
                if (GALLERY_MODE !== 'printer') renderSection(sectionVideos, data.videos, 'video');
            })
            .catch(() => {
                renderSection(sectionPhotos, [], 'photo');
                if (GALLERY_MODE !== 'printer') renderSection(sectionVideos, [], 'video');
            });

        // ── Pusher WebSocket — live updates ───────────────────────────
        const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
            cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
            forceTLS: true,
        });

        const galleryChannel = pusher.subscribe('camera-control');

        galleryChannel.bind('capture:uploaded', function(data) {
            const download = storageUrlToDownload(data.url, 'photo');
            prependItem(sectionPhotos, {
                url: data.url,
                download
            }, 'photo');
        });

        galleryChannel.bind('video:uploaded', function(data) {
            if (GALLERY_MODE === 'printer') return;
            const download = storageUrlToDownload(data.url, 'video');
            prependItem(sectionVideos, {
                url: data.url,
                download
            }, 'video');
        });
    </script>
</x-guest-layout>
