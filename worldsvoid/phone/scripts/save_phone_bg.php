<?php
session_start();
// 1. Basic Security Checks
if (!isset($_SESSION['account_id']) || !isset($_POST['char_id']) || !isset($_POST['bg_image'])) {
    die("Unauthorized access.");
}

$acc_id = (int)$_SESSION['account_id'];
$char_id = (int)$_POST['char_id'];
// Sanitize image name strictly to prevent path traversal attacks
$bg_image = preg_replace("/[^a-zA-Z0-9._-]/", "", $_POST['bg_image']);

// 2. Connect to DB
$db = new mysqli("localhost", "timetoken", "Tuf@18240833", "worlds_void");
if ($db->connect_error) die("DB Connection failed");

// 3. Update the player record
// We ensure the account ID matches so players can't change others' settings
$stmt = $db->prepare("UPDATE players SET phone_bg = ? WHERE id = ? AND account_id = ?");
$stmt->bind_param("sii", $bg_image, $char_id, $acc_id);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error saving";
}

$stmt->close();
$db->close();
?>