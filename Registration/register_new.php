<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "An account with this email already exists. Please sign in instead.";
        } else {
            // Insert new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, member_since) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sss", $name, $email, $hashedPassword);

            if ($stmt->execute()) {
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['member_since'] = date("Y-m-d H:i:s");

                header("Location: ../index.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - EduWell</title>
    <link rel="stylesheet" href="../css/auth.css">
    <style>
        /* Force white background */
        html, body {
            background: #ffffff !important;
            background-color: #ffffff !important;
            background-image: none !important;
        }
        
        .auth-container {
            background: #ffffff !important;
            background-color: #ffffff !important;
        }
    </style>
</head>
<body style="background: #ffffff !important;">
    <div class="auth-container" style="background: #ffffff !important;">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">Create Account</h1>
                <p class="auth-subtitle">Join EduWell and start your learning journey</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="message message-error">
                    <span class="message-icon">⚠️</span>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="" id="registerForm">
                <div class="form-group">
                    <label for="name" class="form-label">Full Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="form-input" 
                        placeholder="Enter your full name"
                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                        required
                        autocomplete="name"
                    >
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="Enter your email"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        required
                        autocomplete="email"
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-field">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input" 
                            placeholder="Create a password"
                            required
                            autocomplete="new-password"
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <span id="toggleText">Show</span>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <div class="strength-text" id="strengthText">Enter a password</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                    <div class="password-field">
                        <input 
                            type="password" 
                            id="confirmPassword" 
                            name="confirmPassword" 
                            class="form-input" 
                            placeholder="Confirm your password"
                            required
                            autocomplete="new-password"
                        >
                        <button type="button" class="password-toggle" onclick="toggleConfirmPassword()">
                            <span id="toggleConfirmText">Show</span>
                        </button>
                    </div>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="terms" class="checkbox" required>
                    <label for="terms" class="checkbox-label">
                        I agree to the <a href="#" target="_blank">Terms of Service</a> and <a href="#" target="_blank">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary" id="registerBtn">
                    Create Account
                </button>
            </form>

            <div class="auth-links">
                <span class="auth-link">Already have an account?</span>
                <a href="signin_new.php" class="auth-link auth-link-primary">Sign in here</a>
            </div>
        </div>
    </div>

    <script>
        // Form validation and submission
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const registerBtn = document.getElementById('registerBtn');
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirmPassword');
            const termsCheckbox = document.getElementById('terms');

            // Form submission with loading state
            form.addEventListener('submit', function(e) {
                if (validateForm()) {
                    registerBtn.classList.add('btn-loading');
                    registerBtn.disabled = true;
                    registerBtn.textContent = '';
                } else {
                    e.preventDefault();
                }
            });

            // Real-time validation
            nameInput.addEventListener('blur', validateName);
            emailInput.addEventListener('blur', validateEmail);
            passwordInput.addEventListener('input', updatePasswordStrength);
            passwordInput.addEventListener('blur', validatePassword);
            confirmPasswordInput.addEventListener('blur', validateConfirmPassword);

            function validateName() {
                const name = nameInput.value.trim();
                
                if (name && name.length < 2) {
                    showFieldError(nameInput, 'Name must be at least 2 characters');
                    return false;
                } else {
                    clearFieldError(nameInput);
                    return true;
                }
            }

            function validateEmail() {
                const email = emailInput.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (email && !emailRegex.test(email)) {
                    showFieldError(emailInput, 'Please enter a valid email address');
                    return false;
                } else {
                    clearFieldError(emailInput);
                    return true;
                }
            }

            function validatePassword() {
                const password = passwordInput.value;
                
                if (password && password.length < 6) {
                    showFieldError(passwordInput, 'Password must be at least 6 characters');
                    return false;
                } else {
                    clearFieldError(passwordInput);
                    return true;
                }
            }

            function validateConfirmPassword() {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                if (confirmPassword && password !== confirmPassword) {
                    showFieldError(confirmPasswordInput, 'Passwords do not match');
                    return false;
                } else {
                    clearFieldError(confirmPasswordInput);
                    return true;
                }
            }

            function updatePasswordStrength() {
                const password = passwordInput.value;
                const strengthFill = document.getElementById('strengthFill');
                const strengthText = document.getElementById('strengthText');
                
                if (!password) {
                    strengthFill.className = 'strength-fill';
                    strengthText.textContent = 'Enter a password';
                    return;
                }
                
                let strength = 0;
                let strengthClass = '';
                let strengthLabel = '';
                
                // Length checks
                if (password.length >= 8) strength++;
                if (password.length >= 12) strength++;
                
                // Character variety checks
                if (/[a-z]/.test(password)) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^A-Za-z0-9]/.test(password)) strength++;
                
                if (strength <= 2) {
                    strengthClass = 'strength-weak';
                    strengthLabel = 'Weak';
                } else if (strength <= 4) {
                    strengthClass = 'strength-fair';
                    strengthLabel = 'Fair';
                } else if (strength <= 5) {
                    strengthClass = 'strength-good';
                    strengthLabel = 'Good';
                } else {
                    strengthClass = 'strength-strong';
                    strengthLabel = 'Strong';
                }
                
                strengthFill.className = 'strength-fill ' + strengthClass;
                strengthText.textContent = strengthLabel;
            }

            function validateForm() {
                const isNameValid = validateName();
                const isEmailValid = validateEmail();
                const isPasswordValid = validatePassword();
                const isConfirmValid = validateConfirmPassword();
                const isTermsChecked = termsCheckbox.checked;
                
                if (!isTermsChecked) {
                    showFieldError(termsCheckbox, 'You must agree to the terms and conditions');
                    return false;
                }
                
                return isNameValid && isEmailValid && isPasswordValid && isConfirmValid;
            }

            function showFieldError(input, message) {
                clearFieldError(input);
                const errorDiv = document.createElement('div');
                errorDiv.className = 'field-error';
                errorDiv.textContent = message;
                input.parentNode.appendChild(errorDiv);
                input.style.borderColor = '#e53e3e';
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

        function toggleConfirmPassword() {
            const confirmPasswordInput = document.getElementById('confirmPassword');
            const toggleConfirmText = document.getElementById('toggleConfirmText');
            
            if (confirmPasswordInput.type === 'password') {
                confirmPasswordInput.type = 'text';
                toggleConfirmText.textContent = 'Hide';
            } else {
                confirmPasswordInput.type = 'password';
                toggleConfirmText.textContent = 'Show';
            }
        }
    </script>
</body>
</html>