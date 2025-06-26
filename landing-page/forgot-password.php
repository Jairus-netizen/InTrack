<?php
session_start();

require_once './database/dbconnection.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_phone = $_POST['email-phone'] ?? '';
    
    // Check if email/phone exists in database
    $stmt = $conn->prepare("SELECT account_id, email, phone FROM accounts WHERE (email = ? OR phone = ?) AND is_active = TRUE");
    $stmt->bind_param("ss", $email_phone, $email_phone);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Generate 6-digit OTP
        $otp = rand(100000, 999999);
        $otp_expiry = time() + 300; // OTP valid for 5 minutes
        
        // Store OTP in session
        $_SESSION['reset_email'] = $user['email'];
        $_SESSION['reset_otp'] = $otp;
        $_SESSION['reset_otp_expiry'] = $otp_expiry;
        
        // Send OTP via email
        require_once '/xampp/htdocs/intrack-cathy/landing-page/send_email.php';
        $subject = "Password Reset OTP";
        $message = "Your OTP for password reset is: $otp\n\nThis OTP is valid for 5 minutes.";
        
        if (sendEmail($user['email'], $subject, $message)) {
            header("Location: otp-confirmation.php");
            exit();
        } else {
            $error = "Failed to send OTP. Please try again.";
        }
    } else {
        $error = "No account found with this email/phone.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="styles/forgot-password.css">
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
    </style>
</head>

<body>
    <section class="forgot-password">
        <div class="container">
            <div class="fp-wrapper">
                <div class="fp-form">
                    <div class="fp-form-wrapper">
                        <div class="fp-form-header">
                            <h1>Forgot Password</h1>
                        </div>
                        <?php if(isset($error)): ?>
                            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <form action="" method="POST" class="fp-form-content">
                            <div class="form-groups">
                                <label for="email-phone">Email / Phone No.</label>
                                <input type="text" id="email-phone" name="email-phone" required>
                            </div>
                            <button class="next" type="submit">Next</button>
                            <div class="remember-password">
                                Remember your password? <a href="./login.php">Login here</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php if(isset($error)): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo addslashes($error); ?>',
            });
        </script>
    <?php endif; ?>
</body>

</html>