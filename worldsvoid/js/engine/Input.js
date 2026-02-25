export class InputHandler {
    constructor() {
        this.state = {
            targetX: null,
            targetY: null,
            isAttacking: false,
            monsterTargeted: null,
            isPhoneOpen: false // Strictly synced variable name
        };
        this.lastTapTime = 0;
        this.doubleTapDelay = 300; // ms
    }

    init(canvas, gameState) {
        canvas.addEventListener('pointerdown', (e) => {
            const currentTime = Date.now();
            const timeSinceLastTap = currentTime - this.lastTapTime;
            
            // Screen coordinates
            const screenX = e.clientX;
            const screenY = e.clientY;

            // 1. UI ZONE (Top Right - Acts as Enter)
            if (screenX > window.innerWidth - 80 && screenY < 80) {
                this.state.isPhoneOpen = !this.state.isPhoneOpen;
                return; // Block gameplay input
            }

            if (this.state.isPhoneOpen) return; // Don't move while in menus

            // 2. WORLD COORDINATES (Account for Camera)
            const worldX = screenX + gameState.camera.x;
            const worldY = screenY + gameState.camera.y;

            // 3. ACTION LOGIC (Space vs Click)
            if (timeSinceLastTap < this.doubleTapDelay) {
                // Double Tap: Attack
                this.state.isAttacking = true;
                this.state.targetX = worldX;
                this.state.targetY = worldY;
            } else {
                // Single Tap: Move
                this.state.targetX = worldX;
                this.state.targetY = worldY;
                this.state.isAttacking = false;
            }

            this.lastTapTime = currentTime;
        });
    }
}