<?php
session_start();
$db = new mysqli("localhost", "timetoken", "Tuf@18240833", "worlds_void");

// 1. Verification
if (!isset($_SESSION['account_id'])) { header("Location: index.php"); exit; }
$acc_id = $_SESSION['account_id'];

$error_msg = "";

// 2. Process Creation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $db->real_escape_string(trim($_POST['char_name']));
    $sex = (int)$_POST['sex'];
    $skin = (int)$_POST['skin'];

    if (strlen($name) < 3 || strlen($name) > 15) {
        $error_msg = "ERROR: Name must be between 3 and 15 characters.";
    } else {
        // Check if name is already taken globally
        $check_name = $db->query("SELECT id FROM players WHERE name = '$name'");
        if ($check_name->num_rows > 0) {
            $error_msg = "REJECTED: Entity name is already registered in the Void.";
        } else {
            // ENFORCE ADMIN RESTRICTIONS
            $restricted = $db->query("SELECT bad_name FROM restricted_names");
            $is_bad = false;
            while($r = $restricted->fetch_assoc()) {
                if (stripos($name, $r['bad_name']) !== false) {
                    $is_bad = true; 
                    break;
                }
            }

            if ($is_bad) {
                $error_msg = "REJECTED: Name contains restricted system nomenclature.";
            } else {
                // Spawn the character! (Default level 1, 0 EXP, centered at X:400, Y:300)
                $db->query("INSERT INTO players (account_id, name, sex, skin_tone, level, experience, x, y) 
                            VALUES ('$acc_id', '$name', '$sex', '$skin', 1, 0, 400, 300)");
                
                header("Location: portal.php?success=created"); 
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Worlds Void - Initialization</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Share+Tech+Mono&display=swap');
        
        body { background: #050505; color: #00f3ff; font-family: 'Share Tech Mono', monospace; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; padding: 20px; }
        .box { border: 2px solid #00ff00; padding: 40px; background: rgba(0,255,0,0.05); box-shadow: 0 0 30px rgba(0,255,0,0.2); max-width: 500px; width: 100%; }
        h2 { color: #fff; font-family: 'Orbitron', sans-serif; text-align: center; margin-top: 0; text-transform: uppercase; text-shadow: 0 0 10px #00ff00; border-bottom: 2px solid #00ff00; padding-bottom: 15px; }
        
        .shop-item { background: rgba(0, 0, 0, 0.5); border: 1px solid #00ff00; padding: 20px; margin-bottom: 20px; }
        .shop-item strong { display: block; color: #fff; margin-bottom: 10px; font-family: 'Orbitron', sans-serif; }
        
        input, select { background: #000; color: #00ff00; border: 1px solid #00ff00; padding: 12px; width: 100%; box-sizing: border-box; font-family: 'Share Tech Mono', monospace; font-size: 1em; margin-bottom: 15px; }
        input:focus, select:focus { outline: none; border-color: #fff; box-shadow: 0 0 10px rgba(0,255,0,0.5); }
        
        button { background: rgba(0,255,0,0.1); color: #00ff00; border: 2px solid #00ff00; padding: 15px; width: 100%; cursor: pointer; font-family: 'Orbitron', sans-serif; font-weight: bold; text-transform: uppercase; transition: 0.3s; font-size: 1.1em;}
        button:hover { background: #00ff00; color: #000; box-shadow: 0 0 20px #00ff00; }
        
        .error { background: rgba(255,0,0,0.1); border: 1px solid #ff0000; color: #ff0000; padding: 15px; text-align: center; margin-bottom: 20px; font-family: 'Orbitron', sans-serif; }
        .back-link { display: block; text-align: center; color: #666; text-decoration: none; margin-top: 20px; transition: 0.3s; font-size: 1.1em;}
        .back-link:hover { color: #00f3ff; }
    </style>
</head>
<body>
    <div class="box">
        <h2>System Initialization</h2>
        
        <?php if ($error_msg): ?>
            <div class="error"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="shop-item">
                <strong>ENTITY NOMENCLATURE</strong>
                <input type="text" name="char_name" placeholder="Enter Character Name..." required autocomplete="off">
            </div>

            <div class="shop-item">
                <strong>GENETICS (SEX)</strong>
                <select name="sex">
                    <option value="1">MALE</option>
                    <option value="2">FEMALE</option>
                </select>
            </div>

            <div class="shop-item">
                <strong>DERMIS (SKIN TONE)</strong>
                <select name="skin">
                    <option value="1">TONE 1 (Light)</option>
                    <option value="2">TONE 2 (Medium)</option>
                    <option value="3">TONE 3 (Dark)</option>
                    <option value="4">TONE 4 (Alien/Custom)</option>
                </select>
            </div>

            <button type="submit">Execute Creation Sequence</button>
        </form>

        <a href="portal.php" class="back-link">[ ABORT INITIALIZATION ]</a>
    </div>
</body>
</html>