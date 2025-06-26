<?php
// Start session and check authentication
session_start();
require_once 'database/dbconnection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /intrack-cathy/landing-page/login.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 AND archived = 0 ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);

function timeAgo($datetime)
{
    $time = strtotime($datetime);
    $current = time();
    $seconds = $current - $time;
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    $weeks = round($seconds / 604800);
    $months = round($seconds / 2629440);
    $years = round($seconds / 31553280);

    if ($seconds <= 60) {
        return "just now";
    } elseif ($minutes <= 60) {
        return $minutes == 1 ? "1 minute ago" : "$minutes minutes ago";
    } elseif ($hours <= 24) {
        return $hours == 1 ? "1 hour ago" : "$hours hours ago";
    } elseif ($days <= 7) {
        return $days == 1 ? "yesterday" : "$days days ago";
    } elseif ($weeks <= 4.3) {  // 4.3 weeks = 1 month
        return $weeks == 1 ? "1 week ago" : "$weeks weeks ago";
    } elseif ($months <= 12) {
        return $months == 1 ? "1 month ago" : "$months months ago";
    } else {
        return $years == 1 ? "1 year ago" : "$years years ago";
    }
}

// Get time stats using your function
function getTimeStats($userId, $conn)
{
    // Today's time
    $stmt = $conn->prepare("SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(clock_out, clock_in)))) AS total 
                           FROM time_entries 
                           WHERE user_id = ? AND date = CURDATE() AND clock_out IS NOT NULL");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $today = $result->fetch_assoc();

    // Week's time
    $stmt = $conn->prepare("SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(clock_out, clock_in)))) AS total 
                           FROM time_entries 
                           WHERE user_id = ? AND YEARWEEK(date, 1) = YEARWEEK(CURDATE(), 1) AND clock_out IS NOT NULL");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $week = $result->fetch_assoc();

    // Month's time
    $stmt = $conn->prepare("SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(clock_out, clock_in)))) AS total 
                           FROM time_entries 
                           WHERE user_id = ? AND YEAR(date) = YEAR(CURDATE()) AND MONTH(date) = MONTH(CURDATE()) AND clock_out IS NOT NULL");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $month = $result->fetch_assoc();

    return [
        'today' => $today['total'] ?: '0h 0m',
        'week' => $week['total'] ?: '0h 0m',
        'month' => $month['total'] ?: '0h 0m'
    ];
}

// Call the function to get time stats
$timeStats = getTimeStats($_SESSION['user_id'], $conn);

// Get current clock status
$clockedIn = false;
$currentEntry = null;

$stmt = $conn->prepare("SELECT * FROM time_entries 
                       WHERE user_id = ? AND date = CURDATE() 
                       ORDER BY clock_in DESC LIMIT 1");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$currentEntry = $result->fetch_assoc();

if ($currentEntry && !$currentEntry['clock_out']) {
    $clockedIn = true;
}

// Get the 3 most recent active projects
$stmt = $conn->prepare("SELECT * FROM projects 
                      WHERE user_id = ? AND status IN ('in_progress', 'over_due')
                      ORDER BY id DESC LIMIT 3");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$projects = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intrack | Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="styles/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <!-- Sidebar Navigation -->
    <?php include 'sidebar.php' ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard Page (default active) -->
        <div id="dashboard" class="page-content active">
            <div class="header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <div class="notification-bell">
                        <i class='bx bx-bell'></i>
                        <?php if (count($notifications) > 0): ?>
                            <span class="notification-badge"><?php echo count($notifications); ?></span>
                        <?php endif; ?>
                        <div class="notification-dropdown">
                            <?php foreach (array_slice($notifications, 0, 3) as $notif): ?>
                                <div class="notification-item" data-id="<?php echo $notif['id']; ?>">
                                    <h4><?php echo htmlspecialchars($notif['title']); ?></h4>
                                    <p><?php echo htmlspecialchars(substr($notif['message'], 0, 50)); ?>...</p>
                                    <small><?php echo timeAgo($notif['created_at']); ?></small>
                                </div>
                            <?php endforeach; ?>
                            <a href="notif.php" class="view-all">View All Notifications</a>
                        </div>
                    </div>
                    <h4>Hello!</h4>
                    <span><?php echo htmlspecialchars($_SESSION['first_name']); ?></span>
                </div>
            </div>

            <!-- Time Tracking Preview -->
            <div class="preview-section">
                <div class="section-title">
                    <h2><i class='bx bx-time'></i> Time Tracking - <?php echo date('F j, Y'); ?></h2>
                    <a href="time.php" class="view-all">View All</a>
                </div>
                <div class="cards-container">
                    <div class="card time-card">
                        <h3>Today's Activity</h3>
                        <div class="status">
                            <div class="status-indicator <?php echo $clockedIn ? 'clocked-in' : 'clocked-out'; ?>"></div>
                            <span>Currently <?php echo $clockedIn ? 'Clocked In' : 'Clocked Out'; ?></span>
                            <button class="toggle-btn" id="clockButton" data-status="<?php echo $clockedIn ? 'in' : 'out'; ?>">
                                <?php echo $clockedIn ? 'Clock Out' : 'Clock In'; ?>
                            </button>
                        </div>
                        <div class="time-stats">
                            <div class="time-stat">
                                <h4>Today</h4>
                                <p><?php echo $timeStats['today']; ?></p>
                            </div>
                            <div class="time-stat">
                                <h4>This Week</h4>
                                <p><?php echo $timeStats['week']; ?></p>
                            </div>
                            <div class="time-stat">
                                <h4>This Month</h4>
                                <p><?php echo $timeStats['month']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Projects Preview -->
            <div class="preview-section">
                <div class="section-title">
                    <h2><i class='bx bx-folder'></i> Recent Projects</h2>
                    <a href="projects.php" class="view-all">View All</a>
                </div>
                <div class="cards-container2">
                    <div class="card">
                        <h3>Active Projects</h3>
                        <?php if (count($projects) > 0): ?>
                            <?php foreach ($projects as $project): ?>
                                <div class="project-item" data-project-id="<?php echo $project['id']; ?>">
                                    <div class="project-color" style="background-color: #<?php echo substr(md5($project['project_title']), 0, 6); ?>;"></div>
                                    <div class="project-info">
                                        <h4><?php echo htmlspecialchars($project['project_title']); ?></h4>
                                        <p>Due: <?php echo date('M j, Y', strtotime($project['expected_completion'])); ?></p>
                                    </div>
                                    <span class="project-status <?php echo str_replace('_', '-', $project['status']); ?>">
                                        <?php echo ucwords(str_replace('_', ' ', $project['status'])); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-projects">
                                <i class='bx bx-folder-open'></i>
                                <p>No active projects found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Account Settings Preview -->
            <div class="preview-section">
                <div class="section-title">
                    <h2><i class='bx bx-cog'></i> Account Settings</h2>
                    <a href="account.php" class="view-all">View All</a>
                </div>
                <div class="cards-container2">
                    <div class="card">
                        <h3>Quick Settings</h3>
                        <div class="settings-options">
                            <a href="account.php" class="settings-btn" data-section="profile">
                                <div class="settings-btn-content">
                                    <i class='bx bx-user'></i>
                                    <span>Profile Information</span>
                                </div>
                                <i class='bx bx-chevron-right'></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <script src="./scripts/dashboard.js"></script>
            <script src="scripts/clock.js"></script>
        </div>
    </div>
</body>

</html>