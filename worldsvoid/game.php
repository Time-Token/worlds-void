<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Worlds Void - Collision Upgrade</title>
    <style>
        body { margin: 0; padding: 0; overflow: hidden; background: #000; font-family: 'Share Tech Mono', monospace; }
        canvas { display: block; touch-action: none; outline: none; }
        
        #phone-interface {
            position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
            width: 280px; height: 450px; background: rgba(15, 15, 25, 0.95);
            border: 3px solid #00f3ff; border-radius: 20px; display: none; 
            flex-direction: column; padding: 20px; box-sizing: border-box;
            color: #00f3ff; box-shadow: 0 0 40px rgba(0, 243, 255, 0.3);
            z-index: 1000; backdrop-filter: blur(10px);
        }
        .phone-header { border-bottom: 2px solid #ff007f; padding-bottom: 10px; margin-bottom: 20px; text-align: center; font-weight: bold; }
        .phone-btn { 
            background: rgba(0, 243, 255, 0.05); border: 1px solid #00f3ff; 
            color: #00f3ff; padding: 12px; margin: 8px 0; text-align: center; cursor: pointer;
            text-transform: uppercase; font-size: 0.9em; transition: 0.2s;
        }
        .phone-btn:hover { background: #00f3ff; color: #000; }
    </style>
</head>
<body>
    <canvas id="gameCanvas"></canvas>
    
    <div id="phone-interface">
        <div class="phone-header">V-LINK TERMINAL</div>
        <div class="phone-btn">Account Data</div>
        <div class="phone-btn">Void Map</div>
        <div class="phone-btn">Settings</div>
        <a href="index.php" style="text-decoration:none; margin-top: auto;">
            <div class="phone-btn" style="border-color:#ff007f; color:#ff007f;">Disconnect</div>
        </a>
    </div>

    <script type="module" src="js/engine/main.js"></script>
</body>
</html>