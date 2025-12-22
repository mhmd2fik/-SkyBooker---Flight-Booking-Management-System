<?php
include 'db.php';
// Create a basic messages table first: 
// CREATE TABLE messages (id INT AUTO_INCREMENT PRIMARY KEY, sender_id INT, receiver_id INT, msg TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);

$me = $_SESSION['user_id'];
$other = $_GET['to'] ?? 0;

if (isset($_POST['send'])) {
    $msg = $_POST['txt'];
    $conn->query("INSERT INTO messages (sender_id, receiver_id, msg) VALUES ($me, $other, '$msg')");
}

$chat = $conn->query("SELECT * FROM messages WHERE (sender_id=$me AND receiver_id=$other) OR (sender_id=$other AND receiver_id=$me) ORDER BY created_at ASC");
?>

<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="css/style.css"></head>
<body>
<div class="container">
    <div class="card">
        <h3>Chat</h3>
        <div class="chat-box">
            <?php while($m = $chat->fetch_assoc()): ?>
                <div class="msg <?php echo ($m['sender_id'] == $me) ? 'sent' : 'received'; ?>">
                    <?php echo $m['msg']; ?>
                </div>
            <?php endwhile; ?>
        </div>
        <form method="POST" style="margin-top:10px; display:flex; gap:10px;">
            <input type="text" name="txt" placeholder="Type a message..." required>
            <button name="send" class="btn btn-primary">Send</button>
        </form>
    </div>
</div>
</body>
</html>