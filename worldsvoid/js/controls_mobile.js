// Mobile Touch Listener
const gameCanvas = document.getElementById('gameCanvas');

gameCanvas.addEventListener("touchstart", function(e) {
    e.preventDefault(); 
    
    // Grab where the player tapped on the screen
    const touch = e.touches[0];
    const rect = gameCanvas.getBoundingClientRect();
    const touchX = touch.clientX - rect.left;
    const touchY = touch.clientY - rect.top;

    // Calculate where that is in the actual game world based on camera position
    const camX = player.x + (player.size / 2) - (gameCanvas.width / 2);
    const camY = player.y + (player.size / 2) - (gameCanvas.height / 2);
    
    // Snap the target to your 32x32 grid
    player.targetX = Math.floor((touchX + camX) / 32) * 32;
    player.targetY = Math.floor((touchY + camY) / 32) * 32;
    player.isMoving = true;
    
}, { passive: false });