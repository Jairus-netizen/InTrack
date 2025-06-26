<?php
session_start();

// Redirect if no email in session
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot-password.php");
    exit();
}

// Check if OTP is expired
if (isset($_SESSION['reset_otp_expiry']) && time() > $_SESSION['reset_otp_expiry']) {
    unset($_SESSION['reset_otp']);
    unset($_SESSION['reset_otp_expiry']);
    $error = "OTP has expired. Please request a new one.";
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle OTP verification
    if (isset($_POST['otp1'], $_POST['otp2'], $_POST['otp3'], $_POST['otp4'], $_POST['otp5'], $_POST['otp6'])) {
        $user_otp = $_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . $_POST['otp4'] . $_POST['otp5'] . $_POST['otp6'];
        
        if ($user_otp == $_SESSION['reset_otp']) {
            // OTP verified, redirect to reset password
            $_SESSION['otp_verified'] = true;
            header("Location: reset-password.php");
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
        $_SESSION['reset_otp'] = $otp;
        $_SESSION['reset_otp_expiry'] = $otp_expiry;
        
        // Send new OTP
        require_once 'send_email.php';
        $subject = "New Password Reset OTP";
        $message = "Your new OTP for password reset is: $otp\n\nThis OTP is valid for 5 minutes.";
        
        if (sendEmail($_SESSION['reset_email'], $subject, $message)) {
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
    <title>OTP Verification</title>
    <link rel="stylesheet" href="styles/otp-confirmation.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="./scripts/otp-confirmation.js"></script>
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
        .success-message {
            color: green;
            margin-bottom: 15px;
            padding: 10px;
            background: #eeffee;
            border: 1px solid #ccffcc;
            border-radius: 4px;
            text-align: center;
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
                            <h1>OTP Verification</h1>
                            <label for="verification">Enter the 6-digit code sent to <?php echo htmlspecialchars($_SESSION['reset_email']); ?></label>
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