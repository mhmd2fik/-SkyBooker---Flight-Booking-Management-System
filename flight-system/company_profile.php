<?php
include 'db.php';

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'company') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
<html>
<head>
    <title>Company Profile</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/main.js"></script>
</head>
<body>
    <nav>
        <div class="logo">✈️ Edit Profile</div>
        <div>
            <a href="company_home.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="form-box" style="max-width: 600px;">
            <h2>Company Details</h2>
            
            <?php if($message): ?>
                <p style="color: green; text-align: center;"><?php echo $message; ?></p>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div style="text-align: center; margin-bottom: 20px;">
                    <?php if($user['logo_path']): ?>
                        <img src="<?php echo $user['logo_path']; ?>" alt="Current Logo" style="max-width: 150px; border-radius: 50%;">
                    <?php else: ?>
                        <div style="width: 100px; height: 100px; background: #ddd; border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center;">No Logo</div>
                    <?php endif; ?>
                </div>

                <label>Company Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                <label>Telephone</label>
                <input type="text" name="telephone" value="<?php echo htmlspecialchars($user['telephone']); ?>" required>

                <label>Bio</label>
                <textarea name="bio" rows="4" style="width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px;"><?php echo htmlspecialchars($user['bio']); ?></textarea>

                <label>Address</label>
                <textarea name="address" rows="2" style="width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px;"><?php echo htmlspecialchars($user['address']); ?></textarea>

                <label>Update Logo</label>
                <input type="file" name="logo" id="logoInput">
                <img id="imgPreview" class="preview-img" src="#" style="display:none; margin: 10px auto;">

                <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>
</body>
</html>