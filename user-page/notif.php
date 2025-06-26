<?php
session_start();
require_once 'database/dbconnection.php';
require_once 'notification-functions.php';

// At the beginning of notif.php, after session_start()
if (!isset($_SESSION['last_notif_check'])) {
    // Check for due dates only if not checked recently
    require_once 'notification-functions.php';
    checkProjectDueDates($conn);
    $_SESSION['last_notif_check'] = time();
} elseif (time() - $_SESSION['last_notif_check'] > 3600) {
    // If more than 1 hour has passed, check again
    require_once 'notification-functions.php';
    checkProjectDueDates($conn);
    $_SESSION['last_notif_check'] = time();
}

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header("Location: login.php");
    exit();
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $notificationId = intval($_POST['notification_id']);
        
        switch ($_POST['action']) {
            case 'archive':
                // First get the notification
                $stmt = $conn->prepare("SELECT * FROM notifications WHERE id = ? AND user_id = ?");
                $stmt->bind_param("ii", $notificationId, $userId);
                $stmt->execute();
                $notification = $stmt->get_result()->fetch_assoc();
                
                if ($notification) {
                    // Insert into archive_notifications
                    $stmt = $conn->prepare("INSERT INTO archive_notifications 
                                          (notification_id, account_id, archived_at) 
                                          VALUES (?, ?, NOW())");
                    $stmt->bind_param("ii", $notificationId, $userId);
                    $stmt->execute();
                    
                    // Mark as archived in notifications table
                    $stmt = $conn->prepare("UPDATE notifications SET archived = 1 WHERE id = ?");
                    $stmt->bind_param("i", $notificationId);
                    $stmt->execute();
                }
                break;
                
            case 'restore':
                // Check if exists in archive
                $stmt = $conn->prepare("SELECT * FROM archive_notifications 
                                      WHERE notification_id = ? AND account_id = ?");
                $stmt->bind_param("ii", $notificationId, $userId);
                $stmt->execute();
                $archived = $stmt->get_result()->fetch_assoc();
                
                if ($archived) {
                    // Remove from archive
                    $stmt = $conn->prepare("DELETE FROM archive_notifications 
                                          WHERE notification_id = ? AND account_id = ?");
                    $stmt->bind_param("ii", $notificationId, $userId);
                    $stmt->execute();
                    
                    // Mark as not archived
                    $stmt = $conn->prepare("UPDATE notifications SET archived = 0 WHERE id = ?");
                    $stmt->bind_param("i", $notificationId);
                    $stmt->execute();
                }
                break;
                
            case 'delete':
                // First delete from archive_notifications if exists
                $stmt = $conn->prepare("DELETE FROM archive_notifications 
                                      WHERE notification_id = ? AND account_id = ?");
                $stmt->bind_param("ii", $notificationId, $userId);
                $stmt->execute();
                
                // Then delete from notifications
                $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
                $stmt->bind_param("ii", $notificationId, $userId);
                $stmt->execute();
                break;
                
            case 'mark_read':
                $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
                $stmt->bind_param("ii", $notificationId, $userId);
                $stmt->execute();
                break;
        }
        
        header("Location: notif.php");
        exit();
    }
}

// Get active notifications
$activeNotifications = [];
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND archived = 0 ORDER BY created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $activeNotifications[] = $row;
}

// Get archived notifications
$archivedNotifications = [];
$stmt = $conn->prepare("SELECT n.* FROM notifications n
                       JOIN archive_notifications a ON n.id = a.notification_id
                       WHERE n.user_id = ? AND n.archived = 1
                       ORDER BY a.archived_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $archivedNotifications[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | Intrack</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="styles/notif.css">
</head>
<body>
    <?php include 'sidebar.php'?>

    <!-- Main Content -->
    <div class="main-content">
        <div id="dashboard" class="page-content active">
            <div class="header">
                <h1>Notifications</h1>
                <div class="user-info">
                    <h4>Hello!</h4>
                    <span><?php echo htmlspecialchars($_SESSION['first_name']); ?></span>
                </div>
            </div>
        
            <div class="tabs">
                <div class="tab active" data-tab="active">Active Notifications</div>
                <div class="tab" data-tab="archived">Archived</div>
            </div>
            
            <!-- Active Notifications Tab -->
            <div id="active-tab" class="tab-content">
                <?php if (empty($activeNotifications)): ?>
                    <div class="empty-state">
                        <i class='bx bx-package'></i>
                        <p>No active notifications</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($activeNotifications as $notification): ?>
                        <div class="notification-card" data-id="<?php echo $notification['id']; ?>">
                            <div class="notification-header">
                                <div class="notification-type-icon">
                                    <?php switch($notification['type']): 
                                        case 'welcome': ?>
                                            <i class='bx bx-party'></i>
                                        <?php break; ?>
                                        <?php case 'project_due': ?>
                                            <i class='bx bx-calendar-exclamation'></i>
                                        <?php break; ?>
                                        <?php case 'policy_terms': ?>
                                            <i class='bx bx-shield-alt'></i>
                                        <?php break; ?>
                                        <?php default: ?>
                                            <i class='bx bx-bell'></i>
                                    <?php endswitch; ?>
                                </div>
                                <div class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></div>
                                <div class="notification-date">
                                    <?php echo date('M j, Y - g:i A', strtotime($notification['created_at'])); ?>
                                    <?php if (!$notification['is_read']): ?>
                                        <span class="unread-badge">New</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="notification-body">
                                <?php echo htmlspecialchars($notification['message']); ?>
                            </div>
                            <div class="notification-actions">
                                <?php if ($notification['type'] === 'project_due' && $notification['related_id']): ?>
                                    <a href="view-project.php?id=<?php echo $notification['related_id']; ?>" class="action-btn view-btn">
                                        <i class='bx bx-show'></i> View Project
                                    </a>
                                <?php endif; ?>
                                <form method="POST" class="notification-form">
                                    <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                    <input type="hidden" name="action" value="archive">
                                    <button type="submit" class="action-btn archive-btn">
                                        <i class='bx bx-archive'></i> Archive
                                    </button>
                                </form>
                                <form method="POST" class="notification-form">
                                    <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                    <input type="hidden" name="action" value="mark_read">
                                    <button type="submit" class="action-btn read-btn">
                                        <i class='bx bx-check'></i> Mark Read
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Archived Notifications Tab -->
            <div id="archived-tab" class="tab-content" style="display: none;">
                <?php if (empty($archivedNotifications)): ?>
                    <div class="empty-state">
                        <i class='bx bx-package'></i>
                        <p>No archived notifications</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($archivedNotifications as $notification): ?>
                        <div class="notification-card" data-id="<?php echo $notification['id']; ?>">
                            <div class="notification-header">
                                <div class="notification-type-icon">
                                    <?php switch($notification['type']): 
                                        case 'welcome': ?>
                                            <i class='bx bx-party'></i>
                                        <?php break; ?>
                                        <?php case 'project_due': ?>
                                            <i class='bx bx-calendar-exclamation'></i>
                                        <?php break; ?>
                                        <?php case 'policy_terms': ?>
                                            <i class='bx bx-shield-alt'></i>
                                        <?php break; ?>
                                        <?php default: ?>
                                            <i class='bx bx-bell'></i>
                                    <?php endswitch; ?>
                                </div>
                                <div class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></div>
                                <div class="notification-date">
                                    <?php echo date('M j, Y - g:i A', strtotime($notification['created_at'])); ?>
                                </div>
                            </div>
                            <div class="notification-body">
                                <?php echo htmlspecialchars($notification['message']); ?>
                            </div>
                            <div class="notification-actions">
                                <form method="POST" class="notification-form">
                                    <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                    <input type="hidden" name="action" value="restore">
                                    <button type="submit" class="action-btn restore-btn">
                                        <i class='bx bx-undo'></i> Restore
                                    </button>
                                </form>
                                <form method="POST" class="notification-form delete-form">
                                    <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="button" class="action-btn delete-btn">
                                        <i class='bx bx-trash'></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3 class="modal-title">Confirm Deletion</h3>
            <p>Are you sure you want to permanently delete this notification? This action cannot be undone.</p>
            <div class="modal-actions">
                <button class="modal-btn cancel-btn" id="cancelDelete">Cancel</button>
                <button class="modal-btn confirm-btn" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
    
    <script src="scripts/notif.js"></script>
</body>
</html>