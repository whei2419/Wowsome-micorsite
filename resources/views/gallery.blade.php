<x-guest-layout>
    @php
        $files = \Illuminate\Support\Facades\Storage::disk('public')->files('captures');

        usort($files, function ($a, $b) {
            return \Illuminate\Support\Facades\Storage::disk('public')->lastModified($b) <=>
                \Illuminate\Support\Facades\Storage::disk('public')->lastModified($a);
        });

        $items = array_map(function ($path) {
            return [
                'url' => \Illuminate\Support\Facades\Storage::disk('public')->url($path),
                'download' => url('/captures/download?file=' . rawurlencode($path)),
            ];
        }, $files);
    @endphp

    <style>
        * {
            font-family: 'PlusJakartaSans', sans-serif;
        }

        .gallery-page {
            min-height: 100vh;
            padding: 2rem 1.25rem;
            background: linear-gradient(180deg, rgba(7, 12, 24, 0.78), rgba(7, 12, 24, 0.92));
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
    </style>

    <div class="gallery-page">
        <div class="gallery-wrap">
            <div class="gallery-top">
                <h1 class="gallery-title">Capture Gallery</h1>
                <a href="{{ route('start') }}" class="gallery-back">Back</a>
            </div>

            @if (count($items))
                <div class="gallery-grid">
                    @foreach ($items as $item)
                        <div class="gallery-item" data-url="{{ $item['url'] }}" data-download="{{ $item['download'] }}"
                            role="button" tabindex="0" aria-label="View and share photo">
                            <img src="{{ $item['url'] }}" alt="Captured photo" loading="lazy" decoding="async" />
                            <span class="gallery-item__share" aria-hidden="true">
                                <i class="fa-solid fa-qrcode"></i>
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="gallery-empty">No captures yet.</div>
            @endif
        </div>
    </div>

    {{-- QR Modal --}}
    <div class="qr-backdrop" id="qrBackdrop" role="dialog" aria-modal="true" aria-label="Share photo">
        <div class="qr-card">
            <button class="qr-card__close" id="qrClose" aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="qr-canvas-wrap" id="qrCanvas"></div>
            <p class="qr-label">Scan to download the photo on your phone</p>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"
        integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSi2jPvUfounding+CAkI7oS" crossorigin="anonymous"
        referrerpolicy="no-referrer"></script>
    <script>
        // Fallback to unpkg if the above fails
        if (typeof QRCode === 'undefined') {
            const s = document.createElement('script');
            s.src = 'https://unpkg.com/qrcodejs@1.0.0/qrcode.min.js';
            document.head.appendChild(s);
        }
    </script>
    <script>
        const backdrop = document.getElementById('qrBackdrop');
        const qrWrap = document.getElementById('qrCanvas');
        const closeBtn = document.getElementById('qrClose');

        function openQr(downloadUrl) {
            qrWrap.innerHTML = '';

            // Responsive: 72vw, capped at 320px, minimum 200px
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
                // Wait for fallback script
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

        document.querySelectorAll('.gallery-item').forEach(el => {
            el.addEventListener('click', () => openQr(el.dataset.download));
            el.addEventListener('keydown', e => {
                if (e.key === 'Enter' || e.key === ' ') openQr(el.dataset.download);
            });
        });

        closeBtn.addEventListener('click', closeQr);
        backdrop.addEventListener('click', e => {
            if (e.target === backdrop) closeQr();
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeQr();
        });
    </script>
</x-guest-layout>
