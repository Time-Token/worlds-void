window.MAP_world_1 = {
    name: "world_1",
    movementStyle: "grid", // Tells controls_pc.js to snap mouse clicks to 32x32
    speed: 250,
    playerColor: "#ff007f",
    
    isSolid: function(x, y) {
        // We will add collision detection in here later!
        return false; 
    },
    
    // Draws the grid AFTER the camera moves so it stays locked to the ground
    drawWorld: function(ctx) {
        ctx.strokeStyle = '#111';
        ctx.lineWidth = 1;
        for(let i=-2000; i<4000; i+=32) {
            ctx.beginPath(); ctx.moveTo(i, -2000); ctx.lineTo(i, 4000); ctx.stroke();
            ctx.beginPath(); ctx.moveTo(-2000, i); ctx.lineTo(4000, i); ctx.stroke();
        }
    },
    
    // The Rigid 32x32 Pathing Engine
    updateMovement: function(player, step) {
        if (!player.isMoving) {
            let nextX = player.x;
            let nextY = player.y;
            let stepInitiated = false;

            if (window.keys && window.keys.w) { nextY -= 32; stepInitiated = true; player.finalDestX = null; player.finalDestY = null; }
            else if (window.keys && window.keys.s) { nextY += 32; stepInitiated = true; player.finalDestX = null; player.finalDestY = null; }
            else if (window.keys && window.keys.a) { nextX -= 32; stepInitiated = true; player.finalDestX = null; player.finalDestY = null; }
            else if (window.keys && window.keys.d) { nextX += 32; stepInitiated = true; player.finalDestX = null; player.finalDestY = null; }
            
            else if (player.finalDestX !== null && player.finalDestY !== null) {
                let curX = Math.round(player.x);
                let curY = Math.round(player.y);
                if (curX < player.finalDestX) { nextX += 32; stepInitiated = true; }
                else if (curX > player.finalDestX) { nextX -= 32; stepInitiated = true; }
                else if (curY < player.finalDestY) { nextY += 32; stepInitiated = true; }
                else if (curY > player.finalDestY) { nextY -= 32; stepInitiated = true; }
                else { player.finalDestX = null; player.finalDestY = null; }
            }
            
            if (stepInitiated) {
                if (this.isSolid(nextX, nextY)) {
                    player.finalDestX = null; player.finalDestY = null;
                } else {
                    player.targetX = Math.round(nextX); player.targetY = Math.round(nextY); player.isMoving = true;
                }
            }
        }

        if (player.isMoving) {
            let dist = 0;
            if (player.x !== player.targetX) {
                dist = Math.abs(player.targetX - player.x);
                player.x += (player.targetX > player.x) ? Math.min(step, dist) : -Math.min(step, dist);
            } else if (player.y !== player.targetY) {
                dist = Math.abs(player.targetY - player.y);
                player.y += (player.targetY > player.y) ? Math.min(step, dist) : -Math.min(step, dist);
            }

            if (Math.round(player.x) === player.targetX && Math.round(player.y) === player.targetY) {
                player.x = player.targetX; player.y = player.targetY; player.isMoving = false;
            }
        }
    },
    
    // Draws the Square Avatar
    drawPlayer: function(ctx, player) {
        ctx.fillRect(player.x, player.y, player.size, player.size);
    }
};