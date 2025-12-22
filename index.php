<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];

        if ($user['role'] == 'company') {
            header("Location: company_home.php");
        } else {
            header("Location: passenger_home.php");
        }
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Flight System - Login</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/main.js"></script>
</head>
<body>
    <div class="hero" style="min-height: 100vh;">
        <h1>Travel Without Limits</h1>
        <p>Book your next adventure with our premium flight booking system. Experience seamless travel planning.</p>
        
        <div class="form-box" style="max-width: 420px;">
            <div style="text-align: center; margin-bottom: 24px;">
                <span style="font-size: 3.5rem; filter: drop-shadow(0 0 20px rgba(59, 130, 246, 0.5));">✈️</span>
                <h2 style="margin-top: 12px;">Welcome Back</h2>
                <p style="color: var(--text-muted); margin-top: 8px;">Sign in to continue your journey</p>
            </div>
            <?php if(isset($error)) echo "<p class='error-msg'>$error</p>"; ?>
            <form method="POST">
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" style="width: 100%; margin-top: 10px;">Sign In</button>
                <p style="text-align: center; margin-top: 24px; color: var(--text-secondary);">
                    Don't have an account? <a href="register.php" style="font-size: small !important;">Create Account</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>