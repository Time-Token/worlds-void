<?php
session_start();
$db = new mysqli("localhost", "timetoken", "Tuf@18240833", "worlds_void");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Worlds Void - Official Home</title>
    <style>
        body { background: #050505; color: #00ffcc; font-family: "Courier New", Courier, monospace; margin: 0; display: flex; justify-content: center; }
        #wrapper { width: 1200px; display: grid; grid-template-columns: 250px 1fr 250px; gap: 20px; padding: 20px; }
        .sidebar { background: #111; border: 1px solid #00aaaa; padding: 15px; box-shadow: 0 0 10px #004444; height: fit-content; }
        .content { background: #0a0a0a; border: 1px solid #00aaaa; padding: 20px; min-height: 800px; }
        h2 { border-bottom: 2px solid #00aaaa; padding-bottom: 5px; color: #fff; font-size: 18px; }
        .news-post { border-left: 3px solid #00ffcc; padding-left: 15px; margin-bottom: 30px; }
        .btn { display: block; background: #00aaaa; color: #000; text-align: center; padding: 10px; text-decoration: none; font-weight: bold; margin-top: 10px; }
        .btn:hover { background: #00ffcc; }
        table { width: 100%; font-size: 12px; border-collapse: collapse; }
        td { padding: 5px; border-bottom: 1px solid #222; }
    </style>
</head>
<body>
    <div id="wrapper">
        <div class="sidebar">
            <h2>ACCOUNT</h2>
            <?php if(isset($_SESSION["account_id"])): ?>
                <p>Status: ONLINE</p>
                <a href="/worldsvoid/portal.php" class="btn">GO TO PORTAL</a>
                <a href="/worldsvoid/logout.php" style="color:#ff0055; font-size:10px;">[ LOGOUT ]</a>
            <?php else: ?>
                <p>Join the Void today.</p>
                <a href="/worldsvoid/index.html" class="btn">LOGIN / JOIN</a>
            <?php endif; ?>
            
            <h2>COMMUNITY</h2>
            <p>> Discord</p>
            <p>> Library</p>
            <p>> Map</p>
        </div>

        <div class="content">
            <h1>WORLDS VOID: NEWS</h1>
            <div class="news-post">
                <h3>V0.1 Alpha: The Engine Awakens</h3>
                <p>The core engine is now live. Players can now link Google, Discord, and Steam accounts to a single identity. Glide movement has been implemented for all planetary explorers.</p>
                <small>Posted: Feb 22, 2026</small>
            </div>
        </div>

        <div class="sidebar">
            <h2>TOP EXPLORERS</h2>
            <table>
                <?php
                $top = $db->query("SELECT name, level FROM players ORDER BY level DESC LIMIT 10");
                $rank = 1;
                if($top) {
                    while($row = $top->fetch_assoc()) {
                        echo "<tr><td>$rank.</td><td>".$row[name]."</td><td style=color:#fff>Lvl ".$row[level]."</td></tr>";
                        $rank++;
                    }
                }
                ?>
            </table>

            <h2 style="margin-top:20px;">SERVER INFO</h2>
            <p>Uptime: 99.9%</p>
            <p>Players Online: <?php echo $db->query("SELECT id FROM players")->num_rows; ?></p>
        </div>
    </div>
</body>
</html>
