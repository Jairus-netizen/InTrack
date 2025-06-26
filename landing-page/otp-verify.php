<?php
session_start();
require_once './database/dbconnection.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Combine OTP digits
    $otp = $_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . 
            $_POST['otp4'] . $_POST['otp5'] . $_POST['otp6'];
    
    // Validate OTP format
    if (strlen($otp) !== 6 || !ctype_digit($otp)) {
        $error = "Invalid OTP format";
    } else {
        // Check against stored OTP (in session or database)
        $stmt = $conn->prepare("SELECT user_id, otp_expiry FROM otp_verifications 
                               WHERE user_id = ? AND otp_code = ? AND otp_expiry > NOW()");
        $stmt->bind_param("is", $_SESSION['temp_user_id'], $otp);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            // OTP is valid
            // Clear used OTP
            $clearStmt = $conn->prepare("DELETE FROM otp_verifications WHERE user_id = ?");
            $clearStmt->bind_param("i", $_SESSION['temp_user_id']);
            $clearStmt->execute();
            
            // Proceed with verification success (e.g., password reset or account activation)
            header("Location: new-password.php");
            exit();
        } else {
            $error = "Invalid or expired OTP";
        }
    }
}

// Include your HTML template here
?>