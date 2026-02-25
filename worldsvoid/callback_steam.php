<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$db = new mysqli("localhost", "timetoken", "Tuf@18240833", "worlds_void");
if ($db->connect_error) die("DEBUG FATAL: Database connection failed: " . $db->connect_error);

// 1. Load Steam API Key
$json_data = json_decode(file_get_contents('steam_secret.json'), true);
if (!$json_data) die("DEBUG FATAL: System cannot read steam_secret.json.");
$api_key = $json_data['web']['api_key'];

// 2. Steam OpenID Validation
if (isset($_GET['openid_mode'])) {
    if ($_GET['openid_mode'] == 'cancel') {
        die("DEBUG: You clicked cancel on the Steam login page.");
    } elseif ($_GET['openid_mode'] == 'id_res') {
        
        // Build validation request
        $params = [];
        foreach ($_GET as $key => $value) {
            $key = str_replace('_', '.', $key); 
            $params[$key] = $value;
        }
        // FIXED: Force the check_authentication mode AFTER the loop so it doesn't get overwritten!
        $params['openid.mode'] = 'check_authentication';

        // Server-to-server verification
        $ch = curl_init('https://steamcommunity.com/openid/login');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_ENCODING, ""); 
        curl_setopt($ch, CURLOPT_USERAGENT, "WorldsVoid_Auth/1.0");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        
        $result = curl_exec($ch);
        
        if(curl_errno($ch)) die("DEBUG: cURL Error during Steam Validation: " . curl_error($ch));
        curl_close($ch);

        // 3. Check if Steam said "Yes, this is real"
        if (preg_match("/is_valid\s*:\s*true/i", $result)) {
            preg_match('#^https?://steamcommunity.com/openid/id/([0-9]{17,25})#', $_GET['openid_claimed_id'], $matches);
            if (!isset($matches[1])) die("DEBUG: Could not extract Steam64 ID from Steam's response.");
            $steam64 = $matches[1];

            // Fetch Username and Avatar from Steam API
            $ch_u = curl_init("https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=$api_key&steamids=$steam64");
            curl_setopt($ch_u, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_u, CURLOPT_SSL_VERIFYPEER, false);
            $u_json = json_decode(curl_exec($ch_u), true);
            
            if(curl_errno($ch_u)) die("DEBUG: cURL Error fetching profile: " . curl_error($ch_u));
            curl_close($ch_u);

            if (!isset($u_json['response']['players'][0])) {
                die("DEBUG: Failed to fetch Steam Profile. API Response: " . json_encode($u_json));
            }
            
            $profile = $u_json['response']['players'][0];
            $sid = $db->real_escape_string($steam64);
            $name = $db->real_escape_string($profile['personaname']);

            // --- THE AIRTIGHT 4-STATE LOGIC ---
            $check = $db->query("SELECT id FROM accounts WHERE steam_id = '$sid'");
            if (!$check) die("DEBUG: Database SQL Error: " . $db->error);

            if ($check->num_rows > 0) {
                $existing_id = $check->fetch_assoc()['id'];
                if (isset($_SESSION['account_id'])) {
                    if ($_SESSION['account_id'] == $existing_id) {
                        header("Location: settings.php"); exit;
                    } else {
                        die("DEBUG: Reached STATE 3 (Identity Steal Block).");
                    }
                } else {
                    $_SESSION['account_id'] = $existing_id;
                    header("Location: portal.php"); exit;
                }
            } else {
                if (isset($_SESSION['account_id'])) {
                    $acc_id = $_SESSION['account_id'];
                    $update = $db->query("UPDATE accounts SET steam_id = '$sid' WHERE id = '$acc_id'");
                    if (!$update) die("DEBUG: DB Update Error: " . $db->error);
                    header("Location: settings.php"); exit;
                } else {
                    $insert = $db->query("INSERT INTO accounts (name, steam_id, is_admin) VALUES ('$name', '$sid', 0)");
                    if (!$insert) die("DEBUG: DB Insert Error: " . $db->error);
                    $_SESSION['account_id'] = $db->insert_id;
                    header("Location: portal.php"); exit;
                }
            }
        } else {
            die("DEBUG: Steam rejected the validation token! Result from Steam: <br>" . htmlspecialchars($result));
        }
    } else {
        die("DEBUG: openid_mode was set, but it was not 'id_res'. It was: " . htmlspecialchars($_GET['openid_mode']));
    }
} else {
    die("DEBUG: callback_steam.php was loaded, but no OpenID data was sent by Steam.");
}
?>