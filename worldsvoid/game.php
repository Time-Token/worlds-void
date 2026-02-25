<?php
session_start();
// Add any necessary session or DB checks here
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Worlds Void - Engine</title>
    <style>
        body { margin: 0; padding: 0; overflow: hidden; background: #050505; font-family: 'Share Tech Mono', monospace; }
        canvas { display: block; touch-action: none; outline: none; }

        /* The In-Game Phone Interface */
        #phone-interface {
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 280px; height: 450px;
            background: rgba(15, 15, 25, 0.95);
            border: 3px solid #00f3ff;
            border-radius: 20px;
            display: none; 
            flex-direction: column;
            padding: 20px; box-sizing: border-box;
            color: #00f3ff;
            box-shadow: 0 0 40px rgba(0, 243, 255, 0.3);
            z-index: 1000;
            backdrop-filter: blur(10px);
        }

        .phone-header { border-bottom: 2px solid #ff007f; padding-bottom: 10px; margin-bottom: 20px; text-align: center; font-weight: bold; }
        .phone-btn { 
            background: rgba(0, 243, 255, 0.05); 
            border: 1px solid #00f3ff; 
            color: #00f3ff; 
            padding: 12px; margin: 8px 0; 
            text-align: center; cursor: pointer;
            text-transform: uppercase; font-size: 0.9em;
            transition: 0.2s;
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
        // --- 1. ASSET LOADING ---
        // Change these paths to your actual image locations
        const playerSprite = new Image();
        playerSprite.src = './images/player.png'; 

        // --- 2. INPUT HANDLER ---
        class InputHandler {
            constructor() {
                this.state = {
                    targetX: null, targetY: null,
                    isAttacking: false, isPhoneOpen: false
                };
                this.lastTapTime = 0;
                this.doubleTapDelay = 300;
            }

            init(canvas, gameState) {
                canvas.addEventListener('pointerdown', (e) => {
                    const currentTime = Date.now();
                    const timeSinceLastTap = currentTime - this.lastTapTime;
                    
                    const screenX = e.clientX;
                    const screenY = e.clientY;

                    // PHONE UI ZONE (Top Right Corner)
                    if (screenX > window.innerWidth - 80 && screenY < 80) {
                        this.state.isPhoneOpen = !this.state.isPhoneOpen;
                        return; 
                    }

                    if (this.state.isPhoneOpen) return;

                    // CONVERT TO WORLD COORDINATES (Using Camera)
                    const worldX = screenX + gameState.camera.x;
                    const worldY = screenY + gameState.camera.y;

                    if (timeSinceLastTap < this.doubleTapDelay) {
                        this.state.isAttacking = true;
                    } 
                    
                    this.state.targetX = worldX;
                    this.state.targetY = worldY;
                    this.lastTapTime = currentTime;
                });
            }
        }

        // --- 3. PHYSICS ENGINE ---
        class PhysicsEngine {
            update(gameState, inputState, deltaTime) {
                if (inputState.isPhoneOpen) return; 

                const player = gameState.player;

                if (inputState.targetX !== null && inputState.targetY !== null) {
                    const dx = inputState.targetX - player.x;
                    const dy = inputState.targetY - player.y;
                    const distance = Math.sqrt(dx * dx + dy * dy);

                    if (distance > player.speed * deltaTime) {
                        player.x += (dx / distance) * player.speed * deltaTime;
                        player.y += (dy / distance) * player.speed * deltaTime;
                    } else {
                        player.x = inputState.targetX;
                        player.y = inputState.targetY;
                        inputState.targetX = null;
                        inputState.targetY = null;
                    }
                }

                // Smooth Camera Follow
                gameState.camera.x = player.x - (window.innerWidth / 2);
                gameState.camera.y = player.y - (window.innerHeight / 2);

                if (inputState.isAttacking) {
                    console.log("Attack Processed");
                    inputState.isAttacking = false;
                }
            }
        }

        // --- 4. RENDERER ---
        class Renderer {
            constructor(context, canvas) {
                this.ctx = context;
                this.canvas = canvas;
                this.resize();
            }

            resize() {
                this.canvas.width = window.innerWidth;
                this.canvas.height = window.innerHeight;
            }
            
            draw(gameState, inputState) {
                this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

                // --- WORLD LAYER (Camera Affected) ---
                this.ctx.save();
                this.ctx.translate(-gameState.camera.x, -gameState.camera.y);

                // Draw World Grid
                this.ctx.strokeStyle = '#111';
                for(let i = -2000; i < 4000; i += 100) {
                    this.ctx.strokeRect(i, -2000, 100, 4000);
                    this.ctx.strokeRect(-2000, i, 4000, 100);
                }

                // Draw Player (Image or Cyan Square Fallback)
                if (playerSprite.complete && playerSprite.naturalWidth !== 0) {
                    this.ctx.drawImage(playerSprite, gameState.player.x - 25, gameState.player.y - 25, 50, 50);
                } else {
                    this.ctx.fillStyle = '#00f3ff';
                    this.ctx.fillRect(gameState.player.x - 15, gameState.player.y - 15, 30, 30);
                }

                // Draw Target Circle
                if (inputState.targetX !== null) {
                    this.ctx.beginPath();
                    this.ctx.arc(inputState.targetX, inputState.targetY, 15, 0, Math.PI * 2);
                    this.ctx.strokeStyle = 'rgba(0, 243, 255, 0.5)';
                    this.ctx.stroke();
                }

                this.ctx.restore();

                // --- UI LAYER (Fixed on Screen) ---
                // Phone Icon Zone
                this.ctx.fillStyle = 'rgba(0, 243, 255, 0.1)';
                this.ctx.strokeStyle = '#00f3ff';
                this.ctx.strokeRect(this.canvas.width - 70, 10, 60, 60);

                if (inputState.isPhoneOpen) {
                    this.ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
                    this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
                }
            }
        }

        // --- 5. INITIALIZE & LOOP ---
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');
        const phoneUI = document.getElementById('phone-interface');

        const gameState = {
            player: { x: 500, y: 500, speed: 250 },
            camera: { x: 0, y: 0 },
            time: { last: 0, accumulator: 0, dt: 1 / 60 }
        };

        const physics = new PhysicsEngine();
        const renderer = new Renderer(ctx, canvas);
        const input = new InputHandler();

        input.init(canvas, gameState);

        function gameLoop(timestamp) {
            let frameTime = (timestamp - gameState.time.last) / 1000;
            gameState.time.last = timestamp;
            if (frameTime > 0.25) frameTime = 0.25; 

            gameState.time.accumulator += frameTime;

            // Sync HTML UI
            phoneUI.style.display = input.state.isPhoneOpen ? 'flex' : 'none';

            while (gameState.time.accumulator >= gameState.time.dt) {
                physics.update(gameState, input.state, gameState.time.dt);
                gameState.time.accumulator -= gameState.time.dt;
            }

            renderer.draw(gameState, input.state);
            requestAnimationFrame(gameLoop);
        }

        requestAnimationFrame(gameLoop);
        window.addEventListener('resize', () => renderer.resize());
    </script>
</body>
</html>