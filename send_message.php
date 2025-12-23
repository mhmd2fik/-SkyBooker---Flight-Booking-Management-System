<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

$me = intval($_SESSION['user_id']);
$other = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;
$msg = isset($_POST['txt']) ? $conn->real_escape_string($_POST['txt']) : '';

if ($other == 0 || empty($msg)) {
    echo json_encode(['error' => 'Invalid data']);
    exit();
}

$query = "INSERT INTO messages (sender_id, receiver_id, msg) VALUES ($me, $other, '$msg')";
if ($conn->query($query)) {
    $new_id = $conn->insert_id;
    echo json_encode([
        'success' => true, 
        'message_id' => $new_id,
        'msg' => $msg
    ]);
} else {
    echo json_encode(['error' => 'Failed to send message']);
}
