<?php
session_start();
$db = new mysqli("localhost", "timetoken", "Tuf@18240833", "worlds_void");

if (!isset($_SESSION['account_id'])) { header("Location: index.php"); exit; }
$acc_id = $_SESSION['account_id'];

// Mark all messages as read
$db->query("UPDATE account_messages SET is_read = 1 WHERE account_id = '$acc_id'");

// Fetch messages
$messages = $db->query("SELECT * FROM account_messages WHERE account_id = '$acc_id' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Worlds Void - Secure Comms</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Share+Tech+Mono&display=swap');
        
        body { background: #050505; color: #00f3ff; font-family: 'Share Tech Mono', monospace; padding: 40px 20px; margin: 0; min-height: 100vh; }
        .inbox-panel { max-width: 800px; margin: 0 auto; border: 2px solid #00f3ff; background: rgba(0, 243, 255, 0.05); padding: 30px; box-shadow: 0 0 20px rgba(0, 243, 255, 0.2); }
        h1 { font-family: 'Orbitron', sans-serif; color: #fff; text-shadow: 0 0 10px #00f3ff; text-align: center; text-transform: uppercase; border-bottom: 2px solid #00f3ff; padding-bottom: 15px; margin-top: 0;}
        
        .msg-box { border: 1px solid #333; padding: 25px; margin-bottom: 25px; background: rgba(0,0,0,0.8); }
        .msg-title { color: #ff0055; font-family: 'Orbitron', sans-serif; font-size: 1.3em; margin-bottom: 10px; text-transform: uppercase; text-shadow: 0 0 5px #ff0055; }
        .msg-date { color: #666; font-size: 0.9em; margin-bottom: 20px; border-bottom: 1px dashed #333; padding-bottom: 10px; }
        .msg-body { color: #e0e0e0; line-height: 1.6; font-size: 1.1em; }
        
        .back-link { display: block; text-align: center; color: #fff; text-decoration: none; margin-top: 30px; font-size: 1.2em; transition: 0.3s; }
        .back-link:hover { color: #ff0055; text-shadow: 0 0 10px #ff0055; }
    </style>
</head>
<body>
    <div class="inbox-panel">
        <h1>Secure Comms Inbox</h1>
        
        <?php if ($messages && $messages->num_rows > 0): ?>
            <?php while($m = $messages->fetch_assoc()): ?>
                <div class="msg-box">
                    <div class="msg-title"><?php echo htmlspecialchars($m['title']); ?></div>
                    <div class="msg-date">TRANSMITTED: <?php echo $m['created_at']; ?></div>
                    <div class="msg-body"><?php echo $m['message']; ?></div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center; color:#666; font-size: 1.2em; padding: 40px 0;">No secure transmissions received from the Void.</p>
        <?php endif; ?>

        <a href="portal.php" class="back-link">[ ABORT COMMS & RETURN TO MANIFEST PORTAL ]</a>
    </div>
</body>
</html>