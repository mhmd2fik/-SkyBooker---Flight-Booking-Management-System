<?php
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'company') {
    header("Location: index.php");
    exit();
}

$company_id = $_SESSION['user_id'];
$company = $conn->query("SELECT logo_path FROM users WHERE id = $company_id")->fetch_assoc();

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['flight_name'];
    $fid = $_POST['flight_id'];
    // Join array of cities into string
    $itinerary = implode(",", $_POST['cities']); 
    $fees = $_POST['fees'];
    $max = $_POST['max_passengers'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];
    $company_id = $_SESSION['user_id'];

    // Check if flight ID already exists
    $check = $conn->prepare("SELECT id FROM flights WHERE flight_unique_id = ?");
    $check->bind_param("s", $fid);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $error = "Flight ID '$fid' already exists. Please use a unique ID.";
    } else {
        $sql = "INSERT INTO flights (company_id, flight_name, flight_unique_id, itinerary, fees, max_passengers, start_time, end_time) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssdiss", $company_id, $name, $fid, $itinerary, $fees, $max, $start, $end);
        $stmt->execute();
        header("Location: company_home.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Flight</title>
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
            <a href="add_flight.php" class="active">Add Flight</a>
            <a href="company_messages.php" target="_blank">Messages</a>
            <a href="company_profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="form-box" style="max-width: 600px;">
            <div style="text-align: center; margin-bottom: 24px;">
                <span style="font-size: 3rem;">🛫</span>
                <h2 style="margin-top: 12px;">Create New Flight</h2>
            </div>
            
            <?php if($error): ?>
                <p class="error-msg"><?php echo $error; ?></p>
            <?php endif; ?>
            
            <form method="POST">
                <input type="text" name="flight_name" placeholder="Flight Name (e.g. Sky Express 101)" required>
                <input type="text" name="flight_id" placeholder="Flight ID (e.g. SE-101)" required>
                
                <label>Route (Cities):</label>
                <div id="itineraryContainer">
                    <input type="text" name="cities[]" placeholder="🛫 Departure City" required>
                    <input type="text" name="cities[]" placeholder="🛬 Arrival City" required>
                </div>
                <button type="button" id="addCityBtn" class="btn btn-secondary btn-small" style="margin: 10px 0;">+ Add Stopover</button>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <label>Price ($)</label>
                        <input type="number" name="fees" placeholder="0.00" step="0.01" required>
                    </div>
                    <div>
                        <label>Max Passengers</label>
                        <input type="number" name="max_passengers" placeholder="100" required>
                    </div>
                </div>
                
                <label>Departure Time:</label>
                <input type="datetime-local" name="start_time" required>
                
                <label>Arrival Time:</label>
                <input type="datetime-local" name="end_time" required>

                <button type="submit" style="width: 100%; margin-top: 20px;">✓ Create Flight</button>
                
                <p style="text-align: center; margin-top: 16px;">
                    <a href="company_home.php" style="color: var(--text-muted);">← Back to Dashboard</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>