<?php
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'passenger') {
    header("Location: index.php");
    exit();
}

$me = $_SESSION['user_id'];

// Handle sending message
if (isset($_POST['send'])) {
    $other = intval($_POST['receiver_id']);
    $msg = $conn->real_escape_string($_POST['txt']);
    $conn->query("INSERT INTO messages (sender_id, receiver_id, msg) VALUES ($me, $other, '$msg')");
}

// Get selected conversation
$selected = isset($_GET['to']) ? intval($_GET['to']) : 0;

// Fetch user data for nav
$user = $conn->query("SELECT * FROM users WHERE id = $me")->fetch_assoc();

// Get all companies this passenger has messaged or booked with
$conversations = $conn->query("
    SELECT DISTINCT u.id, u.name, u.email 
    FROM users u
    WHERE u.role = 'company' AND (
        u.id IN (SELECT DISTINCT receiver_id FROM messages WHERE sender_id = $me)
        OR u.id IN (SELECT DISTINCT sender_id FROM messages WHERE receiver_id = $me)
        OR u.id IN (SELECT DISTINCT f.company_id FROM bookings b JOIN flights f ON b.flight_id = f.id WHERE b.passenger_id = $me)
    )
");

// Get chat messages if conversation selected
$chat = null;
$other_user = null;
if ($selected > 0) {
    $chat = $conn->query("SELECT m.*, u.name as sender_name FROM messages m JOIN users u ON m.sender_id = u.id WHERE (sender_id=$me AND receiver_id=$selected) OR (sender_id=$selected AND receiver_id=$me) ORDER BY created_at ASC");
    $other_user = $conn->query("SELECT name, email FROM users WHERE id = $selected")->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Messages</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/main.js"></script>
</head>
<body>
    <nav>
        <div class="logo">
            <?php if($user['logo_path']): ?>
                <img src="<?php echo $user['logo_path']; ?>" alt="Profile" class="logo-icon">
            <?php else: ?>
                <div class="logo-icon" style="background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple)); display:flex; align-items:center; justify-content:center; font-size: 1rem; color: #fff;">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
            <?php endif; ?>
            SkyBooker
        </div>
        <div>
            <a href="passenger_home.php">Dashboard</a>
            <a href="search_flight.php">Flights</a>
            <a href="passenger_messages.php" class="active">Messages</a>
            <a href="passenger_profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h2 style="margin-bottom: 1.5rem;">💬 Messages</h2>
        <div class="messages-container">
            <div class="conversations-list">
                <h3>Companies</h3>
                <?php if($conversations->num_rows > 0): ?>
                    <?php while($c = $conversations->fetch_assoc()): ?>
                    <div class="conv-item <?php echo ($selected == $c['id']) ? 'active' : ''; ?>" onclick="window.location='passenger_messages.php?to=<?php echo $c['id']; ?>'">
                        <strong><?php echo htmlspecialchars($c['name']); ?></strong>
                        <small><?php echo htmlspecialchars($c['email']); ?></small>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">No conversations yet. Book a flight to start chatting.</p>
                <?php endif; ?>
            </div>

            <div class="chat-area">
                <?php if($selected > 0 && $other_user): ?>
                    <div class="chat-header">
                        <h3><?php echo htmlspecialchars($other_user['name']); ?></h3>
                        <small><?php echo htmlspecialchars($other_user['email']); ?></small>
                    </div>
                    <div class="chat-messages">
                        <?php while($m = $chat->fetch_assoc()): ?>
                            <div class="msg <?php echo ($m['sender_id'] == $me) ? 'sent' : 'received'; ?>">
                                <?php echo htmlspecialchars($m['msg']); ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <form method="POST" class="chat-input">
                        <input type="hidden" name="receiver_id" value="<?php echo $selected; ?>">
                        <input type="text" name="txt" placeholder="Type a message..." required autocomplete="off">
                        <button type="submit" name="send" class="btn btn-primary">Send</button>
                    </form>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--text-muted);">
                        <span style="font-size: 4rem; margin-bottom: 1rem;">💬</span>
                        <h3 style="color: var(--text-secondary);">Select a conversation</h3>
                        <p>Choose a company from the list to view messages.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>