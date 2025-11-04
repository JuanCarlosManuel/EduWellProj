<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Generate token & expiry
        $token = bin2hex(random_bytes(16));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $update = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $update->bind_param("sss", $token, $expiry, $email);
        $update->execute();

        // Normally send email here, but for demo:
        $resetLink = "http://localhost/eduwell/Registration/reset_password.php?token=$token";

        $success = "✅ A reset link has been generated: <a href='$resetLink'>$resetLink</a>";
    } else {
        $error = "⚠️ No account found with that email.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../css/profile.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include '../navbar.php'; ?>

    <div class="form-container">
        <h2>Forgot Password</h2>

        <?php if (!empty($error)): ?>
            <p class="error-message"><?= $error; ?></p>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <p class="success-message"><?= $success; ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Email:</label>
            <input type="email" name="email" required>
            <button type="submit">Send Reset Link</button>
        </form>

        <p>Back to <a href="signin_new.php">Sign In</a></p>
    </div>

    <?php include '../footer.php'; ?>
</body>
</html>
