<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Live Lantern Feed</title>
    <script src="https://js.pusher.com/8.2/pusher.min.js"></script>
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }

        body {
            font-family: monospace;
            background-color: #000;
            color: #e5e7eb;
            overflow: visible;
        }

        #bg-video {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
        }

        #pixi-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        .lantern-gif {
            position: fixed;
            pointer-events: none;
            transform-origin: top center;
            filter: drop-shadow(0 0 20px rgba(255, 200, 100, 0.8)) drop-shadow(0 0 40px rgba(255, 150, 50, 0.6));
        }
    </style>
</head>

<body>
    <video id="bg-video" autoplay loop muted playsinline preload="auto"
        src="{{ asset('assets/videos/WhatsApp Video 2026-01-22 at 16.43.04.mp4') }}"></video>
    <div id="pixi-container"></div>
    <script src="https://cdn.jsdelivr.net/npm/pixi.js@7.4.2/dist/pixi.min.js"></script>
    <script>
        window.addEventListener('load', function() {
            setTimeout(initApp, 200);
        });

        function initApp() {
            if (typeof PIXI === 'undefined') {
                console.error('PIXI not loaded');
                return;
            }
            console.log('✅ PIXI loaded');

            const pusher = new Pusher('10c6dc9fb8040abd25e2', {
                cluster: 'ap1',
                forceTLS: true
            });
            const channel = pusher.subscribe('uploads');
            const container = document.getElementById('pixi-container');
            if (!container) {
                console.error('Container not found');
                return;
            }

            const app = new PIXI.Application({
                width: container.clientWidth,
                height: container.clientHeight,
                backgroundAlpha: 0,
                resolution: window.devicePixelRatio || 1,
                autoDensity: true
            });

            container.appendChild(app.view);
            app.view.style.position = 'absolute';
            app.view.style.width = '100%';
            app.view.style.height = '100%';

            window.addEventListener('resize', function() {
                app.renderer.resize(container.clientWidth, container.clientHeight);
            });

            const MAX_DISPLAY = 10;
            let lanternQueue = [];
            let displayedSprites = [];

            function spawnLanternSprite(lanternData) {
                const url = lanternData.image_url || lanternData.url;
                if (!url) {
                    console.warn('No URL', lanternData);
                    return;
                }

                console.log('🏮 Spawning lantern:', url);

                // Create HTML img element for GIF support
                const img = document.createElement('img');
                img.src = url;
                img.className = 'lantern-gif';

                const scale = 0.3 + Math.random() * 0.6; // 0.3 to 0.9 - more variety
                const baseSize = 300; // Approximate lantern size
                img.style.width = (baseSize * scale) + 'px';
                img.style.height = 'auto';
                img.style.zIndex = '10';

                const startX = 50 + Math.random() * (window.innerWidth - 100);
                const startY = window.innerHeight + 100;
                const speed = 0.8 + Math.random() * 1.2;

                img.style.left = (startX - (baseSize * scale / 2)) + 'px';
                img.style.top = startY + 'px';

                document.body.appendChild(img);

                displayedSprites.push({
                    element: img,
                    x: startX,
                    y: startY,
                    speed,
                    startX,
                    scale,
                    baseSize
                });

                if (displayedSprites.length > MAX_DISPLAY) {
                    const old = displayedSprites.shift();
                    if (old && old.element && old.element.parentNode) {
                        old.element.remove();
                    }
                }
            }

            app.ticker.add((delta) => {
                for (let i = displayedSprites.length - 1; i >= 0; i--) {
                    const item = displayedSprites[i];
                    if (!item || !item.element) continue;

                    // Move upward
                    item.y -= item.speed * delta;

                    // Pendulum sway - rotation creates the swing at bottom
                    // Stronger sway at bottom, gentler at top
                    const heightRatio = item.y / window.innerHeight; // 1 at bottom, 0 at top
                    const swayFactor = 0.3 + (1 - heightRatio) * 0.7; // Range: 0.3 to 1.0
                    const time = Date.now() / 1000;

                    // Keep x position fixed, only rotate (anchor is at top-center via transform-origin)
                    const rotation = Math.sin(time * 1.2 + i) * 0.25 * swayFactor;

                    item.element.style.left = (item.startX - (item.baseSize * item.scale / 2)) + 'px';
                    item.element.style.top = item.y + 'px';
                    item.element.style.transform = `rotate(${rotation}rad)`;

                    if (item.y < -200) {
                        if (item.element && item.element.parentNode) {
                            item.element.remove();
                        }
                        displayedSprites.splice(i, 1);
                    }
                }
            });

            // Fetch newest uploads on load
            function fetchNewestUploads() {
                console.log('📡 Fetching newest uploads from /api/lantern/latest...');
                fetch('/api/lantern/latest', {
                        credentials: 'same-origin'
                    })
                    .then(r => {
                        console.log('📥 Response status:', r.status, r.statusText);
                        return r.ok ? r.json() : Promise.reject(r);
                    })
                    .then(data => {
                        console.log('✅ Fetched data:', data);
                        const uploads = Array.isArray(data) ? data : [data];
                        console.log('📥 Loaded', uploads.length, 'existing lantern(s)');

                        uploads.slice(0, MAX_DISPLAY).forEach((upload, index) => {
                            lanternQueue.push(upload);
                            setTimeout(() => spawnLanternSprite(upload), index * 300);
                        });
                    })
                    .catch(err => {
                        console.error('❌ Could not fetch existing lanterns:', err);
                        showPlaceholder();
                    });
            }

            function showPlaceholder() {
                const text = new PIXI.Text('🏮 Waiting for lanterns...', {
                    fontFamily: 'monospace',
                    fontSize: 24,
                    fill: 0xffffff,
                    alpha: 0.3
                });
                text.anchor.set(0.5);
                text.x = app.renderer.width / 2;
                text.y = app.renderer.height / 2;
                app.stage.addChild(text);

                // Remove placeholder after 10 seconds or when first lantern arrives
                setTimeout(() => {
                    if (text.parent) text.destroy();
                }, 10000);
            }

            channel.bind('image.uploaded', function(event) {
                console.log('🔔 Image uploaded:', event);
                const data = event.data || event;
                lanternQueue.unshift(data);
                spawnLanternSprite(data);
            });

            // Initial load
            fetchNewestUploads();
        }
    </script>
</body>

</html>
