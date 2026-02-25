export class Renderer {
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
        // 1. Clear Screen
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        // --- WORLD SPACE (Affected by Camera) ---
        this.ctx.save();
        this.ctx.translate(-gameState.camera.x, -gameState.camera.y);

        // Draw World Grid (Visual proof of movement)
        this.ctx.strokeStyle = '#111';
        for(let i = -1000; i < 2000; i += 100) {
            this.ctx.strokeRect(i, -1000, 100, 3000);
            this.ctx.strokeRect(-1000, i, 3000, 100);
        }

        // Draw Monsters (Future Addon Scaffold)
        gameState.monsters.forEach(monster => {
            this.ctx.fillStyle = monster.color;
            this.ctx.fillRect(monster.x - 15, monster.y - 15, 30, 30);
        });

        // Draw Player
        this.ctx.fillStyle = gameState.player.color;
        this.ctx.shadowBlur = 15;
        this.ctx.shadowColor = gameState.player.color;
        this.ctx.fillRect(gameState.player.x - (gameState.player.size/2), gameState.player.y - (gameState.player.size/2), gameState.player.size, gameState.player.size);
        this.ctx.shadowBlur = 0; // Reset shadow

        // Draw Tap Target Indicator
        if (inputState.targetX !== null) {
            this.ctx.beginPath();
            this.ctx.arc(inputState.targetX, inputState.targetY, 15, 0, Math.PI * 2);
            this.ctx.strokeStyle = 'rgba(0, 243, 255, 0.8)';
            this.ctx.stroke();
        }

        this.ctx.restore();
        // --- END WORLD SPACE ---

        // --- SCREEN SPACE (UI Layer - NOT affected by camera) ---
        
        // Phone Icon
        this.ctx.fillStyle = 'rgba(0, 243, 255, 0.1)';
        this.ctx.strokeStyle = '#00f3ff';
        this.ctx.fillRect(this.canvas.width - 70, 10, 60, 60);
        this.ctx.strokeRect(this.canvas.width - 70, 10, 60, 60);
        this.ctx.fillStyle = 'white';
        this.ctx.font = "bold 12px monospace";
        this.ctx.fillText("PHONE", this.canvas.width - 58, 45);

        // Darken screen if phone is open
        if (inputState.isPhoneOpen) {
            this.ctx.fillStyle = 'rgba(0, 0, 0, 0.75)';
            this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
        }
    }
}