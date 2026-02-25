<?php
session_start();

// We explicitly hardcode your secure Cloudflare domain for the return trip
$realm = "https://www.worldsvoid.com";
$return_to = "https://www.worldsvoid.com/callback_steam.php";

$login_url_params = [
    'openid.ns'         => 'http://specs.openid.net/auth/2.0',
    'openid.mode'       => 'checkid_setup',
    'openid.return_to'  => $return_to,
    'openid.realm'      => $realm,
    'openid.identity'   => 'http://specs.openid.net/auth/2.0/identifier_select',
    'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select',
];

$steam_login_url = 'https://steamcommunity.com/openid/login?' . http_build_query($login_url_params);

header("Location: $steam_login_url");
exit;
?>