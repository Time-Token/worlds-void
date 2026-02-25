window.MAP_space = {
    name: "space",
    movementStyle: "free", // Tells controls_pc.js to use mouse-follow logic
    speed: 500,
    playerColor: "#00f3ff",
    
    // Draws the starry background BEFORE the camera moves
    drawStaticBackground: function(ctx, canvas, camX, camY) {
        ctx.fillStyle = "rgba(255, 255, 255, 0.5)";
        // We add a slight parallax effect so the stars move slowly as you fly
        for(let i=0; i<100; i++) {
            let starX = ((i*117) - (camX * 0.1)) % canvas.width;
            let starY = ((i*231) - (camY * 0.1)) % canvas.height;
            if (starX < 0) starX += canvas.width;
            if (starY < 0) starY += canvas.height;
            ctx.fillRect(starX, starY, 2, 2);
        }
    },
    
    // The Free-Floating Movement Engine
    updateMovement: function(player, step) {
        let spaceMoved = false;
        if (window.keys && window.keys.w) { player.y -= step; spaceMoved = true; }
        if (window.keys && window.keys.s) { player.y += step; spaceMoved = true; }
        if (window.keys && window.keys.a) { player.x -= step; spaceMoved = true; }
        if (window.keys && window.keys.d) { player.x += step; spaceMoved = true; }

        if (spaceMoved) {
            player.isMoving = false;
            player.targetX = player.x;
            player.targetY = player.y;
        }

        if (player.isMoving) {
            let dx = player.targetX - player.x;
            let dy = player.targetY - player.y;
            let distance = Math.sqrt(dx * dx + dy * dy);
            if (distance > step) {
                player.x += (dx / distance) * step;
                player.y += (dy / distance) * step;
            } else {
                player.x = player.targetX;
                player.y = player.targetY;
                player.isMoving = false;
            }
        }
    },
    
    // Draws the Spaceship Triangle
    drawPlayer: function(ctx, player) {
        ctx.beginPath();
        ctx.moveTo(player.x + 16, player.y);
        ctx.lineTo(player.x, player.y + 32);
        ctx.lineTo(player.x + 32, player.y + 32);
        ctx.fill();
    }
};