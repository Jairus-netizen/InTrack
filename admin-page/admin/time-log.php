<?php
// MUST be at VERY TOP - before any output
session_start();

// Verify admin status immediately
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'admin') {
    header("Location: /intrack-cathy/landing-page/login.php");
    exit();
}

// Include database connection
require_once '/xampp/htdocs/intrack-cathy/user-page/database/dbconnection.php';

// Handle retrieval
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    // Add your action handling code here if needed
}

// Initialize search variables
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$whereClause = "WHERE te.archived = 0";
$params = [];
$types = '';

// Add search conditions if search term exists
if (!empty($search)) {
    $whereClause .= " AND (a.account_id LIKE ? 
                        OR a.first_name LIKE ? 
                        OR a.last_name LIKE ? 
                        OR a.middle_name LIKE ? 
                        OR te.date LIKE ? 
                        OR te.notes LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_fill(0, 6, $searchTerm);
    $types = str_repeat('s', count($params));
}

// Prepare and execute query
$query = "SELECT te.*, a.first_name, a.last_name, a.middle_name, a.account_id 
          FROM time_entries te
          JOIN accounts a ON te.user_id = a.account_id
          $whereClause
          ORDER BY te.date DESC, te.clock_in DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$totalEntries = $result->num_rows;

// Function to convert time to 12-hour format with am/pm
function formatTime12Hour($time) {
    if (empty($time) || $time == '00:00:00') {
        return '--:--';
    }
    return date('g:i a', strtotime($time));
}

$pageStyles = '<link rel="stylesheet" href="../styles/time-log.css">';
include '../includes/header.php';
?>

<!-- MAIN -->
<main>
    <div class="head-title">
        <div class="left">
            <h1>Time Log</h1>
            <ul class="breadcrumb">
                <li>
                    <a href="dashboard.php">Dashboard</a>
                </li>
                <li>
                    <i class="bx bx-chevron-right"></i>
                </li>
                <li>
                    <a class="active" href="#">Time Log</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="time-log-page-container">
        <div class="time-log-tools-wrapper">
            <div class="time-log-search-filter">
                <form method="GET" action="">
                    <div class="time-log-search-form">
                        <div class="time-log-form-input">
                            <input type="text" name="search" placeholder="Search..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="time-log-search-btn">
                                <i class='bx bx-search'></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="time-log-table-data">
            <div class="time-log-table-head">
                <h3>Intern Time Log</h3>
                <span>Total: <?php echo $totalEntries; ?> entries</span>
                <?php if (!empty($search)): ?>
                    <span class="search-results">Showing results for: "<?php echo htmlspecialchars($search); ?>"</span>
                <?php endif; ?>
            </div>
            <div class="time-log-table-responsive">
                <table class="time-log-table">
                    <thead>
                        <tr>
                            <td>Intern ID</td>
                            <td>Intern Name</td>
                            <td>Time In</td>
                            <td>Time Out</td>
                            <td>Date</td>
                            <td>Notes</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['account_id']); ?></td>
                                    <td>
                                        <?php 
                                        $name = htmlspecialchars($row['last_name']) . ', ' . htmlspecialchars($row['first_name']);
                                        if (!empty($row['middle_name'])) {
                                            $name .= ' ' . htmlspecialchars(substr($row['middle_name'], 0, 1)) . '.';
                                        }
                                        echo $name;
                                        ?>
                                    </td>
                                    <td>
                                        <span class="status <?php echo $row['status']; ?>">
                                            <?php echo formatTime12Hour($row['clock_in']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status <?php echo $row['status']; ?>">
                                            <?php echo $row['clock_out'] ? formatTime12Hour($row['clock_out']) : '--:--'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('F j, Y', strtotime($row['date'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['notes'] ?? ''); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">
                                    <?php echo empty($search) ? 'No time entries found' : 'No results found for "' . htmlspecialchars($search) . '"'; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php
$pageScript = ' <script src="../scripts/time-log.js"></script>';
include '../includes/footer.php';
?>