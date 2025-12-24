<?php
include 'db.php';

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'passenger') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    
    // We start with current paths
    $current_sql = "SELECT logo_path, passport_path FROM users WHERE id = $user_id";
    $curr_res = $conn->query($current_sql)->fetch_assoc();
    $photo_path = $curr_res['logo_path'];
    $passport_path = $curr_res['passport_path'];

    // Handle Profile Photo Upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $photo_path = "uploads/p_" . time() . "_" . basename($_FILES["photo"]["name"]);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $photo_path);
    }

    // Handle Passport Image Upload
    if (isset($_FILES['passport']) && $_FILES['passport']['error'] == 0) {
        $passport_path = "uploads/pass_" . time() . "_" . basename($_FILES["passport"]["name"]);
        move_uploaded_file($_FILES["passport"]["tmp_name"], $passport_path);
    }

    $sql = "UPDATE users SET name=?, email=?, telephone=?, logo_path=?, passport_path=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $name, $email, $telephone, $photo_path, $passport_path, $user_id);

    if ($stmt->execute()) {
        $message = "Profile and documents updated successfully!";
        $_SESSION['name'] = $name;
    } else {
        $message = "Error updating profile: " . $conn->error;
    }
}

// Fetch current data
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Profile</title>
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
            <a href="passenger_messages.php" target="_blank">Messages</a>
            <a href="passenger_profile.php" class="active">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="form-box" style="max-width: 800px;">
            <div style="text-align: center; margin-bottom: 24px;">
                <span style="font-size: 3rem;">👤</span>
                <h2 style="margin-top: 12px;">My Profile</h2>
            </div>
            
            <?php if($message): ?>
                <p class="success-msg"><?php echo $message; ?></p>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="flex-row">
                    <div class="flex-item">
                        <label>Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

                        <label>Email Address</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                        <label>Phone Number</label>
                        <input type="text" name="telephone" value="<?php echo htmlspecialchars($user['telephone']); ?>" required>
                        
                        <label>Account Balance</label>
                        <input type="text" value="$<?php echo number_format($user['balance'], 2); ?>" disabled style="color: var(--success); font-weight: 600;">
                    </div>

                    <div class="flex-item">
                        <label>Profile Photo</label>
                        <input type="file" name="photo" id="photoInput" accept="image/*">
                        <img id="photoPreview" class="doc-preview" src="<?php echo $user['logo_path'] ?: '#'; ?>" style="<?php echo $user['logo_path'] ? '' : 'display:none;'; ?>" alt="Profile">

                        <label style="margin-top: 24px;">Passport Image</label>
                        <input type="file" name="passport" id="passportInput" accept="image/*">
                        <img id="passportPreview" class="doc-preview" src="<?php echo $user['passport_path'] ?: '#'; ?>" style="<?php echo $user['passport_path'] ? '' : 'display:none;'; ?>" alt="Passport">
                    </div>
                </div>

                <div style="text-align: center; margin-top: 32px;">
                    <button type="submit">✓ Save Changes</button>
                </div>
                
                <p style="text-align: center; margin-top: 20px;">
                    <a href="passenger_home.php" style="color: var(--text-muted);">← Back to Dashboard</a>
                </p>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            function readURL(input, target) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $(target).attr('src', e.target.result).hide().fadeIn(400);
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }
            $("#photoInput").change(function() { readURL(this, '#photoPreview'); });
            $("#passportInput").change(function() { readURL(this, '#passportPreview'); });
        });
    </script>
</body>
</html>