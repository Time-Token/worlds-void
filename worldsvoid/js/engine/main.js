import { InputHandler } from './Input.js';
import { PhysicsEngine } from './Physics.js';
import { Renderer } from './Renderer.js';

const canvas = document.getElementById('gameCanvas');
const ctx = canvas.getContext('2d');
const phoneUI = document.getElementById('phone-interface');

const playerSprite = new Image();
playerSprite.src = './images/player.png';

// 0: Floor, 1: Wall, 2: Grass
const worldMap = {
    tileSize: 100,
    cols: 10,
    rows: 10,
    tiles: [
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
        1, 0, 0, 0, 2, 2, 0, 0, 0, 1,
        1, 0, 1, 0, 2, 2, 0, 1, 0, 1,
        1, 0, 1, 0, 0, 0, 0, 1, 0, 1,
        1, 0, 0, 0, 0, 0, 0, 0, 0, 1,
        1, 2, 2, 0, 0, 0, 0, 0, 0, 1,
        1, 2, 2, 0, 1, 1, 1, 0, 0, 1,
        1, 0, 0, 0, 1, 0, 0, 0, 0, 1,
        1, 0, 0, 0, 1, 0, 0, 0, 0, 1,
        1, 1, 1, 1, 1, 1, 1, 1, 1, 1
    ]
};

const gameState = {
    player: { x: 500, y: 500, speed: 250, size: 40 },
    camera: { x: 0, y: 0 },
    time: { last: 0, accumulator: 0, dt: 1 / 60 }
};

// Initialize Systems
const physics = new PhysicsEngine(worldMap);
const renderer = new Renderer(ctx, canvas, playerSprite, worldMap);
const input = new InputHandler();

input.init(canvas, gameState);

// The Engine Loop
function gameLoop(timestamp) {
    let frameTime = (timestamp - gameState.time.last) / 1000;
    gameState.time.last = timestamp;
    if (frameTime > 0.1) frameTime = 0.1; // Prevent spiral of death

    gameState.time.accumulator += frameTime;

    // UI Sync
    if (phoneUI) {
        phoneUI.style.display = input.state.isPhoneOpen ? 'flex' : 'none';
    }

    // Physics Step
    while (gameState.time.accumulator >= gameState.time.dt) {
        physics.update(gameState, input.state, gameState.time.dt);
        gameState.time.accumulator -= gameState.time.dt;
    }

    // Render Step
    renderer.draw(gameState, input.state);

    requestAnimationFrame(gameLoop);
}

// Start Engine
requestAnimationFrame(gameLoop);

// Handle Resizing dynamically
window.addEventListener('resize', () => {
    renderer.resize();
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
});
canvas.width = window.innerWidth;
canvas.height = window.innerHeight;