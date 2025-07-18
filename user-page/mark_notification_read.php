<?php
session_start();
require_once 'database/dbconnection.php';

if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notificationId = intval($_POST['notification_id'] ?? 0);
    $userId = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notificationId, $userId);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
    exit();
}

header("HTTP/1.1 400 Bad Request");
exit();
?>