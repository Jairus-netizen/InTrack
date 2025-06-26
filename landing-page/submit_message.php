<?php
// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'C:/xampp/htdocs/intrack-cathy/landing-page/PHPMailer-master/src/Exception.php';
require 'C:/xampp/htdocs/intrack-cathy/landing-page/PHPMailer-master/src/PHPMailer.php';
require 'C:/xampp/htdocs/intrack-cathy/landing-page/PHPMailer-master/src/SMTP.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';

    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'plspfernandez@gmail.com'; // Admin's Gmail address
        $mail->Password   = 'uezf ebho kgib swnt';     // Admin's App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom($email, $first_name . ' ' . $last_name);
        $mail->addAddress('plspfernandez@gmail.com', 'Admin'); // Send to admin

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New Contact Form Submission from ' . $first_name . ' ' . $last_name;
        
        $email_body = "
            <h2>You have received a new message from the contact form</h2>
            <p><strong>Name:</strong> {$first_name} {$last_name}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Message:</strong></p>
            <p>{$message}</p>
        ";
        
        $mail->Body = $email_body;
        $mail->AltBody = strip_tags($email_body);

        $mail->send();
        
        // Redirect back to contact page with success message
        header('Location: contact.php?status=success');
        exit();
    } catch (Exception $e) {
        // Redirect back to contact page with error message
        header('Location: contact.php?status=error&message=' . urlencode($mail->ErrorInfo));
        exit();
    }
} else {
    // If not a POST request, redirect to contact page
    header('Location: contact.php');
    exit();
}
?>