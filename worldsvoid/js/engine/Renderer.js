export class Renderer {
    constructor(context, canvas, playerSprite, worldMap) {
        this.ctx = context;
        this.canvas = canvas;
        this.playerSprite = playerSprite;
        this.worldMap = worldMap;
        this.resize();
    }

    resize() {
        this.canvas.width = window.innerWidth;
        this.canvas.height = window.innerHeight;
    }

    draw(gameState, inputState) {
        // 1. Clear Screen
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        // --- WORLD SPACE (Affected by Camera) ---
        this.ctx.save();
        this.ctx.translate(-gameState.camera.x, -gameState.camera.y);

        for (let r = 0; r < this.worldMap.rows; r++) {
            for (let c = 0; c < this.worldMap.cols; c++) {
                let tile = this.worldMap.tiles[r * this.worldMap.cols + c];
                let x = c * this.worldMap.tileSize;
                let y = r * this.worldMap.tileSize;

                if (tile === 1) {
                    this.ctx.fillStyle = '#1a1a24'; // Wall Color
                } else if (tile === 2) {
                    this.ctx.fillStyle = '#0a2a1a'; // Grass Color
                } else {
                    this.ctx.fillStyle = '#050505'; // Floor Color
                }

                this.ctx.fillRect(x, y, this.worldMap.tileSize, this.worldMap.tileSize);
                this.ctx.strokeStyle = '#111'; // Tile borders
                this.ctx.strokeRect(x, y, this.worldMap.tileSize, this.worldMap.tileSize);
            }
        }

        // Draw Player
        if (this.playerSprite.complete && this.playerSprite.naturalWidth !== 0) {
            this.ctx.drawImage(this.playerSprite, gameState.player.x - (gameState.player.size / 2), gameState.player.y - (gameState.player.size / 2), gameState.player.size, gameState.player.size);
        } else {
            this.ctx.fillStyle = '#00f3ff';
            this.ctx.fillRect(gameState.player.x - (gameState.player.size / 2), gameState.player.y - (gameState.player.size / 2), gameState.player.size, gameState.player.size);
        }

        // Draw Tap Target Indicator
        if (inputState.targetX !== null) {
            this.ctx.beginPath();
            this.ctx.arc(inputState.targetX, inputState.targetY, 15, 0, Math.PI * 2);
            this.ctx.strokeStyle = 'rgba(0, 243, 255, 0.5)';
            this.ctx.stroke();
        }

        this.ctx.restore();
        // --- END WORLD SPACE ---

        // --- SCREEN SPACE (UI Layer - NOT affected by camera) ---

        // UI Layer Phone Outline
        this.ctx.fillStyle = 'rgba(0, 243, 255, 0.1)';
        this.ctx.strokeStyle = '#00f3ff';
        this.ctx.strokeRect(this.canvas.width - 70, 10, 60, 60);

        // Darken screen if phone is open
        if (inputState.isPhoneOpen) {
            this.ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
            this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
        }
    }
}