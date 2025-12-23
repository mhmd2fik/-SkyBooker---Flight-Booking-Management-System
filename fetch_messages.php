<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

$me = intval($_SESSION['user_id']);
$other = isset($_GET['to']) ? intval($_GET['to']) : 0;
$last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;

if ($other == 0) {
    echo json_encode(['messages' => []]);
    exit();
}

// Fetch messages after the last_id
$query = "SELECT m.*, u.name as sender_name 
          FROM messages m 
          JOIN users u ON m.sender_id = u.id 
          WHERE ((sender_id=$me AND receiver_id=$other) OR (sender_id=$other AND receiver_id=$me))
          AND m.id > $last_id
          ORDER BY created_at ASC";

$result = $conn->query($query);
$messages = [];

while($row = $result->fetch_assoc()) {
    $messages[] = [
        'id' => $row['id'],
        'sender_id' => $row['sender_id'],
        'msg' => $row['msg'],
        'sender_name' => $row['sender_name'],
        'created_at' => $row['created_at']
    ];
}

header('Content-Type: application/json');
echo json_encode(['messages' => $messages]);
