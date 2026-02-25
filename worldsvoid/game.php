<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Worlds Void - Collision Upgrade</title>
    <style>
        body { margin: 0; padding: 0; overflow: hidden; background: #000; font-family: 'Share Tech Mono', monospace; }
        canvas { display: block; touch-action: none; outline: none; }
        
        #phone-interface {
            position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
            width: 280px; height: 450px; background: rgba(15, 15, 25, 0.95);
            border: 3px solid #00f3ff; border-radius: 20px; display: none; 
            flex-direction: column; padding: 20px; box-sizing: border-box;
            color: #00f3ff; box-shadow: 0 0 40px rgba(0, 243, 255, 0.3);
            z-index: 1000; backdrop-filter: blur(10px);
        }
        .phone-header { border-bottom: 2px solid #ff007f; padding-bottom: 10px; margin-bottom: 20px; text-align: center; font-weight: bold; }
        .phone-btn { 
            background: rgba(0, 243, 255, 0.05); border: 1px solid #00f3ff; 
            color: #00f3ff; padding: 12px; margin: 8px 0; text-align: center; cursor: pointer;
            text-transform: uppercase; font-size: 0.9em; transition: 0.2s;
        }
        .phone-btn:hover { background: #00f3ff; color: #000; }
    </style>
</head>
<body>
    <canvas id="gameCanvas"></canvas>
    
    <div id="phone-interface">
        <div class="phone-header">V-LINK TERMINAL</div>
        <div class="phone-btn">Account Data</div>
        <div class="phone-btn">Void Map</div>
        <div class="phone-btn">Settings</div>
        <a href="index.php" style="text-decoration:none; margin-top: auto;">
            <div class="phone-btn" style="border-color:#ff007f; color:#ff007f;">Disconnect</div>
        </a>
    </div>

    <script>
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');
        const phoneUI = document.getElementById('phone-interface');

        const playerSprite = new Image();
        playerSprite.src = './images/player.png'; 

        // 0: Floor, 1: Wall, 2: Grass
        const worldMap = {
            tileSize: 100,
            cols: 10,
            rows: 10,
            tiles: [
                1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
                1, 0, 0, 0, 2, 2, 0, 0, 0, 1,
                1, 0, 1, 0, 2, 2, 0, 1, 0, 1,
                1, 0, 1, 0, 0, 0, 0, 1, 0, 1,
                1, 0, 0, 0, 0, 0, 0, 0, 0, 1,
                1, 2, 2, 0, 0, 0, 0, 0, 0, 1,
                1, 2, 2, 0, 1, 1, 1, 0, 0, 1,
                1, 0, 0, 0, 1, 0, 0, 0, 0, 1,
                1, 0, 0, 0, 1, 0, 0, 0, 0, 1,
                1, 1, 1, 1, 1, 1, 1, 1, 1, 1
            ]
        };

        const gameState = {
            player: { x: 500, y: 500, speed: 250, size: 40 },
            camera: { x: 0, y: 0 },
            time: { last: 0, accumulator: 0, dt: 1 / 60 }
        };

        const input = {
            targetX: null, targetY: null, isPhoneOpen: false, isAttacking: false, lastTapTime: 0
        };

        // --- INPUT HANDLING ---
        canvas.addEventListener('pointerdown', (e) => {
            const currentTime = Date.now();
            const timeSinceLastTap = currentTime - input.lastTapTime;

            if (e.clientX > window.innerWidth - 80 && e.clientY < 80) {
                input.isPhoneOpen = !input.isPhoneOpen;
                return;
            }
            if (input.isPhoneOpen) return;

            input.targetX = e.clientX + gameState.camera.x;
            input.targetY = e.clientY + gameState.camera.y;

            if (timeSinceLastTap < 300) input.isAttacking = true;
            input.lastTapTime = currentTime;
        });

        // --- NEW PHYSICS & COLLISION ---
        function isWall(x, y) {
            let col = Math.floor(x / worldMap.tileSize);
            let row = Math.floor(y / worldMap.tileSize);
            
            // Map boundaries act as solid walls
            if (col < 0 || col >= worldMap.cols || row < 0 || row >= worldMap.rows) return true;
            
            // Tile "1" is a wall
            return worldMap.tiles[row * worldMap.cols + col] === 1;
        }

        function update(dt) {
            if (input.isPhoneOpen) return;
            const p = gameState.player;
            
            if (input.targetX !== null) {
                const dx = input.targetX - p.x;
                const dy = input.targetY - p.y;
                const dist = Math.sqrt(dx*dx + dy*dy);
                
                if (dist > p.speed * dt) {
                    let moveX = (dx/dist) * p.speed * dt;
                    let moveY = (dy/dist) * p.speed * dt;
                    let nextX = p.x + moveX;
                    let nextY = p.y + moveY;
                    let half = p.size / 2.2; // Hitbox size

                    // Check X collision separately to allow "sliding" along walls
                    if (!isWall(nextX - half, p.y - half) && !isWall(nextX + half, p.y - half) && 
                        !isWall(nextX - half, p.y + half) && !isWall(nextX + half, p.y + half)) {
                        p.x = nextX;
                    } else {
                        input.targetX = p.x; // Cancel horizontal movement if wall hit
                    }

                    // Check Y collision separately
                    if (!isWall(p.x - half, nextY - half) && !isWall(p.x + half, nextY - half) && 
                        !isWall(p.x - half, nextY + half) && !isWall(p.x + half, nextY + half)) {
                        p.y = nextY;
                    } else {
                        input.targetY = p.y; // Cancel vertical movement if wall hit
                    }

                } else {
                    p.x = input.targetX;
                    p.y = input.targetY;
                    input.targetX = null;
                    input.targetY = null;
                }
            }
            
            // Camera boundaries
            let camX = p.x - (window.innerWidth / 2);
            let camY = p.y - (window.innerHeight / 2);
            const maxCamX = (worldMap.cols * worldMap.tileSize) - window.innerWidth;
            const maxCamY = (worldMap.rows * worldMap.tileSize) - window.innerHeight;

            gameState.camera.x = Math.max(0, Math.min(camX, maxCamX));
            gameState.camera.y = Math.max(0, Math.min(camY, maxCamY));
        }

        // --- RENDERER ---
        function draw() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.save();
            ctx.translate(-gameState.camera.x, -gameState.camera.y);
            
            for (let r = 0; r < worldMap.rows; r++) {
                for (let c = 0; c < worldMap.cols; c++) {
                    let tile = worldMap.tiles[r * worldMap.cols + c];
                    let x = c * worldMap.tileSize;
                    let y = r * worldMap.tileSize;

                    if (tile === 1) {
                        ctx.fillStyle = '#1a1a24'; // Wall Color
                    } else if (tile === 2) {
                        ctx.fillStyle = '#0a2a1a'; // Grass Color
                    } else {
                        ctx.fillStyle = '#050505'; // Floor Color
                    }

                    ctx.fillRect(x, y, worldMap.tileSize, worldMap.tileSize);
                    ctx.strokeStyle = '#111'; // Tile borders
                    ctx.strokeRect(x, y, worldMap.tileSize, worldMap.tileSize);
                }
            }

            // Draw Player
            if (playerSprite.complete && playerSprite.naturalWidth !== 0) {
                ctx.drawImage(playerSprite, gameState.player.x - (gameState.player.size/2), gameState.player.y - (gameState.player.size/2), gameState.player.size, gameState.player.size);
            } else {
                ctx.fillStyle = '#00f3ff';
                ctx.fillRect(gameState.player.x - (gameState.player.size/2), gameState.player.y - (gameState.player.size/2), gameState.player.size, gameState.player.size);
            }

            // Draw Tap Target
            if (input.targetX !== null) {
                ctx.beginPath();
                ctx.arc(input.targetX, input.targetY, 15, 0, Math.PI * 2);
                ctx.strokeStyle = 'rgba(0, 243, 255, 0.5)';
                ctx.stroke();
            }

            ctx.restore();

            // UI Layer
            ctx.fillStyle = 'rgba(0, 243, 255, 0.1)';
            ctx.strokeStyle = '#00f3ff';
            ctx.strokeRect(canvas.width - 70, 10, 60, 60);

            if (input.isPhoneOpen) {
                ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
            }

            phoneUI.style.display = input.isPhoneOpen ? 'flex' : 'none';
        }

        // --- MAIN LOOP ---
        function loop(t) {
            let dt = (t - gameState.time.last) / 1000;
            gameState.time.last = t;
            if (dt > 0.1) dt = 0.1;
            update(dt);
            draw();
            requestAnimationFrame(loop);
        }

        window.addEventListener('resize', () => { canvas.width = window.innerWidth; canvas.height = window.innerHeight; });
        canvas.width = window.innerWidth; canvas.height = window.innerHeight;
        requestAnimationFrame(loop);
    </script>
</body>
</html>