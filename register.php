<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure hash
    $role = $_POST['role'];
    $telephone = $_POST['telephone'];

    // Handle File Upload (Simplified)
    $logo_path = "";
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $target = "uploads/" . basename($_FILES["logo"]["name"]);
        move_uploaded_file($_FILES["logo"]["tmp_name"], $target);
        $logo_path = $target;
    }

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows > 0) {
        $error = "This email is already registered. Please use a different email or login.";
    } else {
        $sql = "INSERT INTO users (name, email, password, role, telephone, logo_path) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $name, $email, $password, $role, $telephone, $logo_path);
        
        if ($stmt->execute()) {
            header("Location: index.php");
            exit();
        } else {
            $error = "Registration failed: " . $conn->error;
        }
    }
    $check->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register - Flight System</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/main.js"></script>
</head>
<body>
    <div class="hero" style="min-height: 100vh; padding: 40px 20px;">
        <h1>Join Our Platform</h1>
        <p>Create your account and start your journey with us today.</p>
        
        <div class="form-box" style="max-width: 480px;">
            <div style="text-align: center; margin-bottom: 24px;">
                <span style="font-size: 3rem; filter: drop-shadow(0 0 20px rgba(59, 130, 246, 0.5));">✈️</span>
                <h2 style="margin-top: 12px;">Create Account</h2>
            </div>
            <?php if (isset($error)): ?>
                <p class="error-msg"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <label>Account Type:</label>
                <div style="display: flex; gap: 20px; margin: 12px 0 20px; justify-content: center;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin: 0; padding: 12px 24px; background: rgba(255,255,255,0.05); border-radius: 10px; border: 1px solid var(--border-color); transition: 0.3s;">
                        <input type="radio" name="role" value="company" checked> 
                        <span>🏢 Company</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin: 0; padding: 12px 24px; background: rgba(255,255,255,0.05); border-radius: 10px; border: 1px solid var(--border-color); transition: 0.3s;">
                        <input type="radio" name="role" value="passenger"> 
                        <span>👤 Passenger</span>
                    </label>
                </div>
                
                <input type="text" name="name" placeholder="Full Name / Company Name" required>
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="text" name="telephone" placeholder="Phone Number" required>
                
                <label>Upload Logo/Photo (Optional):</label>
                <input type="file" name="logo" id="logoInput">
                <img id="imgPreview" class="preview-img" src="#" style="display:none;">

                <button type="submit" style="width: 100%; margin-top: 16px;">Create Account</button>
                <p style="text-align: center; margin-top: 24px; color: var(--text-secondary);">
                    Already have an account? <a href="index.php">Sign In</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>