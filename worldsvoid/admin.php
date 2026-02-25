<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$db = new mysqli("localhost", "timetoken", "Tuf@18240833", "worlds_void");

// 1. SECURITY CHECK
if (!isset($_SESSION['account_id'])) { header("Location: index.php"); exit; }
$acc_id = $_SESSION['account_id'];
$admin_check = $db->query("SELECT is_admin FROM accounts WHERE id = '$acc_id'")->fetch_assoc();
if (!$admin_check || $admin_check['is_admin'] != 1) { die("UNAUTHORIZED ACCESS."); }

// --- HANDLE SYSTEM SETTINGS (DYNAMIC ECONOMY) ---
if (isset($_POST['update_costs'])) {
    $c_name = (int)$_POST['cost_name'];
    $c_sex = (int)$_POST['cost_sex'];
    $c_skin = (int)$_POST['cost_skin'];
    
    $db->query("UPDATE system_settings SET setting_value = $c_name WHERE setting_key = 'cost_name'");
    $db->query("UPDATE system_settings SET setting_value = $c_sex WHERE setting_key = 'cost_sex'");
    $db->query("UPDATE system_settings SET setting_value = $c_skin WHERE setting_key = 'cost_skin'");
    header("Location: admin.php?msg=costs_updated"); exit;
}

// --- HANDLE RESTRICTED NAMES & THE DEATH CLOCK ---
if (isset($_POST['add_restricted'])) {
    $bad_name = $db->real_escape_string($_POST['bad_name']);
    $db->query("INSERT INTO restricted_names (bad_name) VALUES ('$bad_name')");
    
    // 1. Find all players breaking the new rule
    $sql_find = "SELECT id, account_id, name FROM players WHERE name LIKE '%$bad_name%' AND must_rename_by IS NULL";
    $affected = $db->query($sql_find);
    
    // Safety Check: Only loop if the query was successful
    if ($affected) {
        while ($p = $affected->fetch_assoc()) {
            $acc = $p['account_id'];
            $char_name = $p['name']; // Keep raw for message, escape later
            $char_id = $p['id'];

            // 2. Start the 30-Day Death Clock
            $db->query("UPDATE players SET must_rename_by = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE id = '$char_id'");

            // 3. Send the System Alert to their Inbox
            $title = "SYSTEM ALERT: MANIFEST RESTRICTION (" . $char_name . ")";
            $message = "Your manifest named '" . $char_name . "' contains restricted nomenclature ('" . $bad_name . "'). The System has activated a 30-day Death Clock. You must rewrite this identity via the Identity Bureau, or the manifest will be purged from the Void permanently. <br><br><a href='customization.php?char_id=" . $char_id . "' style='color:#00f3ff; text-decoration:underline; font-weight:bold;'>[ CLICK HERE TO REWRITE IDENTITY ]</a>";

            // ESCAPE THE STRINGS FOR SQL SAFETY (The Fix!)
            $title_safe = $db->real_escape_string($title);
            $message_safe = $db->real_escape_string($message);

            // USE THE SAFE VARIABLES
            if (!$db->query("INSERT INTO account_messages (account_id, title, message) VALUES ('$acc', '$title_safe', '$message_safe')")) {
                die("<h1 style='color:red;'>DATABASE ERROR: " . $db->error . "</h1>");
            }
        }
    }
    header("Location: admin.php?msg=restricted_added"); exit;
}

if (isset($_GET['del_restricted'])) {
    $del_id = (int)$_GET['del_restricted'];
    $db->query("DELETE FROM restricted_names WHERE id = $del_id");
    header("Location: admin.php?msg=restricted_removed"); exit;
}

// --- HANDLE NEWS BROADCASTS ---
if (isset($_GET['delete_news'])) {
    $del_id = (int)$_GET['delete_news'];
    $db->query("DELETE FROM news WHERE id = $del_id");
    header("Location: admin.php?msg=news_deleted"); exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['broadcast'])) {
    $title = $db->real_escape_string($_POST['title']);
    $content = $db->real_escape_string($_POST['content']); 
    $img = $db->real_escape_string($_POST['image_url']);
    $db->query("INSERT INTO news (author_id, title, content, image_url) VALUES ('$acc_id', '$title', '$content', '$img')");
    header("Location: admin.php?msg=news_posted"); exit;
}

// Fetch Data for Display
$all_news = $db->query("SELECT * FROM news ORDER BY created_at DESC");
$restricted_list = $db->query("SELECT * FROM restricted_names");

$costs_res = $db->query("SELECT * FROM system_settings");
$settings = [];
if ($costs_res) {
    while($row = $costs_res->fetch_assoc()) { 
        $settings[$row['setting_key']] = $row['setting_value']; 
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Worlds Void - Master Terminal</title>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Share+Tech+Mono&display=swap');
        body { background: #050505; color: #00f3ff; font-family: 'Share Tech Mono', monospace; padding: 20px; margin: 0; }
        .grid-container { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; max-width: 1400px; margin: 0 auto; }
        
        .panel { border: 2px solid #ff0055; background: rgba(10,5,25,0.9); padding: 25px; box-shadow: 0 0 15px rgba(255,0,85,0.3); }
        .panel-blue { border-color: #00f3ff; box-shadow: 0 0 15px rgba(0,243,255,0.3); }
        .panel-yellow { border-color: #ffff00; box-shadow: 0 0 15px rgba(255,255,0,0.3); }
        
        h1, h2 { font-family: 'Orbitron'; text-transform: uppercase; margin-top: 0; }
        h1 { color: #fff; text-shadow: 0 0 10px #ff0055; border-bottom: 2px solid #ff0055; padding-bottom: 10px; }
        
        input[type="text"], input[type="number"] { width: 100%; padding: 10px; margin-bottom: 15px; background: #111; color: #00f3ff; border: 1px solid #333; box-sizing: border-box; font-family: 'Share Tech Mono'; }
        input:focus { border-color: #ff0055; outline: none; box-shadow: 0 0 10px rgba(255,0,85,0.3); }
        
        #editor-container { height: 250px; background: #fff; color: #000; margin-bottom: 20px; }
        
        .btn { padding: 15px; border: none; cursor: pointer; font-family: 'Orbitron'; font-weight: bold; text-transform: uppercase; transition: 0.3s; width: 100%; margin-top:10px; }
        .btn-post { background: #ff0055; color: #fff; } .btn-post:hover { box-shadow: 0 0 20px #ff0055; }
        .btn-blue { background: #00f3ff; color: #000; } .btn-blue:hover { box-shadow: 0 0 20px #00f3ff; color: #fff; }
        .btn-yellow { background: #ffff00; color: #000; } .btn-yellow:hover { box-shadow: 0 0 20px #ffff00; }
        
        .list-item { border: 1px solid #333; padding: 10px; margin-bottom: 5px; display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.5); }
        .del-btn { color: #ff0055; text-decoration: none; border: 1px solid #ff0055; padding: 5px 10px; font-size: 0.8em; }
        .del-btn:hover { background: #ff0055; color: #fff; }
        
        .msg { background: rgba(0,255,0,0.1); color: #00ff00; border: 1px solid #00ff00; padding: 10px; text-align: center; margin-bottom: 20px; font-family: 'Orbitron'; }
    </style>
</head>
<body>

    <div style="max-width: 1400px; margin: 0 auto 20px auto;">
        <a href="index.php" style="color:#00f3ff; text-decoration:none; font-size:1.2em; font-weight:bold;">[ DISCONNECT & RETURN TO MANIFEST ]</a>
        <?php if(isset($_GET['msg'])) echo "<div class='msg'>SYSTEM OVERRIDE SUCCESSFUL</div>"; ?>
    </div>

    <div class="grid-container">
        <div class="panel">
            <h1>News Broadcaster</h1>
            <form id="newsForm" method="POST">
                <input type="text" name="title" placeholder="TRANSMISSION TITLE" required>
                <input type="text" name="image_url" placeholder="IMAGE URL (Optional)">
                <div id="editor-container"></div>
                <input type="hidden" name="content" id="content">
                <button type="submit" name="broadcast" class="btn btn-post">Broadcast to Grid</button>
            </form>

            <h2 style="color:#ff0055; margin-top:30px;">Active Brackets</h2>
            <?php if($all_news) { while($n = $all_news->fetch_assoc()): ?>
                <div class="list-item">
                    <span><strong style="color:#ff0055;">[ID: <?php echo $n['id']; ?>]</strong> <?php echo htmlspecialchars($n['title']); ?></span>
                    <a href="admin.php?delete_news=<?php echo $n['id']; ?>" class="del-btn" onclick="return confirm('Erase transmission?')">DELETE</a>
                </div>
            <?php endwhile; } ?>
        </div>

        <div>
            <div class="panel panel-yellow" style="margin-bottom: 20px;">
                <h2 style="color:#ffff00;">Identity Bureau Pricing</h2>
                <form method="POST">
                    <label style="color:#ffff00;">Name Change (Time Tokens)</label>
                    <input type="number" name="cost_name" value="<?php echo $settings['cost_name'] ?? 50; ?>" required>
                    
                    <label style="color:#ffff00;">Sex Change (Time Tokens)</label>
                    <input type="number" name="cost_sex" value="<?php echo $settings['cost_sex'] ?? 25; ?>" required>
                    
                    <label style="color:#ffff00;">Skin Change (Time Tokens)</label>
                    <input type="number" name="cost_skin" value="<?php echo $settings['cost_skin'] ?? 10; ?>" required>
                    
                    <button type="submit" name="update_costs" class="btn btn-yellow">Update Economy</button>
                </form>
            </div>

            <div class="panel panel-blue">
                <h2 style="color:#00f3ff;">Restricted Nomenclature</h2>
                <p style="font-size:0.8em; color:#aaa;">Adding a restricted string triggers a 30-day "Death Clock" on any character containing it. They must pay TT to change their name or lose the character.</p>
                <form method="POST">
                    <input type="text" name="bad_name" placeholder="Enter illegal string (e.g., 'admin')" required>
                    <button type="submit" name="add_restricted" class="btn btn-blue">Enforce Restriction</button>
                </form>

                <h3 style="color:#00f3ff; margin-top:20px;">Current Blacklist</h3>
                <?php if($restricted_list) { while($r = $restricted_list->fetch_assoc()): ?>
                    <div class="list-item">
                        <span style="color:#fff;"><?php echo htmlspecialchars($r['bad_name']); ?></span>
                        <a href="admin.php?del_restricted=<?php echo $r['id']; ?>" class="del-btn" style="border-color:#00f3ff; color:#00f3ff;">REMOVE</a>
                    </div>
                <?php endwhile; } ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        var quill = new Quill('#editor-container', { theme: 'snow' });
        var form = document.getElementById('newsForm');
        form.onsubmit = function() { document.querySelector('input[id=content]').value = quill.root.innerHTML; };
    </script>
</body>
</html>