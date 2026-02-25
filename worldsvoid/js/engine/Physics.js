export class PhysicsEngine {
    constructor(stepSize = 1/60) {
        this.step = stepSize;
    }

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

        gameState.camera.x = player.x - (window.innerWidth / 2);
        gameState.camera.y = player.y - (window.innerHeight / 2);

        if (inputState.isAttacking) {
            console.log(`Attack executed at X:${inputState.targetX}, Y:${inputState.targetY}`);
            inputState.isAttacking = false; 
        }
    }
}