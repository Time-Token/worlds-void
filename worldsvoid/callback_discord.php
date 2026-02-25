<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$db = new mysqli("localhost", "timetoken", "Tuf@18240833", "worlds_void");

// 1. Handle user cancellation or API error
if (isset($_GET['error'])) { 
    header("Location: index.php"); 
    exit; 
}

// 2. Load Credentials cleanly from JSON
$json_data = json_decode(file_get_contents('discord_secret.json'), true);
if (!$json_data) die("CRITICAL ERROR: System cannot read discord_secret.json.");

$client_id     = $json_data['web']['client_id'];
$client_secret = $json_data['web']['client_secret'];
$redirect_uri  = "https://www.worldsvoid.com/callback_discord.php";

if (isset($_GET['code'])) {
    // 3. Token Exchange with Discord API
    $ch = curl_init("https://discord.com/api/oauth2/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id' => $client_id, 
        'client_secret' => $client_secret,
        'grant_type' => 'authorization_code', 
        'code' => $_GET['code'], 
        'redirect_uri' => $redirect_uri
    ]));
    $res = json_decode(curl_exec($ch), true);

    if (isset($res['access_token'])) {
        // 4. Fetch User Profile Data
        $ch_u = curl_init("https://discord.com/api/users/@me");
        curl_setopt($ch_u, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_u, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $res['access_token']]);
        $u = json_decode(curl_exec($ch_u), true);

        if (!isset($u['id'])) die("Failed to retrieve Discord Identity.");

        $did = $db->real_escape_string($u['id']);
        $name = $db->real_escape_string($u['username']);

        // --- THE AIRTIGHT 4-STATE LOGIC (RESTORED) ---
        $check = $db->query("SELECT id FROM accounts WHERE discord_id = '$did'");

        if ($check->num_rows > 0) {
            // STATE 1 & 3: Identity exists in Database
            $existing_id = $check->fetch_assoc()['id'];

            if (isset($_SESSION['account_id'])) {
                if ($_SESSION['account_id'] == $existing_id) {
                    // Already linked to the current logged-in user
                    header("Location: settings.php"); exit;
                } else {
                    // STATE 3: Identity belongs to someone else
                    showRejected("Discord");
                }
            } else {
                // STATE 1: STANDARD LOGIN (This was likely what was failing)
                $_SESSION['account_id'] = $existing_id;
                header("Location: portal.php"); exit;
            }
        } else {
            // STATE 2 & 4: Identity NOT in database yet
            if (isset($_SESSION['account_id'])) {
                // STATE 2: Link this new Discord ID to the current active session
                $acc_id = $_SESSION['account_id'];
                $db->query("UPDATE accounts SET discord_id = '$did' WHERE id = '$acc_id'");
                header("Location: settings.php"); exit;
            } else {
                // STATE 4: Brand New User registration
                $db->query("INSERT INTO accounts (name, discord_id, is_admin) VALUES ('$name', '$did', 0)");
                $_SESSION['account_id'] = $db->insert_id;
                header("Location: portal.php"); exit;
            }
        }
    }
}
header("Location: index.php");

// Neon Alert Function for consistency
function showRejected($provider) {
    die("
    <body style='background:#050505; color:#ff0055; font-family:monospace; text-align:center; padding-top:100px;'>
        <div style='border:2px solid #ff0055; padding:40px; display:inline-block; background:rgba(255,0,85,0.1); box-shadow: 0 0 20px #ff0055; border-radius:10px;'>
            <h2 style='text-transform:uppercase; letter-spacing:2px;'>Uplink Rejected</h2>
            <p style='color:#fff; font-size:1.1em;'>This $provider identity is already bound to another manifest in the Void.</p>
            <p style='font-size:0.9em; color:#666;'>SYSTEM ALERT: Redirecting to terminal in 3 seconds...</p>
        </div>
        <script>setTimeout(() => { window.location.href='settings.php'; }, 3000);</script>
    </body>");
}
?>