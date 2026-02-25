<?php
session_start();

// --- 1. THE HIGHLANDER KICK EJECTION SEAT ---
if (isset($_GET['action']) && $_GET['action'] == 'kicked') {
    session_destroy(); // Burn the old session to the ground
    die("
    <body style='background:#050505; color:#ff0055; font-family:\"Share Tech Mono\", monospace; text-align:center; padding-top:100px;'>
        <div style='border:2px solid #ff0055; padding:40px; display:inline-block; background:rgba(255,0,85,0.1); box-shadow: 0 0 20px #ff0055; border-radius:10px;'>
            <h2 style='text-transform:uppercase; letter-spacing:2px;'>Uplink Severed</h2>
            <p style='color:#fff; font-size:1.1em;'>Your account was accessed from another terminal.</p>
            <br><br>
            <a href='index.php' style='color:#00f3ff; text-decoration:none; border:1px solid #00f3ff; padding:10px;'>[ RECONNECT TO TERMINAL ]</a>
        </div>
    </body>");
}

$db = new mysqli("localhost", "timetoken", "Tuf@18240833", "worlds_void");

if (!isset($_SESSION['account_id'])) { header("Location: index.php"); exit; }
$acc_id = $_SESSION['account_id'];

// --- 2. UPDATE SESSION TOKEN (Unless returning from a blocked character attempt) ---
if (!isset($_GET['error'])) {
    $current_session = session_id();
    $db->query("UPDATE accounts SET session_token = '$current_session' WHERE id = '$acc_id'");
}

$acc = $db->query("SELECT time_tokens FROM accounts WHERE id = '$acc_id'")->fetch_assoc();
$players = $db->query("SELECT * FROM players WHERE account_id = '$acc_id'");

// Check for unread messages
$unread_q = $db->query("SELECT COUNT(*) as unread FROM account_messages WHERE account_id = '$acc_id' AND is_read = 0");
$unread_count = $unread_q ? $unread_q->fetch_assoc()['unread'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Worlds Void - Active Manifests</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Share+Tech+Mono&display=swap');
        
        body { margin: 0; background: #090a0f; color: #00f3ff; font-family: 'Share Tech Mono', monospace; padding: 40px 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        
        .header-box { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; border-bottom: 2px solid #ff007f; padding-bottom: 15px; margin-bottom: 30px; gap: 15px; }
        h1 { font-family: 'Orbitron', sans-serif; color: #fff; margin: 0; text-shadow: 0 0 10px #ff007f; text-transform: uppercase; font-size: 1.8em; }
        
        .top-stats { text-align: right; flex-grow: 1; }
        .token-balance { color: #ffff00; font-family: 'Orbitron', sans-serif; font-size: 1.2em; text-shadow: 0 0 10px rgba(255,255,0,0.5); padding: 5px; }
        
        .inbox-alert { display: inline-block; background: #ff0055; color: #fff; padding: 8px 15px; text-decoration: none; font-family: 'Orbitron', sans-serif; font-weight: bold; margin-top: 10px; animation: pulse 1.5s infinite; border: 1px solid #ff0055; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(255,0,85,0.7); } 70% { box-shadow: 0 0 0 10px rgba(255,0,85,0); } 100% { box-shadow: 0 0 0 0 rgba(255,0,85,0); } }
        
        .create-btn { display: block; text-align: center; padding: 15px; border: 2px dashed #00ff00; color: #00ff00; text-decoration: none; font-family: 'Orbitron', sans-serif; font-size: 1.2em; margin-bottom: 30px; background: rgba(0,255,0,0.05); transition: 0.3s; text-transform: uppercase; }
        .create-btn:hover { background: #00ff00; color: #000; box-shadow: 0 0 20px #00ff00; }

        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .card { background: rgba(0, 243, 255, 0.05); border: 2px solid #00f3ff; padding: 25px; transition: 0.3s; }
        .card h2 { margin-top: 0; color: #fff; font-family: 'Orbitron', sans-serif; border-bottom: 1px dashed #333; padding-bottom: 10px; margin-bottom: 15px; }
        .stats { color: #aaa; margin-bottom: 20px; line-height: 1.6; }
        
        .death-clock { background: rgba(255,0,0,0.1); border: 1px solid #ff0000; color: #ff0000; padding: 10px; margin-bottom: 15px; text-align: center; font-weight: bold; font-family: 'Orbitron', sans-serif; }
        
        .btn { display: block; padding: 12px; text-align: center; text-decoration: none; font-family: 'Orbitron', sans-serif; font-weight: bold; margin-bottom: 10px; text-transform: uppercase; border: 2px solid; transition: 0.3s; }
        .btn-play { background: rgba(0, 243, 255, 0.1); border-color: #00f3ff; color: #00f3ff; }
        .btn-play:hover { background: #00f3ff; color: #000; box-shadow: 0 0 15px #00f3ff; }
        .btn-shop { background: rgba(255, 0, 127, 0.1); border-color: #ff007f; color: #ff007f; }
        .btn-shop:hover { background: #ff007f; color: #fff; box-shadow: 0 0 15px #ff007f; }
        
        .msg { background: rgba(0,255,0,0.1); color: #00ff00; border: 1px solid #00ff00; padding: 15px; text-align: center; margin-bottom: 20px; font-family: 'Orbitron'; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-box">
            <h1>Active Manifests</h1>
            <div class="top-stats">
                <div class="token-balance">BALANCE: <?php echo number_format($acc['time_tokens']); ?> TT</div>
                
                <a href="messages.php" class="<?php echo ($unread_count > 0) ? 'inbox-alert' : ''; ?>" style="<?php echo ($unread_count == 0) ? 'color:#00f3ff; text-decoration:none; display:block; margin-top:10px; border: 1px solid #00f3ff; padding: 5px 10px;' : ''; ?>">
                    SECURE INBOX (<?php echo $unread_count; ?> UNREAD)
                </a>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="msg">TRANSACTION / INITIALIZATION SUCCESSFUL.</div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <?php if ($_GET['error'] == 'online'): ?>
                <div class="death-clock">⚠ ACCESS DENIED: This manifest is already active. Please wait 5 seconds. ⚠</div>
            <?php elseif ($_GET['error'] == 'account_active'): ?>
                <div class="death-clock">⚠ ACCESS DENIED: Another manifest on this account is currently active! ⚠</div>
            <?php endif; ?>
        <?php endif; ?>

        <a href="create_character.php" class="create-btn">+ Initialize New Manifest +</a>

        <div class="grid">
            <?php if ($players && $players->num_rows > 0): ?>
                <?php while($p = $players->fetch_assoc()): 
                    
                    $is_locked = false;
                    $days_left = 0;
                    if ($p['must_rename_by'] != null) {
                        $deadline = strtotime($p['must_rename_by']);
                        $now = time();
                        if ($now >= $deadline) {
                            $db->query("DELETE FROM players WHERE id = " . $p['id']);
                            continue; 
                        } else {
                            $is_locked = true;
                            $days_left = ceil(($deadline - $now) / 86400);
                        }
                    }
                ?>
                    <div class="card" style="<?php if($is_locked) echo 'border-color:#ff0000; box-shadow: 0 0 15px rgba(255,0,0,0.3);'; ?>">
                        <h2><?php echo htmlspecialchars($p['name']); ?></h2>
                        
                        <?php if ($is_locked): ?>
                            <div class="death-clock">
                                ⚠ PURGE IN: <?php echo $days_left; ?> DAYS ⚠<br>
                                <span style="font-size:0.7em; color:#fff;">NAME RESTRICTED. REWRITE REQUIRED.</span>
                            </div>
                        <?php endif; ?>

                        <div class="stats">
                            LEVEL: <span style="color:#fff;"><?php echo $p['level']; ?></span><br>
                            GENETICS: <span style="color:#fff;"><?php echo ($p['sex'] == 1) ? "MALE" : "FEMALE"; ?></span><br>
                            DERMIS: <span style="color:#fff;">TONE <?php echo $p['skin_tone']; ?></span>
                        </div>
                        
                        <?php if (!$is_locked): ?>
                            <a href="game.php?id=<?php echo $p['id']; ?>" class="btn btn-play">Uplink to Engine</a>
                        <?php else: ?>
                            <div class="btn" style="background:#333; color:#666; border-color:#333; cursor:not-allowed;">ENGINE LOCKED</div>
                        <?php endif; ?>

                        <a href="customization.php?char_id=<?php echo $p['id']; ?>" class="btn btn-shop">Modify Entity (Shop)</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align:center; font-size:1.2em; color:#666; grid-column: 1 / -1;">No active manifests found. Initialize a new entity above.</p>
            <?php endif; ?>
        </div>
        
        <div style="text-align: center; margin-top: 40px;">
            <a href="logout.php" style="color:#666; text-decoration:none;">[ DISCONNECT FROM TERMINAL ]</a>
        </div>
    </div>
</body>
</html>