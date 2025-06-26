<?php
require_once 'database/dbconnection.php';
require_once 'notification-functions.php';

// Check for due projects
checkProjectDueDates($conn);

// Clean up old archived notifications (older than 30 days)
$cleanupDays = 30;
$stmt = $conn->prepare("DELETE n FROM notifications n
                       JOIN archive_notifications a ON n.id = a.notification_id
                       WHERE n.archived = 1
                       AND a.archived_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
$stmt->bind_param("i", $cleanupDays);
$stmt->execute();

echo "Notification checks completed at " . date('Y-m-d H:i:s');
?>