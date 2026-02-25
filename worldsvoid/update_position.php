<?php
session_start();
$db = new mysqli("localhost", "timetoken", "Tuf@18240833", "worlds_void");

if (isset($_POST['id']) && isset($_SESSION['account_id'])) {
    $char_id = $db->real_escape_string($_POST['id']);
    $acc_id = $_SESSION['account_id'];
    $x = (int)$_POST['x'];
    $y = (int)$_POST['y'];

    $db->query("UPDATE players SET x = '$x', y = '$y' WHERE id = '$char_id' AND account_id = '$acc_id'");
}
?>