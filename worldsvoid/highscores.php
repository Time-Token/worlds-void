<?php
session_start();
$db = new mysqli("localhost", "timetoken", "Tuf@18240833", "worlds_void");

// Fetch Top 10 Players
$top_players = $db->query("SELECT name, level, experience FROM players ORDER BY level DESC, experience DESC LIMIT 10");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Worlds Void - Leaderboards</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Share+Tech+Mono&display=swap');
        body { background: #050505; color: #00f3ff; font-family: 'Share Tech Mono', monospace; padding: 40px; }
        .leaderboard-panel { max-width: 800px; margin: 0 auto; border: 2px solid #00f3ff; background: rgba(0, 243, 255, 0.05); padding: 30px; box-shadow: 0 0 20px rgba(0, 243, 255, 0.2); }
        h1 { font-family: 'Orbitron'; text-align: center; text-transform: uppercase; color: #ff007f; text-shadow: 0 0 10px #ff007f; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { border-bottom: 2px solid #ff007f; color: #fff; padding: 15px; text-align: left; font-family: 'Orbitron'; }
        td { padding: 15px; border-bottom: 1px solid #222; font-size: 1.1em; }
        tr:hover { background: rgba(0, 243, 255, 0.1); }
        .rank { color: #ff007f; font-weight: bold; width: 50px; }
    </style>
</head>
<body>
    <div class="leaderboard-panel">
        <h1>Global Ranking: The Void Top 10</h1>
        <table>
            <thead>
                <tr>
                    <th>RANK</th>
                    <th>ENTITY NAME</th>
                    <th>LEVEL</th>
                    <th>EXP</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $rank = 1;
                while($p = $top_players->fetch_assoc()): ?>
                <tr>
                    <td class="rank">#<?php echo $rank++; ?></td>
                    <td style="color:#fff;"><?php echo htmlspecialchars($p['name']); ?></td>
                    <td style="color:#00f3ff;"><?php echo $p['level']; ?></td>
                    <td style="color:#666; font-size: 0.8em;"><?php echo number_format($p['experience']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <br>
        <a href="index.php" style="color:#ff007f; text-decoration:none;">[ BACK TO TERMINAL ]</a>
    </div>
</body>
</html>