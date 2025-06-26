<?php
session_start();

// Redirect if not verified
if (!isset($_SESSION['otp_verified'])) {
    header("Location: forgot-password.php");
    exit();
}

require_once './database/dbconnection.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new-password'] ?? '';
    $confirm_password = $_POST['confirm-password'] ?? '';
    
    // Validate passwords
    if (empty($new_password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE accounts SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $_SESSION['reset_email']);
        
        if ($stmt->execute()) {
            // Clear all session variables
            session_unset();
            session_destroy();
            
            // Redirect to login with success message
            header("Location: login.php?password_reset=success");
            exit();
        } else {
            $error = "Failed to reset password. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="styles/reset-password.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .error-message {
            color: red;
            margin-bottom: 15px;
            padding: 10px;
            background: #ffeeee;
            border: 1px solid #ffcccc;
            border-radius: 4px;
            text-align: center;
        }
        .pass-div {
            position: relative;
        }
        .eye-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
    </style>
</head>

<body>
    <section class="reset-password">
        <div class="container">
            <div class="rp-wrapper">
                <div class="rp-form">
                    <div class="rp-form-wrapper">
                        <div class="rp-form-header">
                            <h1>Reset Password</h1>
                            <?php if(isset($error)): ?>
                                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>
                        </div>
                        <form action="" method="POST" class="rp-form-content">
                            <div class="form-groups">
                                <label for="new-password">New Password</label>
                                <div class="pass-div">
                                    <input type="password" id="new-password" name="new-password" required>
                                    <i class="fa-solid fa-eye-slash eye-icon" id="togglePassword1" onclick="togglePassword('new-password', 'togglePassword1')"></i>
                                </div>
                            </div>
                            <div class="form-groups">
                                <label for="confirm-password">Confirm New Password</label>
                                <div class="pass-div">
                                    <input type="password" id="confirm-password" name="confirm-password" required>
                                    <i class="fa-solid fa-eye-slash eye-icon" id="togglePassword2" onclick="togglePassword('confirm-password', 'togglePassword2')"></i>
                                </div>
                            </div>
                            <button class="confirm" type="submit">Confirm</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <script>
        function togglePassword(fieldId, iconId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(iconId);
            
            if (field.type === "password") {
                field.type = "text";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            } else {
                field.type = "password";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            }
        }
    </script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
</body>

</html>