<?php
session_start();
require_once 'database/dbconnection.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: /intrack-cathy/landing-page/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Get time stats
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

$timeStats = getTimeStats($userId, $conn);

// Get current clock status
$clockedIn = false;
$currentEntry = null;

$stmt = $conn->prepare("SELECT * FROM time_entries 
                       WHERE user_id = ? AND date = CURDATE() 
                       ORDER BY clock_in DESC LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$currentEntry = $result->fetch_assoc();

if ($currentEntry && !$currentEntry['clock_out']) {
    $clockedIn = true;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intrack | Time Tracking</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="styles/time.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <!-- Sidebar Navigation -->
    <?php include 'sidebar.php' ?>

    <!-- Main Content -->
    <div class="main-content">
        <div id="dashboard" class="page-content active">
            <div class="header">
                <h1>Time In/Time Out</h1>
                <div class="user-info">
                    <h4>Hello!</h4>
                    <span><?php echo htmlspecialchars($_SESSION['first_name']); ?></span>
                </div>
            </div>

            <!-- Time Tracking Section -->
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

            <!-- Time History Table -->
            <div class="preview-section">
                <div class="section-title">
                    <h2><i class='bx bx-history'></i> Time History</h2>
                </div>
                <div class="card">
                    <table class="time-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Clock In</th>
                                <th>Clock Out</th>
                                <th>Status</th>
                                <th>Hours</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM time_entries 
                                                  WHERE user_id = ? AND archived = 0
                                                  ORDER BY date DESC, clock_in DESC 
                                                  LIMIT 10");
                            $stmt->bind_param("i", $userId);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            while ($entry = $result->fetch_assoc()):
                                $clockOut = $entry['clock_out'] ? date('g:i A', strtotime($entry['clock_out'])) : '--';
                                $duration = '--';

                                if ($entry['clock_out']) {
                                    $diff = strtotime($entry['clock_out']) - strtotime($entry['clock_in']);
                                    $hours = floor($diff / 3600);
                                    $minutes = floor(($diff % 3600) / 60);
                                    $duration = $hours . 'h ' . $minutes . 'm';
                                }
                            ?>
                                <tr>
                                    <td><?php echo date('M j, Y', strtotime($entry['date'])); ?></td>
                                    <td><?php echo date('g:i A', strtotime($entry['clock_in'])); ?></td>
                                    <td><?php echo $clockOut; ?></td>
                                    <td class="time-status <?php echo strtolower($entry['status']); ?>">
                                        <?php echo ucfirst($entry['status']); ?>
                                    </td>
                                    <td><?php echo $duration; ?></td>
                                    <td class="notes-cell">
                                        <?php
                                        $notesToShow = [];

                                        // System-generated notes based on is_suspicious
                                        if ($entry['is_suspicious'] && $entry['is_suspicious'] !== 'NULL') {
                                            switch ($entry['is_suspicious']) {
                                                case 'early_time_in':
                                                    $notesToShow[] = 'Early time in (before 8:00 AM)';
                                                    break;
                                                case 'late_time_in':
                                                    $notesToShow[] = 'Late time in (after 8:00 AM)';
                                                    break;
                                                case 'early_time_out':
                                                    $notesToShow[] = 'Early time out (before 5:00 PM)';
                                                    break;
                                            }
                                        }

                                        // Additional user/admin notes
                                        if (!empty($entry['notes'])) {
                                            $additionalNotes = trim(preg_replace(
                                                '/Early time (in|out) \(before .*?\)|Late time in \(after .*?\)/',
                                                '',
                                                $entry['notes']
                                            ));

                                            if (!empty($additionalNotes)) {
                                                $notesToShow[] = $additionalNotes;
                                            }
                                        }

                                        echo $notesToShow ? htmlspecialchars(implode("\n", array_filter($notesToShow))) : '--';
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes Modal -->
    <div id="notesModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h3>Edit Notes</h3>
            <textarea id="notesText" rows="5"></textarea>
            <div class="modal-actions">
                <button id="saveNotes">Save</button>
                <button id="cancelNotes">Cancel</button>
            </div>
        </div>
    </div>

</body>
<script src="./scripts/clock.js"></script>
</html>