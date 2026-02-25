<?php
session_start();
$db = new mysqli("localhost", "timetoken", "Tuf@18240833", "worlds_void");

if (isset($_SESSION['account_id']) && isset($_GET['id'])) {
    $char_id = (int)$_GET['id'];
    $acc_id = $_SESSION['account_id'];
    $x = (int)$_GET['x'];
    $y = (int)$_GET['y'];
    
    // Grab the map name, default to world_1
    $map = isset($_GET['map']) ? $db->real_escape_string($_GET['map']) : 'world_1';

    $db->query("UPDATE players SET x = '$x', y = '$y', map_name = '$map' WHERE id = '$char_id' AND account_id = '$acc_id'");
}
?>