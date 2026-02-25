<?php
session_start();
$db = new mysqli("localhost", "timetoken", "Tuf@18240833", "worlds_void");

if (!isset($_SESSION["account_id"])) { header("Location: index.html"); exit; }
$acc_id = $_SESSION["account_id"];

// Count active links to prevent lockouts
$user = $db->query("SELECT * FROM accounts WHERE id = '$acc_id'")->fetch_assoc();
$link_count = ($user['discord_id'] ? 1 : 0) + ($user['google_id'] ? 1 : 0) + ($user['steam_id'] ? 1 : 0);

if (isset($_GET["unlink"])) {
    $target = $db->real_escape_string($_GET["unlink"]);
    $allowed = ["discord_id", "google_id", "steam_id"];
    if (in_array($target, $allowed) && $link_count > 1) {
        $db->query("UPDATE accounts SET $target = NULL WHERE id = '$acc_id'");
        header("Location: settings.php"); exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Worlds Void - Settings</title>
    <style>
        body { background: #0a0a0a; color: #00ffcc; font-family: monospace; text-align: center; padding: 20px; }
        .box { border: 2px solid #00aaaa; padding: 30px; background: #111; display: inline-block; width: 400px; box-shadow: 0 0 20px #004444; }
        .nav-btn { background: #00aaaa; color: #000; padding: 10px; text-decoration: none; font-weight: bold; border: 1px solid #00ffff; }
        .item { border-bottom: 1px solid #222; padding: 15px 0; text-align: left; }
        .action { float: right; color: #ff0055; text-decoration: none; font-size: 12px; }
        .primary { float: right; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <a href="portal.php" class="nav-btn"><< BACK TO PORTAL</a><br><br>
    <div class="box">
        <h2>ACCOUNT_CORE</h2>
        <div class="item">DISCORD: 
            <?php if($user['discord_id']) { echo "✅"; echo $link_count > 1 ? " <a href='?unlink=discord_id' class='action'>UNLINK</a>" : "<span class='primary'>[PRIMARY]</span>"; } else { echo "<a href='auth_discord.php' class='action' style='color:#00ffcc;'>LINK</a>"; } ?>
        </div>
        <div class="item">GOOGLE: 
            <?php if($user['google_id']) { echo "✅"; echo $link_count > 1 ? " <a href='?unlink=google_id' class='action'>UNLINK</a>" : "<span class='primary'>[PRIMARY]</span>"; } else { echo "<a href='auth_google.php' class='action' style='color:#00ffcc;'>LINK</a>"; } ?>
        </div>
        <div class="item">STEAM: 
            <?php if($user['steam_id']) { echo "✅"; echo $link_count > 1 ? " <a href='?unlink=steam_id' class='action'>UNLINK</a>" : "<span class='primary'>[PRIMARY]</span>"; } else { echo "<a href='auth_steam.php' class='action' style='color:#00ffcc;'>LINK</a>"; } ?>
        </div>
    </div>
</body>
</html>