<?php
session_start();
$pageStyles = '<link rel="stylesheet" href="../styles/all-notification.css">';
include '../includes/header.php';
?>
<!-- MAIN -->
<main>
    <div class="head-title">
        <div class="left">
            <h1>All Notifications</h1>
            <ul class="breadcrumb">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><i class="bx bx-chevron-right"></i></li>
                <li><a class="active" href="#">All Notifications</a></li>
            </ul>
        </div>
    </div>

    <div class="all-notifications-container">
        <div class="all-notifications-header">
            <h2>Notifications</h2>
            <div class="all-notification-actions">
                <a href="#" class="mark-all-read">Mark all as read</a>
            </div>
        </div>
        
        <div class="all-notifications-list">
            <!-- Today's Notifications -->
            <div class="all-notification-day-group">
                <h3 class="all-notification-day">Today</h3>
                <div class="all-notification-item unread">
                    <div class="all-notification-icon">
                        <i class='bx bxs-user-plus'></i>
                    </div>
                    <div class="all-notification-content">
                        <p><strong>John Doe</strong> sent you a connection request</p>
                        <span class="notification-time">2 mins ago</span>
                    </div>
                </div>
                <div class="all-notification-item unread">
                    <div class="all-notification-icon">
                        <i class='bx bxs-message-alt'></i>
                    </div>
                    <div class="all-notification-content">
                        <p><strong>Sarah Smith</strong> sent you a message</p>
                        <span class="all-notification-time">15 mins ago</span>
                    </div>
                </div>
            </div>
            
            <!-- Yesterday's Notifications -->
            <div class="all-notification-day-group">
                <h3 class="all-notification-day">Yesterday</h3>
                <div class="all-notification-item">
                    <div class="all-notification-icon">
                        <i class='bx bxs-badge-check'></i>
                    </div>
                    <div class="all-notification-content">
                        <p>Your job posting for <strong>Web Developer</strong> was approved</p>
                        <span class="all-notification-time">1 day ago</span>
                    </div>
                </div>
            </div>
            
            <!-- Older Notifications -->
            <div class="all-notification-day-group">
                <h3 class="all-notification-day">This Week</h3>
                <div class="all-notification-item">
                    <div class="all-notification-icon">
                        <i class='bx bxs-group'></i>
                    </div>
                    <div class="all-notification-content">
                        <p><strong>5 new applicants</strong> for your job posting</p>
                        <span class="all-notification-time">3 days ago</span>
                    </div>
                </div>
                <div class="all-notification-item">
                    <div class="all-notification-icon">
                        <i class='bx bxs-calendar'></i>
                    </div>
                    <div class="all-notification-content">
                        <p>Interview scheduled with <strong>Tech Solutions Inc.</strong></p>
                        <span class="all-notification-time">4 days ago</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>