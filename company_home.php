<?php
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'company') {
    header("Location: index.php");
    exit();
}

$company_id = $_SESSION['user_id'];
// Fetch company info for logo
$company = $conn->query("SELECT * FROM users WHERE id = $company_id")->fetch_assoc();
// Fetch flights
$result = $conn->query("SELECT * FROM flights WHERE company_id = $company_id");
$flights = [];
$ongoing = 0;
$completed = 0;
$cancelled = 0;
while($row = $result->fetch_assoc()) {
    $flights[] = $row;
    if($row['status'] == 'ongoing') $ongoing++;
    elseif($row['status'] == 'completed') $completed++;
    else $cancelled++;
}
// Count pending bookings
$pending_bookings = $conn->query("SELECT COUNT(*) as cnt FROM bookings b JOIN flights f ON b.flight_id = f.id WHERE f.company_id = $company_id AND b.status = 'pending'")->fetch_assoc()['cnt'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Company Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/main.js"></script>
</head>
<body>
    <nav>
        <div class="logo">
            <?php if($company['logo_path']): ?>
                <img src="<?php echo $company['logo_path']; ?>" alt="Logo" class="logo-icon">
            <?php else: ?>
                <span class="logo-plane">✈️</span>
            <?php endif; ?>
            <?php echo htmlspecialchars($_SESSION['name']); ?>
        </div>
        <div>
            <a href="company_home.php" class="active">Dashboard</a>
            <a href="add_flight.php">Add Flight</a>
            <a href="company_messages.php">Messages</a>
            <a href="company_profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($flights); ?></div>
                <div class="stat-label">Total Flights</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $ongoing; ?></div>
                <div class="stat-label">Active Flights</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $pending_bookings; ?></div>
                <div class="stat-label">Pending Bookings</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">$<?php echo number_format($company['balance'], 0); ?></div>
                <div class="stat-label">Balance</div>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="margin: 0;">Your Flights</h2>
            <a href="add_flight.php" class="btn btn-primary">+ Add New Flight</a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Flight ID</th>
                    <th>Name</th>
                    <th>Route</th>
                    <th>Price</th>
                    <th>Capacity</th>
                    <th>Departure</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($flights) > 0): ?>
                    <?php foreach($flights as $row): ?>
                    <tr onclick="window.location='flight_details.php?id=<?php echo $row['id']; ?>'" style="cursor:pointer;">
                        <td><?php echo htmlspecialchars($row['flight_unique_id']); ?></td>
                        <td><strong><?php echo htmlspecialchars($row['flight_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['itinerary']); ?></td>
                        <td style="color: var(--accent-cyan); font-weight: 600;">$<?php echo number_format($row['fees'], 2); ?></td>
                        <td><?php echo $row['max_passengers']; ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($row['start_time'])); ?></td>
                        <td><span class="status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                        <td>
                            <a href="flight_details.php?id=<?php echo $row['id']; ?>" class="btn btn-small btn-secondary">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                            No flights yet. <a href="add_flight.php">Create your first flight</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>