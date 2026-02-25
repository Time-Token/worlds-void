<div class="hud">
    <div class="status-bar" style="position: relative;">
        <a href="portal.php" style="position: absolute; top: 10px; right: 15px; color: #00f3ff; text-decoration: none; font-size: 0.8em; border: 1px solid #00f3ff; padding: 4px 8px; border-radius: 4px; font-weight: bold; transition: 0.2s;">CHARACTERS</a>
        
        <div style="font-size: 0.9em; color: #00f3ff; margin-bottom: 5px;">ENTITY: <?php echo htmlspecialchars($p['name']); ?></div>
        <div style="font-size: 0.7em; color: #ff007f;">LEVEL: <?php echo $p['level']; ?> | EXP: <?php echo number_format($p['experience']); ?></div>
        <div id="realm-indicator" style="font-size: 0.7em; color: #ffff00; margin-top: 5px;">MAP: <?php echo strtoupper($map_name); ?></div>
    </div>
</div>