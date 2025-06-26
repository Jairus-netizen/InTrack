<?php
session_start();
require_once 'database/dbconnection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Check if project ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['success' => false, 'message' => 'Project ID is required']);
    exit();
}

$projectId = $_GET['id'];

// Get the expected completion date
$query = "SELECT expected_completion FROM projects WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $projectId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($projectData = mysqli_fetch_assoc($result)) {
    echo json_encode([
        'success' => true,
        'expected_completion' => $projectData['expected_completion']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Project not found']);
}
?>