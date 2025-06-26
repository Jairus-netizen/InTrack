<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Archive</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="styles/archive.css">
</head>
<body>
    <?php include 'sidebar.php'?>
    <!-- Main Content -->
    <div class="container">
        <div class="header">
            <h1>Archived Notifications</h1>
            <a href="notif.php" class="back-btn">
                <i class='bx bx-arrow-back'></i> Back to Notifications
            </a>
        </div>
        
        <div class="notification-list">
            <!-- Sample Archived Notification 1 -->
            <div class="notification-card">
                <div class="notification-header">
                    <div class="notification-title">Weekly Team Meeting</div>
                    <div class="notification-date">May 10, 2023 - 11:20 AM</div>
                </div>
                <div class="notification-body">
                    Reminder: Weekly team meeting today at 2:00 PM in Conference Room B. Please bring your project updates.
                </div>
                <div class="notification-actions">
                    <button class="action-btn restore-btn">
                        <i class='bx bx-undo'></i> Restore
                    </button>
                    <button class="action-btn delete-btn">
                        <i class='bx bx-trash'></i> Delete
                    </button>
                </div>
            </div>
            
            <!-- Sample Archived Notification 2 -->
            <div class="notification-card">
                <div class="notification-header">
                    <div class="notification-title">Holiday Schedule</div>
                    <div class="notification-date">May 5, 2023 - 4:30 PM</div>
                </div>
                <div class="notification-body">
                    The office will be closed on Monday, May 29th for Memorial Day. Please plan your work accordingly.
                </div>
                <div class="notification-actions">
                    <button class="action-btn restore-btn">
                        <i class='bx bx-undo'></i> Restore
                    </button>
                    <button class="action-btn delete-btn">
                        <i class='bx bx-trash'></i> Delete
                    </button>
                </div>
            </div>
            
            <!-- Sample Archived Notification 3 -->
            <div class="notification-card">
                <div class="notification-header">
                    <div class="notification-title">System Update</div>
                    <div class="notification-date">Apr 28, 2023 - 9:15 AM</div>
                </div>
                <div class="notification-body">
                    We've updated the time tracking system with new features. Check out the release notes in the help section.
                </div>
                <div class="notification-actions">
                    <button class="action-btn restore-btn">
                        <i class='bx bx-undo'></i> Restore
                    </button>
                    <button class="action-btn delete-btn">
                        <i class='bx bx-trash'></i> Delete
                    </button>
                </div>
            </div>
            
            <!-- Empty State (hidden by default) -->
            <div class="empty-state" style="display: none;">
                <i class='bx bx-package'></i>
                <p>No archived notifications</p>
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
<script src="scripts/archive.js"></script>
</body>
</html>