<?php
session_start();

// Redirect if not an admin or not pending authentication
if (!isset($_SESSION['pending_admin_auth']) || $_SESSION['account_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Check if OTP is expired
if (isset($_SESSION['admin_otp_expiry']) && time() > $_SESSION['admin_otp_expiry']) {
    unset($_SESSION['admin_otp']);
    unset($_SESSION['admin_otp_expiry']);
    $error = "OTP has expired. Please login again.";
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle OTP verification
    if (isset($_POST['otp1'], $_POST['otp2'], $_POST['otp3'], $_POST['otp4'], $_POST['otp5'], $_POST['otp6'])) {
        $user_otp = $_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . $_POST['otp4'] . $_POST['otp5'] . $_POST['otp6'];
        
        if ($user_otp == $_SESSION['admin_otp']) {
            // OTP verified, clear OTP session and redirect to admin dashboard
            unset($_SESSION['admin_otp']);
            unset($_SESSION['admin_otp_expiry']);
            unset($_SESSION['pending_admin_auth']);
            
            header("Location: /intrack-cathy/admin-page/admin/dashboard.php");
            exit();
        } else {
            $error = "Invalid OTP. Please try again.";
        }
    }
    
    // Handle resend OTP
    if (isset($_POST['resend'])) {
        require_once './database/dbconnection.php';
        
        // Generate new OTP
        $otp = rand(100000, 999999);
        $otp_expiry = time() + 300; // 5 minutes
        
        // Update session
        $_SESSION['admin_otp'] = $otp;
        $_SESSION['admin_otp_expiry'] = $otp_expiry;
        
        // Send new OTP
        require_once './includes/send_email.php';
        $subject = "New Admin Login Verification OTP";
        $message = "Your new admin login verification OTP is: $otp\n\nThis OTP is valid for 5 minutes.";
        
        if (sendEmail($_SESSION['email'], $subject, $message)) {
            $success = "A new OTP has been sent to your email.";
        } else {
            $error = "Failed to send new OTP. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin OTP Verification</title>
    <link rel="stylesheet" href="styles/otp-confirmation.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .error-message, .success-message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }
        .error-message {
            color: red;
            background: #ffeeee;
            border: 1px solid #ffcccc;
        }
        .success-message {
            color: green;
            background: #eeffee;
            border: 1px solid #ccffcc;
        }
        .admin-notice {
            text-align: center;
            color: #d9534f;
            font-weight: bold;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <section class="otp">
        <div class="container">
            <div class="otp-wrapper">
                <div class="otp-form">
                    <div class="otp-form-wrapper">
                        <div class="otp-form-header">
                            <h1>Admin Security Verification</h1>
                            <div class="admin-notice">Administrator login requires additional verification</div>
                            <label for="verification">Enter the 6-digit code sent to <?php echo htmlspecialchars($_SESSION['email']); ?></label>
                            <?php if(isset($error)): ?>
                                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>
                            <?php if(isset($success)): ?>
                                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
                            <?php endif; ?>
                        </div>
                        <form action="" method="POST" class="otp-form-content" id="otpForm">
                            <div class="form-groups">
                                <input type="text" id="otp1" name="otp1" maxlength="1" pattern="[0-9]" inputmode="numeric" autofocus required>
                                <input type="text" id="otp2" name="otp2" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                                <input type="text" id="otp3" name="otp3" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                                <input type="text" id="otp4" name="otp4" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                                <input type="text" id="otp5" name="otp5" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                                <input type="text" id="otp6" name="otp6" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                            </div>
                            <button class="confirm" type="submit">Verify</button>
                            <div class="resend-otp">
                                <span class="timer" id="timer">02:00</span>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="resend" value="1">
                                    <button type="submit" class="resend-button" style="background: none; border: none; color: #0066cc; cursor: pointer; padding: 0;">Resend OTP</button>
                                </form>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <script>
        // Timer functionality
        function startTimer(duration, display) {
            var timer = duration, minutes, seconds;
            var interval = setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = minutes + ":" + seconds;

                if (--timer < 0) {
                    clearInterval(interval);
                    display.textContent = "00:00";
                    document.querySelector('.resend-button').style.color = '#0066cc';
                    document.querySelector('.resend-button').style.cursor = 'pointer';
                }
            }, 1000);
        }

        window.onload = function () {
            var fiveMinutes = 60 * 2, // 2 minutes
                display = document.querySelector('#timer');
            startTimer(fiveMinutes, display);
        };

        // Auto-focus next input field
        document.getElementById('otpForm').addEventListener('input', function (e) {
            if (e.target.tagName === 'INPUT' && e.target.value.length === 1) {
                const inputs = Array.from(document.querySelectorAll('input[type="text"]'));
                const index = inputs.indexOf(e.target);
                
                if (index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            }
        });
    </script>
</body>
</html>