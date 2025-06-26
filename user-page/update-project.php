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
if (!isset($_POST['project_id']) || empty($_POST['project_id'])) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['success' => false, 'message' => 'Project ID is required']);
    exit();
}

$projectId = $_POST['project_id'];

// Prepare update query
$query = "UPDATE projects SET 
          project_title = ?,
          arrange_by = ?,
          project_manager = ?,
          expected_completion = ?,
          project_progress = ?,
          team_lead = ?,
          department = ?,
          availability = ?";

$params = [
    $_POST['project_title'] ?? '',
    $_POST['arrange_by'] ?? '',
    $_POST['project_manager'] ?? '',
    $_POST['expected_completion'] ?? '',
    $_POST['project_progress'] ?? '',
    $_POST['team_lead'] ?? '',
    $_POST['department'] ?? '',
    $_POST['availability'] ?? ''
];

// Handle image upload if present
if (!empty($_FILES['project_image']['name'])) {
    $targetDir = "project-images/";
    $fileName = basename($_FILES["project_image"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    
    // Allow certain file formats
    $allowTypes = array('jpg','png','jpeg','gif');
    if (in_array($fileType, $allowTypes)) {
        // Upload file to server
        if (move_uploaded_file($_FILES["project_image"]["tmp_name"], $targetFilePath)) {
            $query .= ", images = ?";
            $params[] = $targetFilePath;
        }
    }
}

$query .= " WHERE id = ?";
$params[] = $projectId;

// Prepare and execute the statement
$stmt = mysqli_prepare($conn, $query);
$types = str_repeat('s', count($params));
mysqli_stmt_bind_param($stmt, $types, ...$params);
$success = mysqli_stmt_execute($stmt);

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed']);
}
?>