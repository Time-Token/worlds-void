<?php
session_start();
// Security checks
if (!isset($_SESSION['account_id']) || !isset($_POST['char_id'])) { exit; }

$db = new mysqli("localhost", "timetoken", "Tuf@18240833", "worlds_void");

$acc_id = $_SESSION['account_id'];
$char_id = (int)$_POST['char_id'];

// --- THE ONGOING SESSION CHECK ---
$current_session = session_id();
$check = $db->query("SELECT session_token FROM accounts WHERE id = '$acc_id'")->fetch_assoc();

if ($check['session_token'] !== $current_session) {
    // If the tokens don't match, they logged in somewhere else! Tell the game to kick them.
    echo json_encode(['status' => 'kicked']);
    exit;
}
// ---------------------------------

$x = (float)$_POST['x'];
$y = (float)$_POST['y'];
$map = $db->real_escape_string($_POST['map']);

// Update YOUR current position and refresh your "online" timer
$db->query("UPDATE players SET x = '$x', y = '$y', map_name = '$map', last_active = NOW() WHERE id = '$char_id'");

// Look for OTHER players on the same map
$other_players = [];
$res = $db->query("SELECT name, x, y FROM players WHERE map_name = '$map' AND id != '$char_id' AND last_active >= NOW() - INTERVAL 5 SECOND");

while($row = $res->fetch_assoc()) {
    $other_players[] = [
        'name' => htmlspecialchars($row['name']),
        'x' => (float)$row['x'],
        'y' => (float)$row['y']
    ];
}

header('Content-Type: application/json');
echo json_encode($other_players);
$db->close();
?>