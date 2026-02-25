<?php
session_start();

// Read credentials from your locked JSON file
$json_data = json_decode(file_get_contents('google_secret.json'), true);

if (!$json_data) {
    die("CRITICAL ERROR: System cannot read google_secret.json. Run 'sudo chown daemon:daemon google_secret.json' in terminal.");
}

$client_id = $json_data['web']['client_id'];

// FORCE HTTPS: The custom domain we set up in Apache and Cloudflare
$redirect_uri = "https://www.worldsvoid.com/callback_google.php";

$url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'response_type' => 'code',
    'scope' => 'email profile',
    'access_type' => 'online',
    'prompt' => 'select_account' // This forces the "Choose Account" screen to show up
]);

header("Location: $url");
exit;
?>