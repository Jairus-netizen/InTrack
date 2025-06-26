<?php
session_start();
// Verify admin status immediately
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'admin') {
    header("Location: /intrack-cathy/landing-page/login.php");
    exit();
}

// Include database connection
require_once '/xampp/htdocs/intrack-cathy/user-page/database/dbconnection.php';

// Get account statistics
$totalUsers = $conn->query("SELECT COUNT(*) FROM accounts")->fetch_row()[0];
$activeInterns = $conn->query("SELECT COUNT(*) FROM accounts WHERE is_active = 1 AND account_type = 'user'")->fetch_row()[0];
$inactiveInterns = $conn->query("SELECT COUNT(*) FROM accounts WHERE is_active = 0 AND account_type = 'user'")->fetch_row()[0];

// Get recent time logs (limit to 3)
$recentTimeLogs = $conn->query("
    SELECT te.*, a.first_name, a.last_name, a.account_id 
    FROM time_entries te
    JOIN accounts a ON te.user_id = a.account_id
    ORDER BY te.date DESC, te.clock_in DESC
    LIMIT 3
");

$monthlyStats = $conn->query("
    SELECT 
        MONTH(te.date) as month,
        FLOOR(COUNT(DISTINCT te.user_id)) as active_interns
    FROM time_entries te
    WHERE YEAR(te.date) = YEAR(CURDATE())
    GROUP BY MONTH(te.date)
    ORDER BY month
");

$monthlyData = array_fill(0, 12, 0); // Initialize all months with 0
while ($row = $monthlyStats->fetch_assoc()) {
    $monthlyData[$row['month'] - 1] = $row['active_interns'];
}

// Function to convert time to 12-hour format with am/pm
function formatTime12Hour($time) {
    if (empty($time) || $time == '00:00:00') {
        return '--:--';
    }
    return date('g:i a', strtotime($time));
}

include '../includes/header.php';
?>

<!-- MAIN -->
<main class="main-content">
    <div class="head-title">
        <div class="left">
            <h1>Dashboard</h1>
            <ul class="breadcrumb">
                <li>
                    <a href="#">Dashboard</a>
                </li>
            </ul>
        </div>
    </div>

    <ul class="box-info">
        <li>
            <i class="bx bxs-user"></i>
            <span class="text">
                <h3>Total Users</h3>
                <p><?php echo $totalUsers; ?></p>
            </span>
        </li>
        <li>
            <i class='bx bxs-user-check'></i>
            <span class="text">
                <h3>Active Interns</h3>
                <p><?php echo $activeInterns; ?></p>
            </span>
        </li>
        <li>
            <i class='bx bxs-user-x'></i>
            <span class="text">
                <h3>Inactive Interns</h3>
                <p><?php echo $inactiveInterns; ?></p>
            </span>
        </li>
    </ul>

    <div class="table-data">
        <div class="head">
            <h3>Recent Time Logs</h3>
        </div>
        <table>
            <thead>
                <tr>
                    <td>Intern ID</td>
                    <td>Intern Name</td>
                    <td>Time In</td>
                    <td>Time Out</td>
                    <td>Date</td>
                </tr>
            </thead>
            <tbody>
                <?php if ($recentTimeLogs->num_rows > 0): ?>
                    <?php while ($log = $recentTimeLogs->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['account_id']); ?></td>
                            <td><?php echo htmlspecialchars($log['first_name'] . ' ' . $log['last_name']); ?></td>
                            <td><?php echo formatTime12Hour($log['clock_in']); ?></td>
                            <td><?php echo $log['clock_out'] ? formatTime12Hour($log['clock_out']) : '--:--'; ?></td>
                            <td><?php echo date('m - d - Y', strtotime($log['date'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">No recent time logs found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="table-data-foot">
            <a href="../admin/time-log.php"><span>View All</span></a>
        </div>
    </div>

    <div class="statistics-graph">
        <div class="head">
            <h3>Intern Statistics (<?php echo date('Y'); ?>)</h3>
        </div>
        <canvas id="myChart" data-monthly-data="<?php echo htmlspecialchars(json_encode($monthlyData)); ?>"></canvas>
    </div>
</main>

<!-- FOOTER -->
<?php
include '../includes/footer.php';
?>