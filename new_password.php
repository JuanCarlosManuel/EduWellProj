<?php
include 'db.php';
session_start();

$message = '';
$messageType = '';
$tokenValid = false;
$token = $_GET['token'] ?? '';

// Verify token (NO EXPIRATION CHECK)
if ($token) {
    $stmt = $conn->prepare("SELECT pr.user_id, u.email, u.name 
                           FROM password_resets pr 
                           JOIN users u ON pr.user_id = u.id 
                           WHERE pr.token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $tokenValid = true;
        $resetData = $result->fetch_assoc();
    } else {
        $message = "❌ Invalid reset link. Please request a new one.";
        $messageType = 'error';
    }
    $stmt->close();
}

// Process password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (strlen($new_password) < 6) {
        $message = "❌ Password must be at least 6 characters long.";
        $messageType = 'error';
    } elseif ($new_password !== $confirm_password) {
        $message = "❌ Passwords do not match.";
        $messageType = 'error';
    } else {
        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
        $user_id = $resetData['user_id'];
        
        // Update password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $user_id);
        $stmt->execute();
        $stmt->close();
        
        // Delete used token
        $stmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->close();
        
        $message = "✅ Password reset successfully! You can now log in with your new password.";
        $messageType = 'success';
        $tokenValid = false; // Prevent form from showing again
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - EduWell</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #ffffffff 0%, #ffffffff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .reset-container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 450px;
            width: 100%;
        }
        h2 {
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }
        .subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #5568d3;
        }
        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h2>🔑 Set New Password</h2>
        <p class="subtitle">Choose a strong password for your account.</p>
        
        <?php if ($message): ?>
            <div class="message <?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($tokenValid): ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6">
                    <p class="password-requirements">Must be at least 6 characters</p>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>
                
                <button type="submit">Reset Password</button>
            </form>
        <?php elseif (!$message): ?>
            <div class="message error">
                ❌ Invalid reset link.
            </div>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="Registration/signin_new.php">← Back to Login</a>
        </div>
    </div>
</body>
</html>