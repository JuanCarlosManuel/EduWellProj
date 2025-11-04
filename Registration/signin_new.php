<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, email, password, member_since FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $name, $userEmail, $hashedPassword, $member_since);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        if (password_verify($password, $hashedPassword)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $userEmail;
            $_SESSION['member_since'] = $member_since;

            header("Location: ../index.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - EduWell</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/sregister.css">
    <style>
        /* Enhanced mobile responsiveness */
        @media (max-width: 768px) {
            body {
                padding: 16px;
            }
            
            .container {
                max-width: 100%;
            }
            
            .card {
                padding: 20px;
                border-radius: 16px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .field {
                margin-bottom: 16px;
            }
            
            input[type="text"],
            input[type="email"],
            input[type="password"] {
                padding: 14px 16px;
                font-size: 16px;
            }
            
            .btn {
                padding: 14px 16px;
                font-size: 16px;
            }
        }
        
        @media (max-width: 480px) {
            .card {
                padding: 16px;
                margin: 0;
            }
            
            h1 {
                font-size: 22px;
            }
        }
        
        /* Enhanced form styling */
        .error-message {
            background: #fef2f2;
            color: #dc2626;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #fecaca;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .error-message::before {
            content: '⚠️';
            font-size: 16px;
        }
        
        /* Loading state */
        .btn.loading {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        .btn.loading::after {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 8px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Password toggle styling */
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--muted);
            font-size: 12px;
            user-select: none;
        }
        
        .field {
            position: relative;
        }
        
        .field input[type="password"] {
            padding-right: 50px;
        }
        
        /* Field error styling */
        .field-error {
            color: #c33;
            font-size: 12px;
            margin-top: 4px;
        }
        
        /* Forgot password link styling */
        .forgot-password-link {
            display: inline-block;
            margin-top: 12px;
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s;
        }
        
        .forgot-password-link:hover {
            color: #5568d3;
            text-decoration: underline;
        }
    </style>
</head>

<body>
<div class="container">
  <div class="card">
    <h1>Welcome Back</h1>
    <p class="subtitle">Sign in to continue</p>

    <?php if (!empty($error)): ?>
        <p class="error-message"><?= $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="" id="signinForm">
      <div class="field">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="you@example.com" required autocomplete="email">
      </div>

      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="••••••" required autocomplete="current-password">
        <div class="password-toggle" onclick="togglePassword()">
          <span id="toggleText">Show</span>
        </div>
      </div>

      <button type="submit" name="login" class="btn primary" id="signinBtn">Sign In</button>
    </form>

    <div class="switch">
      <a href="../reset_password.php" class="forgot-password-link">Forgot Password?</a>
    </div>

    <div class="switch">
      Don't have an account? <a href="register_new.php">Register here</a>
    </div>
  </div>
</div>

<script>
    // Enhanced form functionality
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('signinForm');
        const signinBtn = document.getElementById('signinBtn');
        
        // Form submission with loading state
        form.addEventListener('submit', function(e) {
            signinBtn.classList.add('loading');
            signinBtn.textContent = 'Signing In...';
        });
        
        // Real-time validation
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        
        emailInput.addEventListener('blur', validateEmail);
        passwordInput.addEventListener('blur', validatePassword);
        
        function validateEmail() {
            const email = emailInput.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                showFieldError(emailInput, 'Please enter a valid email address');
            } else {
                clearFieldError(emailInput);
            }
        }
        
        function validatePassword() {
            const password = passwordInput.value;
            
            if (password && password.length < 6) {
                showFieldError(passwordInput, 'Password must be at least 6 characters');
            } else {
                clearFieldError(passwordInput);
            }
        }
        
        function showFieldError(input, message) {
            clearFieldError(input);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            errorDiv.textContent = message;
            errorDiv.style.color = '#c33';
            errorDiv.style.fontSize = '12px';
            errorDiv.style.marginTop = '4px';
            input.parentNode.appendChild(errorDiv);
            input.style.borderColor = '#c33';
        }
        
        function clearFieldError(input) {
            const existingError = input.parentNode.querySelector('.field-error');
            if (existingError) {
                existingError.remove();
            }
            input.style.borderColor = '';
        }
    });
    
    // Password toggle functionality
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleText = document.getElementById('toggleText');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleText.textContent = 'Hide';
        } else {
            passwordInput.type = 'password';
            toggleText.textContent = 'Show';
        }
    }
</script>
</body>
</html>