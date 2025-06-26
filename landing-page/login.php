<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Back</title>
    <link rel="stylesheet" href="styles/loginuser.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script src="./scripts/login.js"></script>
    <style>
        body.swal2-height-auto {
            height: 100vh !important;
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
            padding: 10px;
            background: #ffeeee;
            border: 1px solid #ffcccc;
            border-radius: 4px;
        }

        /* Add some responsive styles */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }

            .login-image {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-image">
            <img src="styles/images/user.jpg" alt="People collaborating">
        </div>

        <div class="login-form-container">
            <div class="login-form-header">
                <div class="form-logo">
                    <img src="styles/images/logo.png" alt="Company Logo">
                </div>

                <h1 class="form-title">Welcome Back!</h1>
            </div>

            <?php
            session_start();
            
            // Check for remember token cookie if not already logged in
            if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
                require_once './database/dbconnection.php';
                
                $stmt = $conn->prepare("SELECT account_id, email, phone, account_type, first_name, last_name 
                                      FROM accounts 
                                      WHERE remember_token = ? 
                                      AND remember_token_expiry > NOW() 
                                      AND is_active = TRUE");
                $stmt->bind_param("s", $_COOKIE['remember_token']);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    
                    // Set session variables
                    $_SESSION['user_id'] = $user['account_id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['phone'] = $user['phone'];
                    $_SESSION['account_type'] = $user['account_type'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];
                    
                    // Redirect based on account type
                    if ($user['account_type'] === 'admin') {
                        header("Location: /intrack-cathy/admin-page/admin/dashboard.php");
                        exit();
                    } else {
                        header("Location: /intrack-cathy/user-page/dashboard.php");
                        exit();
                    }
                }
            }

            if (isset($_GET['password_reset']) && $_GET['password_reset'] === 'success') {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "Password Reset",
                        text: "Your password has been reset successfully. Please login with your new password.",
                    });
                </script>';
            }

            // Prevent caching of this page
            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                require_once './database/dbconnection.php';

                $login = $_POST['login'] ?? '';
                $password = $_POST['password'] ?? '';
                $remember = $_POST['remember'] ?? false;

                if (!empty($login) && !empty($password)) {
                    $stmt = $conn->prepare("SELECT account_id, email, phone, password, account_type, first_name, last_name
                                          FROM accounts 
                                          WHERE (email = ? OR phone = ?) AND is_active = TRUE");
                    $stmt->bind_param("ss", $login, $login);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows === 1) {
                        $user = $result->fetch_assoc();

                        if (password_verify($password, $user['password'])) {
                            // Regenerate session ID for security
                            session_regenerate_id(true);

                            // Set all session variables
                            $_SESSION = array();
                            $_SESSION['user_id'] = $user['account_id'];
                            $_SESSION['email'] = $user['email'];
                            $_SESSION['phone'] = $user['phone'];
                            $_SESSION['account_type'] = $user['account_type'];
                            $_SESSION['first_name'] = $user['first_name'];
                            $_SESSION['last_name'] = $user['last_name'];

                            // Handle "Remember Me" functionality
                            if ($remember) {
                                $token = bin2hex(random_bytes(32));
                                $expiry = time() + 60 * 60 * 24 * 30; // 30 days
                                
                                // Set secure cookie
                                setcookie('remember_token', $token, [
                                    'expires' => $expiry,
                                    'path' => '/',
                                    'secure' => true, // Only send over HTTPS
                                    'httponly' => true, // Not accessible via JavaScript
                                    'samesite' => 'Strict' // Prevent CSRF
                                ]);

                                // Update database with token and expiry
                                $updateStmt = $conn->prepare("UPDATE accounts SET remember_token = ?, remember_token_expiry = FROM_UNIXTIME(?) WHERE account_id = ?");
                                $updateStmt->bind_param("ssi", $token, $expiry, $user['account_id']);
                                $updateStmt->execute();
                                $updateStmt->close();
                            } else {
                                // Clear any existing remember token if "Remember Me" is not checked
                                $updateStmt = $conn->prepare("UPDATE accounts SET remember_token = NULL, remember_token_expiry = NULL WHERE account_id = ?");
                                $updateStmt->bind_param("i", $user['account_id']);
                                $updateStmt->execute();
                                $updateStmt->close();
                                
                                // Clear the cookie if it exists
                                if (isset($_COOKIE['remember_token'])) {
                                    setcookie('remember_token', '', time() - 3600, '/');
                                }
                            }

                            // For admin users, generate and send OTP
                            if ($user['account_type'] === 'admin') {
                                // Generate 6-digit OTP
                                $otp = rand(100000, 999999);
                                $otp_expiry = time() + 300; // OTP valid for 5 minutes

                                // Store OTP in session
                                $_SESSION['admin_otp'] = $otp;
                                $_SESSION['admin_otp_expiry'] = $otp_expiry;
                                $_SESSION['pending_admin_auth'] = true;

                                // Send OTP via email
                                require_once 'C:/xampp/htdocs/intrack-cathy/landing-page/send_email.php';
                                $subject = "Admin Login Verification OTP";
                                $message = "Your admin login verification OTP is: $otp\n\nThis OTP is valid for 5 minutes.";

                                if (sendEmail($user['email'], $subject, $message)) {
                                    header("Location: otp-confirmation-admin.php");
                                    exit();
                                } else {
                                    echo '<script>
                                        Swal.fire({
                                            icon: "error",
                                            title: "Error",
                                            text: "Failed to send OTP. Please try again.",
                                        });
                                    </script>';
                                }
                            } else {
                                // Regular user - redirect to dashboard
                                header("Location: /intrack-cathy/user-page/dashboard.php");
                                exit();
                            }
                        } else {
                            echo '<script>
                                Swal.fire({
                                    icon: "error",
                                    title: "Login Failed",
                                    text: "Invalid login credentials",
                                });
                            </script>';
                        }
                    } else {
                        echo '<script>
                            Swal.fire({
                                icon: "error",
                                title: "Login Failed",
                                text: "No account found with this email/phone",
                            });
                        </script>';
                    }

                    $stmt->close();
                    $conn->close();
                } else {
                    echo '<script>
                        Swal.fire({
                            icon: "error",
                            title: "Missing Fields",
                            text: "Please fill in all fields",
                        });
                    </script>';
                }
            }
            ?>

            <form class="login-form" method="POST" action="">
                <div class="form-group">
                    <label for="login">Email / Phone No.</label>
                    <input type="text" id="login" name="login" required
                        value="<?php echo isset($_POST['login']) ? htmlspecialchars($_POST['login']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="pass-div">
                        <input type="password" id="password" name="password" required>
                        <i class="fa-solid fa-eye-slash eye-icon" id="togglePassword" onclick="togglePassword()"></i>
                    </div>
                </div>

                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Keep me signed in</label>
                </div>

                <button type="submit" class="login-button">Log In</button>

                <div class="signup-link">
                    Don't have an account? <a href="./sign.php">Sign Up</a>
                </div>
                <div class="forgot-password">
                    <a href="forgot-password.php">Forgot password?</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>