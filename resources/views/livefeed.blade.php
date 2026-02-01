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
            animation: glowBlink 3s ease-in-out infinite;
        }

        @keyframes glowBlink {
            0%, 100% {
                filter: drop-shadow(0 0 20px rgba(255, 200, 100, 0.8)) drop-shadow(0 0 40px rgba(255, 150, 50, 0.6));
            }
            50% {
                filter: drop-shadow(0 0 30px rgba(255, 200, 100, 1)) drop-shadow(0 0 60px rgba(255, 150, 50, 0.9));
            }
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
            let exitInProgress = false; // Track if an exit animation is happening
            
            // Predefined scattered positions for 10 lanterns - portrait optimized
            function getPredefinedPositions() {
                const screenWidth = window.innerWidth;
                const screenHeight = window.innerHeight;
                const padding = 80; // Keep away from edges
                
                // Manually defined scattered positions - more spread out
                const scatterPattern = [
                    { xPercent: 0.15, yPercent: 0.1 },   // Top left
                    { xPercent: 0.85, yPercent: 0.08 },  // Top right
                    { xPercent: 0.5, yPercent: 0.18 },   // Upper center
                    { xPercent: 0.25, yPercent: 0.3 },   // Mid-left
                    { xPercent: 0.75, yPercent: 0.28 },  // Mid-right
                    { xPercent: 0.5, yPercent: 0.42 },   // Center
                    { xPercent: 0.2, yPercent: 0.54 },   // Lower-mid left
                    { xPercent: 0.8, yPercent: 0.52 },   // Lower-mid right
                    { xPercent: 0.55, yPercent: 0.68 },  // Lower center-right
                    { xPercent: 0.25, yPercent: 0.76 }   // Lower left
                ];
                
                return scatterPattern.map((pattern, index) => ({
                    x: padding + (screenWidth - padding * 2) * pattern.xPercent,
                    y: (screenHeight * pattern.yPercent),
                    occupied: false
                }));
            }
            
            let lanternPositions = getPredefinedPositions();
            
            // Get next available position
            function getNextPosition() {
                const available = lanternPositions.find(pos => !pos.occupied);
                if (available) {
                    available.occupied = true;
                    console.log(`🎯 Assigned position: (${Math.round(available.x)}, ${Math.round(available.y)})`);
                    return { x: available.x, y: available.y, posIndex: lanternPositions.indexOf(available) };
                }
                // Fallback if all occupied (shouldn't happen with MAX_DISPLAY)
                console.warn('⚠️ All positions occupied, using fallback');
                return { x: window.innerWidth / 2, y: window.innerHeight / 3, posIndex: -1 };
            }
            
            // Release position when lantern is removed
            function releasePosition(posIndex) {
                if (posIndex >= 0 && posIndex < lanternPositions.length) {
                    lanternPositions[posIndex].occupied = false;
                    console.log(`✅ Released position ${posIndex}`);
                }
            }

            function spawnLanternSprite(lanternData, fromSide = false, reusePositionIndex = null) {
                const url = lanternData.image_url || lanternData.url;
                if (!url) {
                    console.warn('No URL', lanternData);
                    return;
                }

                // If at max capacity, animate out the oldest lantern first
                if (displayedSprites.length >= MAX_DISPLAY && reusePositionIndex === null) {
                    // Check if an exit is already in progress
                    if (exitInProgress) {
                        console.log('⏳ Exit already in progress, queuing new lantern');
                        // Queue this spawn to try again after current exit completes
                        setTimeout(() => {
                            spawnLanternSprite(lanternData, fromSide, reusePositionIndex);
                        }, 500);
                        return;
                    }
                    
                    const oldestLantern = displayedSprites[0];
                    if (oldestLantern && !oldestLantern.isExiting) {
                        exitInProgress = true; // Set flag
                        oldestLantern.isExiting = true;
                        oldestLantern.exitStartTime = Date.now();
                        const exitingPositionIndex = oldestLantern.posIndex; // Store position to reuse
                        console.log(`👋 Starting exit animation for oldest lantern at position ${exitingPositionIndex}`);
                        
                        // Wait for exit animation to complete before spawning new one
                        setTimeout(() => {
                            exitInProgress = false; // Clear flag
                            actuallySpawnLantern(lanternData, url, fromSide, exitingPositionIndex);
                        }, 1500); // 1.5 second exit animation
                        return;
                    }
                }
                
                actuallySpawnLantern(lanternData, url, fromSide, reusePositionIndex);
            }

            function actuallySpawnLantern(lanternData, url, fromSide = false, reusePositionIndex = null) {
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

                // Get predefined position - reuse specific position if provided
                let targetX, targetY, posIndex;
                if (reusePositionIndex !== null && reusePositionIndex >= 0 && reusePositionIndex < lanternPositions.length) {
                    // Reuse the specific position from exiting lantern
                    const pos = lanternPositions[reusePositionIndex];
                    targetX = pos.x;
                    targetY = pos.y;
                    posIndex = reusePositionIndex;
                    pos.occupied = true;
                    console.log(`🎯 Reusing position ${posIndex}: (${Math.round(targetX)}, ${Math.round(targetY)})`);
                } else {
                    // Get next available position
                    const result = getNextPosition();
                    targetX = result.x;
                    targetY = result.y;
                    posIndex = result.posIndex;
                }
                const startY = window.innerHeight + 100; // Start from bottom
                const startX = targetX;
                
                const lanternWidth = baseSize * scale;
                const leftPos = startX - (lanternWidth / 2);
                
                console.log(`📍 Lantern spawn - Position ${posIndex}, Target: (${Math.round(targetX)}, ${Math.round(targetY)})`);

                img.style.left = leftPos + 'px';
                img.style.top = startY + 'px';

                document.body.appendChild(img);

                displayedSprites.push({
                    element: img,
                    x: startX,
                    y: startY,
                    targetX: targetX,
                    targetY: targetY,
                    anchorX: targetX, // Store anchor point for floating
                    anchorY: targetY,
                    posIndex: posIndex,
                    scale,
                    baseSize,
                    vx: (Math.random() - 0.5) * 0.3, // More velocity for visible floating
                    vy: (Math.random() - 0.5) * 0.3,
                    isEntering: true,
                    isExiting: false,
                    fromSide: fromSide,
                    entrySpeed: fromSide ? 5.0 : 2.0
                });
            }

            app.ticker.add((delta) => {
                const smoothDelta = Math.min(delta, 1.5); // Cap delta for consistent smooth movement
                const currentTime = Date.now();
                
                for (let i = displayedSprites.length - 1; i >= 0; i--) {
                    const item = displayedSprites[i];
                    if (!item || !item.element) continue;

                    const time = currentTime / 1000;
                    
                    // Handle exit animation - fade out and fly up
                    if (item.isExiting) {
                        const exitDuration = 1500; // 1.5 seconds
                        const elapsed = currentTime - item.exitStartTime;
                        const progress = Math.min(elapsed / exitDuration, 1);
                        
                        // Move up and fade out
                        item.y -= 2.5 * smoothDelta; // Fly up
                        const opacity = 1 - progress;
                        item.element.style.opacity = opacity;
                        item.element.style.top = item.y + 'px';
                        
                        // Remove when animation complete
                        if (progress >= 1) {
                            if (item.element.parentNode) {
                                item.element.remove();
                            }
                            releasePosition(item.posIndex); // Release the position
                            displayedSprites.splice(i, 1);
                            console.log('✅ Removed exited lantern and released position');
                        }
                        continue;
                    }
                    
                    // Entry animation - rise from bottom or float in from side
                    if (item.isEntering) {
                        if (item.fromSide) {
                            // Horizontal entry from side
                            const dx = item.targetX - item.x;
                            const dy = item.targetY - item.y;
                            const distance = Math.sqrt(dx * dx + dy * dy);
                            
                            if (distance > 5) {
                                item.x += (dx / distance) * item.entrySpeed * smoothDelta;
                                item.y += (dy / distance) * item.entrySpeed * smoothDelta;
                            } else {
                                item.x = item.targetX;
                                item.y = item.targetY;
                                item.isEntering = false;
                            }
                        } else {
                            // Vertical entry from bottom - no boundary restrictions
                            item.y -= item.entrySpeed * smoothDelta;
                            
                            // Check if reached target position
                            if (item.y <= item.targetY) {
                                item.y = item.targetY;
                                item.x = item.targetX; // Ensure x is also at target
                                item.isEntering = false; // Switch to floating mode
                            }
                        }
                        
                        // Faster sway during entry
                        const floatX = Math.sin(time * 2.5 + i) * 8;
                        const rotation = Math.sin(time * 2.2 + i) * 0.15;
                        
                        item.element.style.left = (item.x - (item.baseSize * item.scale / 2) + floatX) + 'px';
                        item.element.style.top = item.y + 'px';
                        item.element.style.transform = `rotate(${rotation}rad)`;
                    } 
                    // Floating mode - gentle drift around anchor point within zone
                    else {
                        // Much smaller drift zone to prevent overlap
                        const driftRadius = 40; // Reduced to 40px for tight zone control
                        const dx = item.anchorX - item.x;
                        const dy = item.anchorY - item.y;
                        const distFromAnchor = Math.sqrt(dx * dx + dy * dy);
                        
                        // More frequent random gentle nudges for natural floating
                        if (Math.random() < 0.05) { // 5% chance each frame for more movement
                            item.vx += (Math.random() - 0.5) * 0.15; // Reduced strength
                            item.vy += (Math.random() - 0.5) * 0.15;
                        }
                        
                        // Add subtle circular motion for more life
                        const circleSpeed = 0.3;
                        item.vx += Math.sin(time * circleSpeed + i) * 0.015 * smoothDelta;
                        item.vy += Math.cos(time * circleSpeed + i) * 0.015 * smoothDelta;
                        
                        // Stronger pull back toward anchor if drifting too far
                        if (distFromAnchor > driftRadius) {
                            const pullStrength = (distFromAnchor - driftRadius) / driftRadius * 0.3;
                            item.vx += (dx / distFromAnchor) * pullStrength * smoothDelta;
                            item.vy += (dy / distFromAnchor) * pullStrength * smoothDelta;
                        }
                        
                        // Update position
                        item.x += item.vx * smoothDelta;
                        item.y += item.vy * smoothDelta;
                        
                        // Very light velocity damping for more sustained movement
                        item.vx *= 0.995;
                        item.vy *= 0.995;
                        
                        // Add gentle wave motion (reduced amplitude)
                        const floatY = Math.sin(time * 0.4 + i) * 6;
                        const floatX = Math.cos(time * 0.25 + i) * 8;

                        // Faster rotation/sway
                        const rotation = Math.sin(time * 1.8 + i) * 0.12;

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

                        // Spawn lanterns one by one from bottom on initial load
                        uploads.slice(0, MAX_DISPLAY).forEach((upload, index) => {
                            lanternQueue.push(upload);
                            setTimeout(() => spawnLanternSprite(upload, false), index * 1000); // 1 second delay, from bottom
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
