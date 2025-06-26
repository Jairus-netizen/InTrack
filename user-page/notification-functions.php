<?php
require_once 'database/dbconnection.php';

function createNotification($userId, $title, $message, $type = 'general', $relatedId = null) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO notifications 
                          (user_id, title, message, type, is_read, archived, created_at, updated_at, related_id) 
                          VALUES (?, ?, ?, ?, 0, 0, NOW(), NOW(), ?)");
    $stmt->bind_param("isssi", $userId, $title, $message, $type, $relatedId);
    return $stmt->execute();
}

function sendWelcomeNotification($userId, $firstName) {
    $title = "Welcome to Intrack!";
    $message = "Hi $firstName, welcome to Intrack! We're excited to have you on board. "
             . "Get started by completing your profile and exploring the features.";
    return createNotification($userId, $title, $message, 'welcome');
}

function sendPolicyNotification($userId) {
    $title = "Important: Privacy Policy and Terms";
    $message = "Please review our Privacy Policy and Terms of Service. "
             . "By continuing to use Intrack, you agree to these terms.";
    return createNotification($userId, $title, $message, 'policy_terms');
}
function checkProjectDueDates($conn) {
    // Get projects due in the next 3 days or overdue
    $query = "SELECT p.id, p.user_id, p.project_title, p.expected_completion, 
                     a.first_name, a.last_name 
              FROM projects p
              JOIN accounts a ON p.user_id = a.account_id
              WHERE p.status = 'in_progress'
              AND p.expected_completion <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)";
    
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        $dueDate = new DateTime($row['expected_completion']);
        $today = new DateTime();
        $diff = $today->diff($dueDate);
        
        // Check if notification already exists for this project
        $checkQuery = "SELECT id FROM notifications 
                      WHERE user_id = ? 
                      AND type = 'project_due' 
                      AND related_id = ?
                      AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
                      LIMIT 1";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("ii", $row['user_id'], $row['id']);
        $checkStmt->execute();
        $exists = $checkStmt->get_result()->fetch_assoc();
        
        if ($exists) {
            continue; // Skip if notification already exists from last 24 hours
        }
        
        if ($diff->invert) {
            // Project is overdue
            $title = "Project Overdue: {$row['project_title']}";
            $message = "Your project '{$row['project_title']}' is overdue. Please update its status.";
        } else {
            // Project due soon
            $days = $diff->days;
            $title = "Project Due Soon: {$row['project_title']}";
            $message = "Your project '{$row['project_title']}' is due in $days days.";
        }
        
        createNotification(
            $row['user_id'],
            $title,
            $message,
            'project_due',
            $row['id']
        );
    }
}

function sendNewProjectNotification($userId, $projectTitle, $projectId) {
    $title = "New Project Created: $projectTitle";
    $message = "Your project '$projectTitle' has been successfully created. Click to view details.";
    return createNotification($userId, $title, $message, 'project_due', $projectId);
}
?>