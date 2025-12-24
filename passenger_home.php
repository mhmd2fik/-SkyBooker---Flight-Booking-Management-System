<?php
include 'db.php';

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'passenger') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle passenger cancel booking
if (isset($_POST['cancel_booking'])) {
    $booking_id = intval($_POST['booking_id']);
    // Get booking details for refund
    $stmt = $conn->prepare("SELECT b.*, f.fees FROM bookings b JOIN flights f ON b.flight_id = f.id WHERE b.id = ? AND b.passenger_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    
    if ($booking && $booking['status'] == 'confirmed') {
        // Refund if was confirmed (paid)
        $conn->query("UPDATE users SET balance = balance + {$booking['fees']} WHERE id = $user_id");
    }
    // Update booking status to cancelled
    $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND passenger_id = ?")->execute([$booking_id, $user_id]);
    header("Location: passenger_home.php");
    exit();
}

// Fetch User Details
$user_sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch Booked Flights
$sql = "SELECT b.id as booking_id, b.status as booking_status, f.* FROM bookings b 
        JOIN flights f ON b.flight_id = f.id 
        WHERE b.passenger_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$pending_flights = [];
$confirmed_flights = [];
$cancelled_flights = [];

while ($row = $result->fetch_assoc()) {
    if ($row['booking_status'] == 'pending') {
        $pending_flights[] = $row;
    } elseif ($row['booking_status'] == 'confirmed') {
        $confirmed_flights[] = $row;
    } else {
        $cancelled_flights[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Passenger Dashboard</title>
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
            <a href="passenger_home.php" class="active">Dashboard</a>
            <a href="search_flight.php">Flights</a>
            <a href="passenger_messages.php" target="_blank">Messages</a>
            <a href="passenger_profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <!-- Hero Banner -->
    <div class="hero-banner" style="
        background: linear-gradient(rgba(10, 10, 15, 0.7), rgba(10, 10, 15, 0.8)), url('https://img.freepik.com/free-photo/bermuda-triangle-mystery-event_23-2151625818.jpg?semt=ais_hybrid&w=740&q=80');
        background-size: cover;
        background-position: center;
        padding: 80px 20px;
        text-align: center;
        margin-bottom: 2rem;
    ">
        <h1 style="font-size: 2.5rem; margin-bottom: 12px; background: linear-gradient(135deg, #fff, var(--accent-cyan)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
            Welcome Back, <?php echo htmlspecialchars($user['name']); ?>!
        </h1>
        <p style="color: var(--text-secondary); font-size: 1.1rem; max-width: 600px; margin: 0 auto 24px;">
            Your next adventure awaits. Search for flights or connect with airlines.
        </p>
        <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
            <a href="search_flight.php" class="btn btn-primary" style="padding: 14px 32px;">
                🔍 Search Flights
            </a>
            <a href="passenger_messages.php" class="btn btn-secondary" style="padding: 14px 32px;">
                💬 Messages
            </a>
        </div>
    </div>

    <div class="container">
        <!-- Profile Header -->
        <div class="profile-header">
            <?php if($user['logo_path']): ?>
                <img src="<?php echo $user['logo_path']; ?>" alt="Profile" class="profile-img">
            <?php else: ?>
                <div class="profile-img" style="background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple)); display:flex; align-items:center; justify-content:center; font-size: 2rem;">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
            <?php endif; ?>
            
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
                <p class="profile-balance">$<?php echo number_format($user['balance'], 2); ?></p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($pending_flights); ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($confirmed_flights); ?></div>
                <div class="stat-label">Confirmed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($cancelled_flights); ?></div>
                <div class="stat-label">Cancelled</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($pending_flights) + count($confirmed_flights) + count($cancelled_flights); ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
        </div>

        <!-- Pending Flights -->
        <h3 class="section-title">⏳ Pending Flights</h3>
        <div class="grid">
            <?php if(count($pending_flights) > 0): ?>
                <?php foreach($pending_flights as $flight): ?>
                <div class="card">
                    <h4><?php echo htmlspecialchars($flight['flight_name']); ?></h4>
                    <p><strong>Route:</strong> <?php echo htmlspecialchars($flight['itinerary']); ?></p>
                    <p><strong>Date:</strong> <?php echo date("d M Y - H:i", strtotime($flight['start_time'])); ?></p>
                    <p class="status-pending">Awaiting Approval</p>
                    <form method="POST" style="margin-top:16px;">
                        <input type="hidden" name="booking_id" value="<?php echo $flight['booking_id']; ?>">
                        <button type="submit" name="cancel_booking" class="btn btn-danger btn-small">Cancel Booking</button>
                    </form>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card" style="text-align: center; padding: 40px;">
                    <p style="color: var(--text-muted);">No pending flights</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Confirmed Flights -->
        <h3 class="section-title">✅ Confirmed Flights</h3>
        <div class="grid">
            <?php if(count($confirmed_flights) > 0): ?>
                <?php foreach($confirmed_flights as $flight): ?>
                <div class="card">
                    <h4><?php echo htmlspecialchars($flight['flight_name']); ?></h4>
                    <p><strong>Route:</strong> <?php echo htmlspecialchars($flight['itinerary']); ?></p>
                    <p><strong>Date:</strong> <?php echo date("d M Y - H:i", strtotime($flight['start_time'])); ?></p>
                    <p class="status-confirmed">Confirmed</p>
                    <form method="POST" style="margin-top:16px;">
                        <input type="hidden" name="booking_id" value="<?php echo $flight['booking_id']; ?>">
                        <button type="submit" name="cancel_booking" class="btn btn-danger btn-small">Cancel & Refund</button>
                    </form>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card" style="text-align: center; padding: 40px;">
                    <p style="color: var(--text-muted);">No confirmed flights</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Cancelled Flights -->
        <h3 class="section-title">❌ Cancelled Flights</h3>
        <div class="grid">
            <?php if(count($cancelled_flights) > 0): ?>
                <?php foreach($cancelled_flights as $flight): ?>
                <div class="card" style="opacity: 0.6;">
                    <h4><?php echo htmlspecialchars($flight['flight_name']); ?></h4>
                    <p><strong>Route:</strong> <?php echo htmlspecialchars($flight['itinerary']); ?></p>
                    <p><strong>Date:</strong> <?php echo date("d M Y - H:i", strtotime($flight['start_time'])); ?></p>
                    <p class="status-cancelled">Cancelled</p>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card" style="text-align: center; padding: 40px;">
                    <p style="color: var(--text-muted);">No cancelled flights</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>