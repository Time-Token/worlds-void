<?php
session_start();
$db = new mysqli("localhost", "timetoken", "Tuf@18240833", "worlds_void");

// 1. Security & Identity Verification
if (!isset($_SESSION['account_id'])) { header("Location: index.php"); exit; }
$acc_id = $_SESSION['account_id'];

if (!isset($_GET['char_id'])) { header("Location: portal.php"); exit; }
$char_id = (int)$_GET['char_id'];

// Ensure the character actually belongs to the logged-in user
$p = $db->query("SELECT * FROM players WHERE id = '$char_id' AND account_id = '$acc_id'")->fetch_assoc();
if (!$p) { die("CRITICAL ERROR: UNAUTHORIZED MANIFEST ACCESS."); }

// Fetch the wallet balance
$acc = $db->query("SELECT time_tokens FROM accounts WHERE id = '$acc_id'")->fetch_assoc();

// --- FETCH DYNAMIC COSTS FROM DATABASE ---
$costs_res = $db->query("SELECT * FROM system_settings");
$system_costs = [];
while($row = $costs_res->fetch_assoc()) {
    $system_costs[$row['setting_key']] = $row['setting_value'];
}
$cost_name = $system_costs['cost_name'] ?? 50;
$cost_sex = $system_costs['cost_sex'] ?? 25;
$cost_skin = $system_costs['cost_skin'] ?? 10;

$error_msg = "";

// 2. Process Transactions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['type'])) {
    $type = $_POST['type'];
    
    // Determine which cost applies
    $cost = 9999;
    if ($type == 'name') $cost = $cost_name;
    if ($type == 'sex') $cost = $cost_sex;
    if ($type == 'skin') $cost = $cost_skin;

    if ($acc['time_tokens'] >= $cost) {
        $transaction_success = false;

        if ($type == 'name' && !empty($_POST['new_name'])) {
            $new_name = $db->real_escape_string(trim($_POST['new_name']));
            
            // Check if the NEW name they are trying to buy is ALSO restricted
            $restricted = $db->query("SELECT bad_name FROM restricted_names");
            $is_bad = false;
            while($r = $restricted->fetch_assoc()) {
                if (stripos($new_name, $r['bad_name']) !== false) {
                    $is_bad = true; break;
                }
            }
            
            if ($is_bad) {
                $error_msg = "TRANSACTION FAILED: The new name contains restricted nomenclature.";
            } else {
                // Change the name AND clear the Death Clock!
                $db->query("UPDATE players SET name = '$new_name', must_rename_by = NULL WHERE id = '$char_id'");
                $transaction_success = true;
            }

        } elseif ($type == 'sex' && isset($_POST['new_sex'])) {
            $new_sex = (int)$_POST['new_sex'];
            $db->query("UPDATE players SET sex = '$new_sex' WHERE id = '$char_id'");
            $transaction_success = true;

        } elseif ($type == 'skin' && isset($_POST['new_skin'])) {
            $new_skin = (int)$_POST['new_skin'];
            $db->query("UPDATE players SET skin_tone = '$new_skin' WHERE id = '$char_id'");
            $transaction_success = true;
        }
        
        // Deduct Tokens & Redirect ONLY if it was successful (not blocked by bad name)
        if ($transaction_success) {
            $db->query("UPDATE accounts SET time_tokens = time_tokens - $cost WHERE id = '$acc_id'");
            header("Location: portal.php?success=1"); 
            exit;
        }

    } else {
        $error_msg = "TRANSACTION FAILED: INSUFFICIENT TIME TOKENS.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Worlds Void - Identity Bureau</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Share+Tech+Mono&display=swap');
        
        body { background: #050505; color: #00f3ff; font-family: 'Share Tech Mono', monospace; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; padding: 20px; }
        .box { border: 2px solid #ff007f; padding: 40px; background: rgba(10,5,20,0.9); box-shadow: 0 0 30px rgba(255,0,127,0.2); max-width: 500px; width: 100%; }
        h2 { color: #fff; font-family: 'Orbitron', sans-serif; text-align: center; margin-top: 0; text-transform: uppercase; text-shadow: 0 0 10px #ff007f; border-bottom: 2px solid #ff007f; padding-bottom: 15px; }
        
        .balance { text-align: center; color: #ffff00; font-size: 1.2em; border-bottom: 1px dashed #333; padding-bottom: 20px; margin-bottom: 20px; font-family: 'Orbitron', sans-serif;}
        
        .shop-item { background: rgba(0, 243, 255, 0.05); border: 1px solid #00f3ff; padding: 20px; margin-bottom: 20px; }
        .shop-item strong { display: block; color: #fff; margin-bottom: 10px; font-family: 'Orbitron', sans-serif; }
        
        input, select { background: #000; color: #00f3ff; border: 1px solid #00f3ff; padding: 12px; width: 100%; box-sizing: border-box; font-family: 'Share Tech Mono', monospace; font-size: 1em; margin-bottom: 15px; }
        input:focus, select:focus { outline: none; border-color: #ff007f; box-shadow: 0 0 10px rgba(255,0,127,0.3); }
        
        button { background: rgba(255,0,127,0.1); color: #ff007f; border: 2px solid #ff007f; padding: 12px; width: 100%; cursor: pointer; font-family: 'Orbitron', sans-serif; font-weight: bold; text-transform: uppercase; transition: 0.3s; }
        button:hover { background: #ff007f; color: #fff; box-shadow: 0 0 15px #ff007f; }
        
        .error { background: rgba(255,0,0,0.1); border: 1px solid #ff0000; color: #ff0000; padding: 15px; text-align: center; margin-bottom: 20px; font-family: 'Orbitron', sans-serif; }
        .back-link { display: block; text-align: center; color: #666; text-decoration: none; margin-top: 20px; transition: 0.3s; font-size: 1.1em;}
        .back-link:hover { color: #00f3ff; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Modifying: <?php echo htmlspecialchars($p['name']); ?></h2>
        <div class="balance">AVAILABLE FUNDS: <?php echo number_format($acc['time_tokens']); ?> TT</div>
        
        <?php if ($error_msg): ?>
            <div class="error"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="shop-item">
                <strong>REWRITE NAME (<?php echo $cost_name; ?> TT)</strong>
                <input type="text" name="new_name" placeholder="Enter new entity name..." required>
                <button type="submit" name="type" value="name">Purchase Name Change</button>
            </div>
        </form>

        <form method="POST">
            <div class="shop-item">
                <strong>REASSIGN GENETICS (<?php echo $cost_sex; ?> TT)</strong>
                <select name="new_sex">
                    <option value="1" <?php if($p['sex']==1) echo 'selected'; ?>>MALE</option>
                    <option value="2" <?php if($p['sex']==2) echo 'selected'; ?>>FEMALE</option>
                </select>
                <button type="submit" name="type" value="sex">Purchase Sex Change</button>
            </div>
        </form>

        <form method="POST">
            <div class="shop-item">
                <strong>OVERRIDE DERMIS (<?php echo $cost_skin; ?> TT)</strong>
                <select name="new_skin">
                    <option value="1" <?php if($p['skin_tone']==1) echo 'selected'; ?>>TONE 1 (Light)</option>
                    <option value="2" <?php if($p['skin_tone']==2) echo 'selected'; ?>>TONE 2 (Medium)</option>
                    <option value="3" <?php if($p['skin_tone']==3) echo 'selected'; ?>>TONE 3 (Dark)</option>
                    <option value="4" <?php if($p['skin_tone']==4) echo 'selected'; ?>>TONE 4 (Alien/Custom)</option>
                </select>
                <button type="submit" name="type" value="skin">Purchase Skin Change</button>
            </div>
        </form>

        <a href="portal.php" class="back-link">[ ABORT & RETURN TO PORTAL ]</a>
    </div>
</body>
</html>