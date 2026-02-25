// Top of main.js
import { InputHandler } from './Input.js';
import { PhysicsEngine } from './Physics.js'; // POINT TO THE NEW FILE
import { Renderer } from './Renderer.js';

const canvas = document.getElementById('gameCanvas');
const ctx = canvas.getContext('2d');
const phoneUI = document.getElementById('phone-interface');

// 1. Advanced Game State
const gameState = {
    player: { x: 500, y: 500, speed: 300, size: 30, color: '#00f3ff' },
    camera: { x: 0, y: 0 },
    monsters: [
        { x: 300, y: 300, hp: 100, color: '#ff007f' }, // Test monster!
        { x: 700, y: 600, hp: 100, color: '#ff007f' }
    ],
    time: { last: 0, accumulator: 0, dt: 1 / 60 }
};

// 2. Initialize Systems
const physics = new PhysicsEngine();
const renderer = new Renderer(ctx, canvas);
const input = new InputHandler();

input.init(canvas, gameState);

// 3. The Engine Loop
function gameLoop(timestamp) {
    let frameTime = (timestamp - gameState.time.last) / 1000;
    gameState.time.last = timestamp;
    if (frameTime > 0.25) frameTime = 0.25; // Prevent spiral of death

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
});