console.log("PC Control Link Established.");

// --- 1. KEYBOARD ENGINE (WASD) ---
window.keys = {};

// When a key is pressed down, set it to true
window.addEventListener('keydown', function(e) { 
    // We convert it to lowercase so 'W' and 'w' both work perfectly
    window.keys[e.key.toLowerCase()] = true; 
});

// When the key is let go, set it to false so the player stops
window.addEventListener('keyup', function(e) { 
    window.keys[e.key.toLowerCase()] = false; 
});


gameCanvas.addEventListener("mousedown", function(e) {
    const rect = gameCanvas.getBoundingClientRect();
    const clickX = e.clientX - rect.left;
    const clickY = e.clientY - rect.top;

    const camX = player.x + (player.size / 2) - (gameCanvas.width / 2);
    const camY = player.y + (player.size / 2) - (gameCanvas.height / 2);
    
    const worldX = clickX + camX;
    const worldY = clickY + camY;

    if (typeof CURRENT_MAP !== 'undefined' && CURRENT_MAP.movementStyle === 'free') {
        player.targetX = worldX;
        player.targetY = worldY;
        player.isMoving = true;
    } else {
        player.finalDestX = Math.floor(worldX / 32) * 32;
        player.finalDestY = Math.floor(worldY / 32) * 32;
    }
});