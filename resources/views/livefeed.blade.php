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
            will-change: transform, left, top;
            transition: left 0.1s ease-out, top 0.1s ease-out;
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

            const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
                cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
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
            let spawnDelay = 0;
            let lastSpawnTime = 0;

            // Check if position overlaps with existing lanterns
            function hasOverlap(x, y, size) {
                const minDistance = size * 1.5; // Minimum distance between lanterns
                return displayedSprites.some(sprite => {
                    if (sprite.isEntering) return false; // Ignore lanterns still entering
                    const dx = sprite.x - x;
                    const dy = sprite.y - y;
                    const distance = Math.sqrt(dx * dx + dy * dy);
                    return distance < minDistance;
                });
            }

            // Find a non-overlapping position
            function findValidPosition(baseSize, scale) {
                const size = baseSize * scale;
                let attempts = 0;
                let x, targetY;
                
                do {
                    x = 50 + Math.random() * (window.innerWidth - 100);
                    targetY = 100 + Math.random() * (window.innerHeight - 300);
                    attempts++;
                } while (hasOverlap(x, targetY, size) && attempts < 50);
                
                return { x, targetY };
            }

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

                const scale = 0.5; // Fixed size - no randomization
                const baseSize = 300; // Approximate lantern size
                img.style.width = (baseSize * scale) + 'px';
                img.style.height = 'auto';
                img.style.zIndex = '10';

                // Find non-overlapping position
                const { x: startX, targetY } = findValidPosition(baseSize, scale);
                const startY = window.innerHeight + 100; // Start below screen

                img.style.left = (startX - (baseSize * scale / 2)) + 'px';
                img.style.top = startY + 'px';

                document.body.appendChild(img);

                displayedSprites.push({
                    element: img,
                    x: startX,
                    y: startY,
                    targetY: targetY,
                    startX,
                    scale,
                    baseSize,
                    vx: (Math.random() - 0.5) * 0.3, // Slower horizontal velocity
                    vy: (Math.random() - 0.5) * 0.2, // Slower vertical velocity
                    isEntering: true, // Entry animation flag
                    entrySpeed: 1.5 + Math.random() * 0.5 // Slower entry speed
                });

                // FIFO: Remove oldest lantern when max limit is reached
                if (displayedSprites.length > MAX_DISPLAY) {
                    const old = displayedSprites.shift();
                    if (old && old.element && old.element.parentNode) {
                        old.element.remove();
                    }
                }
            }

            app.ticker.add((delta) => {
                const smoothDelta = Math.min(delta, 1.5); // Cap delta for consistent smooth movement
                const currentTime = Date.now();
                
                for (let i = displayedSprites.length - 1; i >= 0; i--) {
                    const item = displayedSprites[i];
                    if (!item || !item.element) continue;

                    const time = currentTime / 1000;
                    
                    // Entry animation - rise from bottom with easing
                    if (item.isEntering) {
                        const progress = 1 - ((item.y - item.targetY) / (window.innerHeight + 100 - item.targetY));
                        const easing = 1 - Math.pow(1 - progress, 3); // Ease-out cubic
                        
                        item.y -= item.entrySpeed * smoothDelta;
                        
                        // Check if reached target position
                        if (item.y <= item.targetY) {
                            item.y = item.targetY;
                            item.isEntering = false; // Switch to floating mode
                        }
                        
                        // Gentle sway during entry
                        const floatX = Math.sin(time * 1.2 + i) * 8;
                        const rotation = Math.sin(time * 1.0 + i) * 0.15;
                        
                        item.element.style.left = (item.x - (item.baseSize * item.scale / 2) + floatX) + 'px';
                        item.element.style.top = item.y + 'px';
                        item.element.style.transform = `rotate(${rotation}rad)`;
                    } 
                    // Floating mode - drift around screen with collision avoidance
                    else {
                        // Check for collisions with other lanterns before moving
                        const minDistance = item.baseSize * item.scale * 1.2;
                        
                        for (let j = 0; j < displayedSprites.length; j++) {
                            if (i === j || displayedSprites[j].isEntering) continue;
                            const other = displayedSprites[j];
                            const dx = item.x - other.x;
                            const dy = item.y - other.y;
                            const distance = Math.sqrt(dx * dx + dy * dy);
                            
                            if (distance < minDistance && distance > 0) {
                                // Push away from each other gently
                                const angle = Math.atan2(dy, dx);
                                const pushForce = 0.5;
                                item.vx += Math.cos(angle) * pushForce * smoothDelta;
                                item.vy += Math.sin(angle) * pushForce * smoothDelta;
                            }
                        }
                        
                        // Always update position - keep moving
                        item.x += item.vx * smoothDelta;
                        item.y += item.vy * smoothDelta;
                        
                        // Bounce off edges - maintain velocity
                        if (item.x < 50 || item.x > window.innerWidth - 50) {
                            item.vx *= -1;
                            item.x = Math.max(50, Math.min(window.innerWidth - 50, item.x));
                        }
                        if (item.y < 50 || item.y > window.innerHeight - 100) {
                            item.vy *= -1;
                            item.y = Math.max(50, Math.min(window.innerHeight - 100, item.y));
                        }
                        
                        // Add gentle wave motion (reduced amplitude)
                        const floatY = Math.sin(time * 0.4 + i) * 6;
                        const floatX = Math.cos(time * 0.25 + i) * 8;

                        // Gentle rotation/sway
                        const rotation = Math.sin(time * 0.6 + i) * 0.12;

                        item.element.style.left = (item.x - (item.baseSize * item.scale / 2) + floatX) + 'px';
                        item.element.style.top = (item.y + floatY) + 'px';
                        item.element.style.transform = `rotate(${rotation}rad)`;
                    }
                }
            });

            // Fetch newest uploads on load
            function fetchNewestUploads() {
                console.log('📡 Fetching newest uploads from named route api.lantern.latest...');
                fetch('{{ route('api.lantern.latest') }}', {
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

                        // Stagger the spawning with delays
                        uploads.slice(0, MAX_DISPLAY).forEach((upload, index) => {
                            lanternQueue.push(upload);
                            setTimeout(() => spawnLanternSprite(upload), index * 800); // 800ms delay between each
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
                
                // Stagger new lanterns with a delay
                const now = Date.now();
                const timeSinceLastSpawn = now - lastSpawnTime;
                const minDelay = 500; // Minimum 500ms between spawns
                
                if (timeSinceLastSpawn >= minDelay) {
                    spawnLanternSprite(data);
                    lastSpawnTime = now;
                } else {
                    setTimeout(() => {
                        spawnLanternSprite(data);
                        lastSpawnTime = Date.now();
                    }, minDelay - timeSinceLastSpawn);
                }
            });

            // Initial load
            fetchNewestUploads();
        }
    </script>
</body>

</html>
