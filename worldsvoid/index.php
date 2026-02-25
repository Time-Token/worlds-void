<?php
session_start();
$db = new mysqli("localhost", "timetoken", "Tuf@18240833", "worlds_void");

// 1. Check Admin Status
$is_admin = false;
if (isset($_SESSION['account_id'])) {
    $acc_id = $_SESSION['account_id'];
    $admin_check = $db->query("SELECT is_admin FROM accounts WHERE id = '$acc_id'")->fetch_assoc();
    if ($admin_check && $admin_check['is_admin'] == 1) { $is_admin = true; }
}

// 2. Fetch Latest News (Now including image_url)
$news_query = $db->query("SELECT news.*, accounts.name as author_name FROM news JOIN accounts ON news.author_id = accounts.id ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Worlds Void - Official Server</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Righteous&family=Share+Tech+Mono&family=Orbitron:wght@700&display=swap');
        
        body { 
            margin: 0; background: linear-gradient(to bottom, #090a0f 0%, #1a0b2e 100%); 
            color: #00f3ff; font-family: 'Share Tech Mono', monospace; 
            min-height: 100vh; overflow-x: hidden; background-attachment: fixed; 
        }

        /* Retro Grid Floor */
        body::after { 
            content: ''; position: fixed; bottom: 0; left: 0; width: 100%; height: 40vh; 
            background: linear-gradient(transparent 65%, #ff007f 100%), linear-gradient(90deg, rgba(0, 243, 255, 0.1) 1px, transparent 1px); 
            background-size: 100% 100%, 50px 100%; perspective: 500px; transform: rotateX(60deg); 
            transform-origin: bottom; z-index: -1; pointer-events: none; 
        }

        .site-header { text-align: center; padding: 40px; }
        .site-title { font-family: 'Righteous', cursive; font-size: 5em; color: #fff; text-shadow: 4px 4px 0px #000, 0 0 20px #ff007f; margin: 0; text-transform: uppercase; }

        .nav-bar { background: rgba(0,0,0,0.8); border-top: 3px solid #ff007f; border-bottom: 3px solid #00f3ff; text-align: center; padding: 15px; margin-bottom: 40px; }
        .nav-bar a { color: #fff; text-decoration: none; padding: 10px 25px; font-family: 'Orbitron', sans-serif; transition: 0.3s; }
        .nav-bar a:hover { color: #00f3ff; text-shadow: 0 0 10px #00f3ff; }

        .container { display: flex; max-width: 1200px; margin: 0 auto; gap: 30px; padding: 0 20px; }
        .panel { background: rgba(10, 5, 20, 0.85); border: 2px solid #00f3ff; padding: 25px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,243,255,0.2); backdrop-filter: blur(5px); }

        .main-content { flex: 3; }
        .sidebar { flex: 1; }

        /* News Styling */
        .news-post { border-bottom: 1px dashed #444; padding: 25px 0; }
        .news-post:last-child { border-bottom: none; }
        .news-image { width: 100%; border: 1px solid #ff007f; border-radius: 5px; margin-bottom: 15px; box-shadow: 0 0 10px rgba(255, 0, 127, 0.3); }
        .news-title { font-family: 'Orbitron'; color: #ff007f; margin: 0 0 10px 0; }
        .news-body { color: #e0e0e0; line-height: 1.6; font-size: 1.1em; }
        /* Ensuring Quill HTML styles (lists, bold) look right */
        .news-body ul { padding-left: 20px; color: #00f3ff; }
        .news-body strong { color: #fff; text-shadow: 0 0 5px #fff; }

        .btn { display: block; padding: 15px; text-decoration: none; font-family: 'Orbitron', sans-serif; text-align: center; margin-bottom: 15px; border: 2px solid #00f3ff; color: #fff; transition: 0.3s; text-transform: uppercase; background: rgba(0, 243, 255, 0.1); }
        .btn:hover { background: #00f3ff; color: #000; box-shadow: 0 0 15px #00f3ff; }
        .btn-portal { border-color: #ff007f; color: #ff007f; background: rgba(255,0,127,0.1); }
        .btn-portal:hover { background: #ff007f; color: #fff; }
    </style>
</head>
<body>
    <div class="site-header">
        <h1 class="site-title">WORLDS VOID</h1>
    </div>

    <div class="nav-bar">
        <a href="index.php">NEWS DATA</a>
        <a href="highscores.php">LEADERBOARDS</a>
        <?php if ($is_admin): ?> <a href="admin.php" style="color:#ffff00;">SYSTEM OVERRIDE (ADMIN)</a> <?php endif; ?>
    </div>

    <div class="container">
        <div class="main-content">
            <div class="panel">
                <h2 style="font-family:'Orbitron'; color:#ff007f; border-bottom: 2px solid #ff007f; padding-bottom: 10px;">COMMUNICATION LOG</h2>
                
                <?php if ($news_query->num_rows > 0): while($n = $news_query->fetch_assoc()): ?>
                    <div class="news-post">
                        <h3 class="news-title"><?php echo htmlspecialchars($n['title']); ?></h3>
                        
                        <?php if (!empty($n['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($n['image_url']); ?>" class="news-image">
                        <?php endif; ?>

                        <div class="news-body">
                            <?php echo $n['content']; // This allows HTML from the Quill editor ?>
                        </div>
                        
                        <div style="margin-top: 15px;">
                            <small style="color:#666; letter-spacing: 1px;">TRANSMITTED BY: <b style="color:#00f3ff;"><?php echo strtoupper($n['author_name']); ?></b></small>
                        </div>
                    </div>
                <?php endwhile; else: ?>
                    <p>No active transmissions found in the Void.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="sidebar">
            <div class="panel">
                <h3 style="font-family:'Orbitron'; text-align:center; border-bottom: 1px solid #00f3ff; padding-bottom: 10px;">UPLINK TERMINAL</h3>
                <?php if (isset($_SESSION['account_id'])): ?>
                    <p style="text-align:center; color:#00ff00; font-weight: bold;">[ IDENTITY VERIFIED ]</p>
                    <a href="portal.php" class="btn btn-portal">ENTER THE VOID</a>
                    <a href="settings.php" class="btn" style="border-color:#ffff00; color:#ffff00;">ACCOUNT LINKING</a>
                    <a href="logout.php" class="btn" style="border-color:#ff0000; color:#ff0000;">ABORT CONNECTION</a>
                <?php else: ?>
                    <a href="auth_discord.php" class="btn" style="border-color:#5865F2; color:#5865F2;">DISCORD UPLINK</a>
                    <a href="auth_google.php" class="btn" style="border-color:#DB4437; color:#DB4437;">GOOGLE UPLINK</a>
                    <a href="auth_steam.php" class="btn" style="border-color:#aaa; color:#aaa;">STEAM UPLINK</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>