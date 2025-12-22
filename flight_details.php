<?php
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'company') {
    header("Location: index.php");
    exit();
}

$company_id = $_SESSION['user_id'];
$company = $conn->query("SELECT logo_path FROM users WHERE id = $company_id")->fetch_assoc();

$flight_id = intval($_GET['id']);

// Handle Cancellation & Refund Logic - DELETE flight from DB
if (isset($_POST['cancel_flight'])) {
    $conn->begin_transaction();
    try {
        // Get all passengers who paid and refund them
        $res = $conn->query("SELECT b.passenger_id, f.fees FROM bookings b JOIN flights f ON b.flight_id = f.id WHERE b.flight_id = $flight_id AND b.status = 'confirmed'");
        while ($row = $res->fetch_assoc()) {
            $conn->query("UPDATE users SET balance = balance + {$row['fees']} WHERE id = {$row['passenger_id']}");
        }
        // Update all bookings to cancelled so passengers can see
        $conn->query("UPDATE bookings SET status = 'cancelled' WHERE flight_id = $flight_id");
        // Delete the flight from database
        $conn->query("DELETE FROM flights WHERE id = $flight_id");
        $conn->commit();
        echo "<script>alert('Flight cancelled and removed. Passengers refunded.'); window.location='company_home.php';</script>";
        exit();
    } catch (Exception $e) { $conn->rollback(); }
}

// Handle booking status change (accept/reject pending bookings)
if (isset($_POST['update_booking'])) {
    $booking_id = intval($_POST['booking_id']);
    $new_status = $_POST['new_status'];
    
    if ($new_status == 'confirmed') {
        // Accept - just update status (they pay at company)
        $stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
    } elseif ($new_status == 'cancelled') {
        // Reject - update status to cancelled
        $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
    }
}

$flight = $conn->query("SELECT * FROM flights WHERE id = $flight_id")->fetch_assoc();
$passengers = $conn->query("SELECT u.name, u.email, b.status, b.id as booking_id FROM bookings b JOIN users u ON b.passenger_id = u.id WHERE b.flight_id = $flight_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Flight Details</title>
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
        <a href="company_home.php">Dashboard</a>
        <a href="add_flight.php">Add Flight</a>
        <a href="company_messages.php">Messages</a>
        <a href="company_profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>
<div class="container">
    <!-- Flight Info Card -->
    <div class="card" style="margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
            <div>
                <h2 style="margin-bottom: 16px;"><?php echo htmlspecialchars($flight['flight_name']); ?></h2>
                <p><strong style="color: var(--accent-cyan);">Flight ID:</strong> <?php echo htmlspecialchars($flight['flight_unique_id']); ?></p>
                <p><strong style="color: var(--accent-cyan);">Route:</strong> <?php echo htmlspecialchars($flight['itinerary']); ?></p>
                <p><strong style="color: var(--accent-cyan);">Departure:</strong> <?php echo date('M d, Y - H:i', strtotime($flight['start_time'])); ?></p>
                <p><strong style="color: var(--accent-cyan);">Arrival:</strong> <?php echo date('M d, Y - H:i', strtotime($flight['end_time'])); ?></p>
                <p><strong style="color: var(--accent-cyan);">Capacity:</strong> <?php echo $flight['max_passengers']; ?> passengers</p>
            </div>
            <div style="text-align: right;">
                <p class="flight-price" style="font-size: 2.5rem;">$<?php echo number_format($flight['fees'], 2); ?></p>
                <p><span class="status-<?php echo $flight['status']; ?>"><?php echo ucfirst($flight['status']); ?></span></p>
                <form method="POST" style="margin-top: 16px;">
                    <button name="cancel_flight" class="btn btn-danger">🚫 Cancel Flight</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Passenger Manifest -->
    <h3 class="section-title">👥 Passenger Manifest</h3>
    <table>
        <thead>
            <tr>
                <th>Passenger</th>
                <th>Email</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if($passengers->num_rows > 0): ?>
                <?php while($p = $passengers->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($p['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($p['email']); ?></td>
                    <td><span class="badge badge-<?php echo $p['status']; ?>"><?php echo ucfirst($p['status']); ?></span></td>
                    <td>
                        <?php if($p['status'] == 'pending'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="booking_id" value="<?php echo $p['booking_id']; ?>">
                            <input type="hidden" name="new_status" value="confirmed">
                            <button type="submit" name="update_booking" value="1" class="btn btn-small btn-success">✓ Accept</button>
                        </form>
                        <form method="POST" style="display:inline; margin-left: 8px;">
                            <input type="hidden" name="booking_id" value="<?php echo $p['booking_id']; ?>">
                            <input type="hidden" name="new_status" value="cancelled">
                            <button type="submit" name="update_booking" value="1" class="btn btn-small btn-danger">✕ Reject</button>
                        </form>
                        <?php else: ?>
                            <span style="color: var(--text-muted);">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center; padding: 40px; color: var(--text-muted);">
                        No passengers booked yet.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div style="margin-top: 2rem; display: flex; gap: 12px;">
        <a href="company_home.php" class="btn btn-secondary">← Back to Dashboard</a>
        <a href="company_messages.php" class="btn btn-primary">💬 Messages</a>
    </div>
</div>
</body>
</html>