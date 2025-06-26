<?php
// Function to send email using SMTP
function sendEmail($to, $subject, $message) {
    // Include PHPMailer library with correct absolute paths
    require_once 'C:/xampp/htdocs/intrack-cathy/landing-page/PHPMailer-master/src/Exception.php';
    require_once 'C:/xampp/htdocs/intrack-cathy/landing-page/PHPMailer-master/src/PHPMailer.php';
    require_once 'C:/xampp/htdocs/intrack-cathy/landing-page/PHPMailer-master/src/SMTP.php';
    
    // Initialize PHPMailer using full namespace
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'plspfernandez@gmail.com';
        $mail->Password = 'uezf ebho kgib swnt';
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('plspfernandez@gmail.com', 'InTrack');
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $message;
        
        return $mail->send();
    } catch (\Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>