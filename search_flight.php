<?php
include 'db.php';

// Fetch user data for nav
$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

$results = [];
if (isset($_GET['from']) && isset($_GET['to'])) {
    $from = $_GET['from'];
    $to = $_GET['to'];
    // Simple search: checks if both cities exist in the itinerary string
    $sql = "SELECT * FROM flights WHERE itinerary LIKE ? AND itinerary LIKE ? AND status='ongoing'";
    $param1 = "%$from%";
    $param2 = "%$to%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $param1, $param2);
    $stmt->execute();
    $results = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Search Flights</title>
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
            <a href="search_flight.php" class="active">Flights</a>
            <a href="passenger_messages.php" target="_blank">Messages</a>
            <a href="passenger_profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>
    
    <div class="hero" style="min-height: 50vh; padding: 60px 20px;">
        <h1>Find Your Perfect Flight</h1>
        <p>Search thousands of flights and find the best deals for your next adventure.</p>
        
        <div class="search-card">
            <form method="GET" class="search-form">
                <input type="text" name="from" placeholder="✈️ From (City)" value="<?php echo isset($_GET['from']) ? htmlspecialchars($_GET['from']) : ''; ?>" required>
                <input type="text" name="to" placeholder="📍 To (City)" value="<?php echo isset($_GET['to']) ? htmlspecialchars($_GET['to']) : ''; ?>" required>
                <button type="submit">🔍 Search Flights</button>
            </form>
        </div>
    </div>

    <div class="container">
        <?php if($results && $results->num_rows > 0): ?>
            <h3 class="section-title">Available Flights</h3>
            <div class="grid">
                <?php while($row = $results->fetch_assoc()): ?>
                <div class="card">
                    <h4><?php echo htmlspecialchars($row['flight_name']); ?></h4>
                    <p><strong>Route:</strong> <?php echo htmlspecialchars($row['itinerary']); ?></p>
                    <p class="flight-price">$<?php echo number_format($row['fees'], 2); ?></p>
                    <p><strong>Departure:</strong> <?php echo date('M d, Y - H:i', strtotime($row['start_time'])); ?></p>
                    <div style="margin-top: 16px;">
                        <a href="flight_info.php?id=<?php echo $row['id']; ?>" class="btn btn-primary" style="width: 100%;">Book Now</a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php elseif(isset($_GET['from'])): ?>
            <div style="text-align: center; padding: 60px 20px;">
                <h3 style="color: var(--text-secondary);">No flights found</h3>
                <p style="color: var(--text-muted);">Try searching for different cities.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>