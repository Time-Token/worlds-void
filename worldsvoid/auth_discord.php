<?php
session_start();

$json_data = json_decode(file_get_contents('discord_secret.json'), true);
if (!$json_data) {
    die("Error: Could not read discord_secret.json. Check file permissions.");
}

$client_id = $json_data['web']['client_id'];
$redirect_uri = $json_data['web']['redirect_uris'][0];

// Build the Discord Login URL
$url = "https://discord.com/api/oauth2/authorize?" . http_build_query([
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'response_type' => 'code',
    'scope' => 'identify'
]);

header("Location: $url");
exit;
?>