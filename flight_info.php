<?php
include 'db.php';
$f_id = $_GET['id'];
$u_id = $_SESSION['user_id'];
$flight = $conn->query("SELECT * FROM flights WHERE id = $f_id")->fetch_assoc();
$user = $conn->query("SELECT * FROM users WHERE id = $u_id")->fetch_assoc();

if (isset($_POST['book'])) {
    $pay_type = $_POST['pay_type'];
    if ($pay_type == 'account' && $user['balance'] < $flight['fees']) {
        echo "<script>alert('Insufficient Balance!');</script>";
    } else {
        if ($pay_type == 'account') {
            $conn->query("UPDATE users SET balance = balance - {$flight['fees']} WHERE id = $u_id");
            $status = 'confirmed';
        } else { $status = 'pending'; } // Cash
        
        $conn->query("INSERT INTO bookings (flight_id, passenger_id, status) VALUES ($f_id, $u_id, '$status')");
        header("Location: passenger_home.php");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Book Flight</title>
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
        <a href="passenger_messages.php">Messages</a>
        <a href="passenger_profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>
<div class="container">
    <div class="form-box" style="max-width: 600px;">
        <div style="text-align: center; margin-bottom: 24px;">
            <span style="font-size: 3rem;">🛫</span>
            <h2 style="margin-top: 12px;"><?php echo htmlspecialchars($flight['flight_name']); ?></h2>
        </div>
        
        <div style="background: rgba(255,255,255,0.03); padding: 20px; border-radius: 12px; margin-bottom: 24px;">
            <p style="margin: 8px 0;"><strong style="color: var(--accent-cyan);">Route:</strong> <?php echo htmlspecialchars($flight['itinerary']); ?></p>
            <p style="margin: 8px 0;"><strong style="color: var(--accent-cyan);">Departure:</strong> <?php echo date('M d, Y - H:i', strtotime($flight['start_time'])); ?></p>
            <p style="margin: 8px 0;"><strong style="color: var(--accent-cyan);">Arrival:</strong> <?php echo date('M d, Y - H:i', strtotime($flight['end_time'])); ?></p>
            <p class="flight-price" style="margin-top: 16px; font-size: 2rem;">$<?php echo number_format($flight['fees'], 2); ?></p>
        </div>
        
        <form method="POST">
            <h4 style="margin-bottom: 16px;">Select Payment Method</h4>
            
            <label style="display: flex; align-items: center; gap: 12px; padding: 16px; background: rgba(255,255,255,0.03); border-radius: 10px; margin-bottom: 10px; cursor: pointer; border: 1px solid var(--border-color); transition: 0.3s;">
                <input type="radio" name="pay_type" value="account" checked>
                <div>
                    <strong>💳 Pay from Account</strong>
                    <p style="margin: 4px 0 0; font-size: 0.9rem; color: var(--text-muted);">Balance: $<?php echo number_format($user['balance'], 2); ?></p>
                </div>
            </label>
            
            <label style="display: flex; align-items: center; gap: 12px; padding: 16px; background: rgba(255,255,255,0.03); border-radius: 10px; margin-bottom: 20px; cursor: pointer; border: 1px solid var(--border-color); transition: 0.3s;">
                <input type="radio" name="pay_type" value="cash">
                <div>
                    <strong>💵 Pay at Company</strong>
                    <p style="margin: 4px 0 0; font-size: 0.9rem; color: var(--text-muted);">Cash payment (requires approval)</p>
                </div>
            </label>
            
            <div style="display: flex; gap: 12px;">
                <button type="submit" name="book" class="btn btn-success" style="flex: 1;">✓ Confirm Booking</button>
                <a href="passenger_messages.php?to=<?php echo $flight['company_id']; ?>" class="btn btn-secondary">💬 Message</a>
            </div>
        </form>
        
        <p style="text-align: center; margin-top: 20px;">
            <a href="search_flight.php" style="color: var(--text-muted);">← Back to Search</a>
        </p>
    </div>
</div>
</body>
</html>