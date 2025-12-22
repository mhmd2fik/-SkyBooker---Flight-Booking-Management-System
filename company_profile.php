<?php
include 'db.php';

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'company') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle Add Balance (separate from profile update)
if (isset($_POST['add_balance'])) {
    $p_email = $_POST['p_email'];
    $amount = floatval($_POST['amount']);
    $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE email = ? AND role = 'passenger'");
    $stmt->bind_param("ds", $amount, $p_email);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $message = "Balance added successfully!";
    } else {
        $message = "Passenger not found or update failed.";
    }
}

// Handle Profile Update (only when update_profile is submitted)
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $bio = $_POST['bio'];
    $address = $_POST['address'];
    $telephone = $_POST['telephone'];

    // Handle Logo Upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $target = "uploads/" . basename($_FILES["logo"]["name"]);
        move_uploaded_file($_FILES["logo"]["tmp_name"], $target);
        // Update with logo
        $sql = "UPDATE users SET name=?, email=?, bio=?, address=?, telephone=?, logo_path=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $name, $email, $bio, $address, $telephone, $target, $user_id);
    } else {
        // Update without logo
        $sql = "UPDATE users SET name=?, email=?, bio=?, address=?, telephone=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $name, $email, $bio, $address, $telephone, $user_id);
    }

    if ($stmt->execute()) {
        $message = "Profile updated successfully!";
        $_SESSION['name'] = $name; // Update session name
    } else {
        $message = "Error updating profile.";
    }
}

// Fetch current user data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Company Profile</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/main.js"></script>
</head>
<body>
    <nav>
        <div class="logo">
            <?php if($user['logo_path']): ?>
                <img src="<?php echo $user['logo_path']; ?>" alt="Logo" class="logo-icon">
            <?php else: ?>
                <span class="logo-plane">✈️</span>
            <?php endif; ?>
            <?php echo htmlspecialchars($_SESSION['name']); ?>
        </div>
        <div>
            <a href="company_home.php">Dashboard</a>
            <a href="add_flight.php">Add Flight</a>
            <a href="company_messages.php">Messages</a>
            <a href="company_profile.php" class="active">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="form-box" style="max-width: 650px;">
            <div style="text-align: center; margin-bottom: 24px;">
                <?php if($user['logo_path']): ?>
                    <img src="<?php echo $user['logo_path']; ?>" alt="Logo" class="profile-img" style="width: 120px; height: 120px;">
                <?php else: ?>
                    <div style="width: 120px; height: 120px; background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple)); border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center; font-size: 3rem;">
                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <h2 style="margin-top: 16px;">Company Profile</h2>
            </div>
            
            <?php if($message): ?>
                <p class="success-msg"><?php echo $message; ?></p>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <label>Company Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                <label>Phone Number</label>
                <input type="text" name="telephone" value="<?php echo htmlspecialchars($user['telephone']); ?>" required>

                <label>Company Bio</label>
                <textarea name="bio" rows="4" placeholder="Tell passengers about your company..."><?php echo htmlspecialchars($user['bio']); ?></textarea>

                <label>Address</label>
                <textarea name="address" rows="2" placeholder="Company address..."><?php echo htmlspecialchars($user['address']); ?></textarea>

                <label>Update Logo</label>
                <input type="file" name="logo" id="logoInput" accept="image/*">
                <img id="imgPreview" class="preview-img" src="#" style="display:none; margin: 10px 0;">

                <button type="submit" name="update_profile" style="width: 100%; margin-top: 16px;">✓ Save Changes</button>
            </form>
        </div>
        
        <!-- Add Balance Card -->
        <div class="form-box" style="max-width: 650px; margin-top: 2rem;">
            <div style="text-align: center; margin-bottom: 20px;">
                <span style="font-size: 2.5rem;">💰</span>
                <h3 style="margin-top: 12px;">Transfer Funds to Passenger</h3>
            </div>
            <form method="POST">
                <input type="email" name="p_email" placeholder="Passenger Email Address" required>
                <input type="number" name="amount" placeholder="Amount ($)" step="0.01" required>
                <button type="submit" name="add_balance" class="btn btn-warning" style="width: 100%;">Transfer Funds</button>
            </form>
        </div>
        
        <p style="text-align: center; margin-top: 24px;">
            <a href="company_home.php" style="color: var(--text-muted);">← Back to Dashboard</a>
        </p>
    </div>
</body>
</html>