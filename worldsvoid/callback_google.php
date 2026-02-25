<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$db = new mysqli("localhost", "timetoken", "Tuf@18240833", "worlds_void");

// 1. Handle user cancellation
if (isset($_GET['error'])) { 
    header("Location: index.php"); 
    exit; 
}

// 2. Load Credentials
$json_data = json_decode(file_get_contents('google_secret.json'), true);
if (!$json_data) die("CRITICAL ERROR: System cannot read google_secret.json.");

$client_id     = $json_data['web']['client_id']; 
$client_secret = $json_data['web']['client_secret'];
$redirect_uri = "https://www.worldsvoid.com/callback_google.php";

if (isset($_GET["code"])) {
    // 3. Token Exchange (SSL Bypass for local Linux)
    $ch = curl_init("https://oauth2.googleapis.com/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        "code" => $_GET["code"], 
        "client_id" => $client_id, 
        "client_secret" => $client_secret,
        "redirect_uri" => $redirect_uri, 
        "grant_type" => "authorization_code"
    ]));
    
    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($res["access_token"])) {
        // 4. Fetch User Data
        $ch_u = curl_init("https://www.googleapis.com/oauth2/v1/userinfo");
        curl_setopt($ch_u, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_u, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch_u, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $res["access_token"]]);
        $u = json_decode(curl_exec($ch_u), true);

        $gid = $db->real_escape_string($u["id"]);
        $name = $db->real_escape_string($u["name"]);
        $email = $db->real_escape_string($u["email"] ?? "");

        // --- AIRTIGHT 4-STATE LOGIC ---
        $check = $db->query("SELECT id FROM accounts WHERE google_id = '$gid'");

        if ($check->num_rows > 0) {
            $existing_id = $check->fetch_assoc()['id'];
            if (isset($_SESSION['account_id'])) {
                if ($_SESSION['account_id'] == $existing_id) {
                    header("Location: settings.php"); exit;
                } else {
                    // STATE 3: NEON REJECTED POP-UP
                    showRejected("Google");
                }
            } else {
                $_SESSION['account_id'] = $existing_id;
                header("Location: portal.php"); exit;
            }
        } else {
            if (isset($_SESSION['account_id'])) {
                $db->query("UPDATE accounts SET google_id = '$gid', email = '$email' WHERE id = '".$_SESSION['account_id']."'");
                header("Location: settings.php"); exit;
            } else {
                $db->query("INSERT INTO accounts (name, google_id, email, is_admin) VALUES ('$name', '$gid', '$email', 0)");
                $_SESSION['account_id'] = $db->insert_id;
                header("Location: portal.php"); exit;
            }
        }
    }
}
header("Location: index.php");

// Neon Alert Function
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