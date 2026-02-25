// --- HYBRID PATHFINDING & MOVEMENT SYSTEM ---

window.keys = { w: false, a: false, s: false, d: false };

window.addEventListener('keydown', (e) => {
    const key = e.key.toLowerCase();
    if (window.keys.hasOwnProperty(key)) {
        window.keys[key] = true;
        
        // INSTANT CANCEL MOUSE PATH
        if (VOID_DATA.mapName !== 'space') {
            player.finalDestX = null;
            player.finalDestY = null;
        }
    }
});

window.addEventListener('keyup', (e) => {
    const key = e.key.toLowerCase();
    if (window.keys.hasOwnProperty(key)) {
        window.keys[key] = false;
    }
});

document.getElementById('gameCanvas').addEventListener('mousedown', (e) => {
    if (VOID_DATA.mapName !== 'space') {
        // WORLD: Force snap the destination to the grid
        player.finalDestX = Math.floor(e.clientX / 32) * 32;
        player.finalDestY = Math.floor(e.clientY / 32) * 32;
    } else {
        // SPACE: Free fly
        player.targetX = e.clientX - (player.size / 2);
        player.targetY = e.clientY - (player.size / 2);
        player.isMoving = true;
    }
});

let saveTimeout;
function savePosition() {
    clearTimeout(saveTimeout);
    saveTimeout = setTimeout(() => {
        // We use Math.round to ensure the DB gets clean integers
        fetch(`update_pos.php?id=${VOID_DATA.charId}&x=${Math.round(player.x)}&y=${Math.round(player.y)}&map=${VOID_DATA.mapName}`);
    }, 300); 
}