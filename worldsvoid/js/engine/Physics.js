export class PhysicsEngine {
    constructor(worldMap, stepSize = 1/60) {
        this.step = stepSize;
        this.worldMap = worldMap;
    }

    isWall(x, y) {
        let col = Math.floor(x / this.worldMap.tileSize);
        let row = Math.floor(y / this.worldMap.tileSize);
        
        // Map boundaries act as solid walls
        if (col < 0 || col >= this.worldMap.cols || row < 0 || row >= this.worldMap.rows) return true;
        
        // Tile "1" is a wall
        return this.worldMap.tiles[row * this.worldMap.cols + col] === 1;
    }

    update(gameState, inputState, deltaTime) {
        if (inputState.isPhoneOpen) return;

        const p = gameState.player;
        
        if (inputState.targetX !== null) {
            const dx = inputState.targetX - p.x;
            const dy = inputState.targetY - p.y;
            const dist = Math.sqrt(dx*dx + dy*dy);
            
            if (dist > p.speed * deltaTime) {
                let moveX = (dx/dist) * p.speed * deltaTime;
                let moveY = (dy/dist) * p.speed * deltaTime;
                let nextX = p.x + moveX;
                let nextY = p.y + moveY;
                let half = p.size / 2.2; // Hitbox size

                // Check X collision separately to allow "sliding" along walls
                if (!this.isWall(nextX - half, p.y - half) && !this.isWall(nextX + half, p.y - half) && 
                    !this.isWall(nextX - half, p.y + half) && !this.isWall(nextX + half, p.y + half)) {
                    p.x = nextX;
                } else {
                    inputState.targetX = p.x; // Cancel horizontal movement if wall hit
                }

                // Check Y collision separately
                if (!this.isWall(p.x - half, nextY - half) && !this.isWall(p.x + half, nextY - half) && 
                    !this.isWall(p.x - half, nextY + half) && !this.isWall(p.x + half, nextY + half)) {
                    p.y = nextY;
                } else {
                    inputState.targetY = p.y; // Cancel vertical movement if wall hit
                }

            } else {
                p.x = inputState.targetX;
                p.y = inputState.targetY;
                inputState.targetX = null;
                inputState.targetY = null;
            }
        }
        
        // Camera boundaries
        let camX = p.x - (window.innerWidth / 2);
        let camY = p.y - (window.innerHeight / 2);
        const maxCamX = (this.worldMap.cols * this.worldMap.tileSize) - window.innerWidth;
        const maxCamY = (this.worldMap.rows * this.worldMap.tileSize) - window.innerHeight;

        gameState.camera.x = Math.max(0, Math.min(camX, maxCamX));
        gameState.camera.y = Math.max(0, Math.min(camY, maxCamY));
    }
}