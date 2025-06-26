<?php
session_start();
require_once 'database/dbconnection.php';

if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0 AND archived = 0");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$count = $result->fetch_assoc()['count'];

echo json_encode(['count' => $count]);
exit();
?>