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

// Handle archive action
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'archive') {
        // Get project data
        $query = "SELECT * FROM projects WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $project = $result->fetch_assoc();

        if ($project) {
            // Convert project data to JSON
            $archivedData = json_encode($project);
            $archivedBy = $_SESSION['user_id'];
            $originType = 'project';

            // Insert into archive_admin table
            $query = "INSERT INTO archive_admin (origin_type, original_id, archived_data, archived_by, reason) 
                      VALUES (?, ?, ?, ?, 'Archived by admin')";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('sisi', $originType, $id, $archivedData, $archivedBy);

            if ($stmt->execute()) {
                // Delete from projects table
                $query = "DELETE FROM projects WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('i', $id);
                $stmt->execute();

                // Redirect to avoid form resubmission
                header("Location: project-monitor.php");
                exit();
            }
        }
    }
}

// Initialize variables
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$whereClause = "WHERE p.id IS NOT NULL";
$params = [];
$types = '';

// Add search conditions if search term exists
if (!empty($search)) {
    $whereClause .= " AND (p.project_title LIKE ? OR p.arrange_by LIKE ? OR p.project_manager LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_fill(0, 3, $searchTerm);
    $types = str_repeat('s', count($params));
}

// Add status filter if selected
if (!empty($status_filter)) {
    $status_map = [
        'ongoing' => 'in_progress',
        'on-hold' => 'over_due',
        'dropped' => 'completed'
    ];

    if (array_key_exists($status_filter, $status_map)) {
        $whereClause .= " AND p.status = ?";
        $params[] = $status_map[$status_filter];
        $types .= 's';
    }
}

// Prepare and execute query
$query = "SELECT p.*, a.first_name, a.last_name 
          FROM projects p
          LEFT JOIN accounts a ON p.user_id = a.account_id
          $whereClause
          ORDER BY p.expected_completion ASC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$totalProjects = $result->num_rows;

$pageStyles = '<link rel="stylesheet" href="../styles/project-monitor.css">';
include '../includes/header.php';
?>

<!-- MAIN -->
<main>
    <div class="head-title">
        <div class="left">
            <h1>Project Monitor</h1>
            <ul class="breadcrumb">
                <li>
                    <a href="dashboard.php">Dashboard</a>
                </li>
                <li>
                    <i class="bx bx-chevron-right"></i>
                </li>
                <li>
                    <a class="active" href="#">Project Monitor</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="project-monitor-page-container">
        <div class="project-monitor-tools-wrapper">
            <div class="project-monitor-search-filter">
                <div class="project-monitor-filter-group">
                    <form method="GET" action="">
                        <select name="status" id="status" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="ongoing" <?php echo ($status_filter === 'ongoing') ? 'selected' : ''; ?>>In Progress</option>
                            <option value="on-hold" <?php echo ($status_filter === 'on-hold') ? 'selected' : ''; ?>>Over Due</option>
                            <option value="dropped" <?php echo ($status_filter === 'dropped') ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </form>
                </div>
                <div class="project-monitor-search-form">
                    <form method="GET" action="">
                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                        <div class="project-monitor-form-input">
                            <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>" id="search-input">
                            <button type="<?php echo empty($search) ? 'submit' : 'button'; ?>"
                                class="project-monitor-search-btn"
                                id="search-button">
                                <i class='bx <?php echo empty($search) ? 'bx-search' : 'bx-x'; ?>'></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="project-monitor-table-data">
            <div class="project-monitor-table-head">
                <h3>Project Details</h3>
                <span>Total: <?php echo $totalProjects; ?> Current Project(s)</span>
                <?php if (!empty($search)): ?>
                    <span class="search-results">Showing results for: "<?php echo htmlspecialchars($search); ?>"</span>
                <?php endif; ?>
            </div>
            <div class="project-monitor-table-responsive">
                <table class="project-monitor-table">
                    <thead>
                        <tr>
                            <td>Project ID</td>
                            <td>Project Title</td>
                            <td>Arranged</td>
                            <td>Status</td>
                            <td>Actions</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>PRJ-<?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($row['project_title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['arrange_by'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        $status_text = '';
                                        switch ($row['status']) {
                                            case 'in_progress':
                                                $status_class = 'on-hold';
                                                $status_text = 'In Progress';
                                                break;
                                            case 'over_due':
                                                $status_class = 'ongoing';
                                                $status_text = 'Over Due';
                                                break;
                                            case 'completed':
                                                $status_class = 'dropped';
                                                $status_text = 'Completed';
                                                break;
                                        }
                                        ?>
                                        <span class="project-monitor-stat <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                    </td>
                                    <td>
                                        <div class="project-monitor-actions">
                                            <a href="project-details.php?id=<?php echo $row['id']; ?>" class="project-monitor-view-btn" title="View Details">
                                                <i class='bx bx-show'></i>
                                            </a>
                                            <a href="project-monitor.php?action=archive&id=<?php echo $row['id']; ?>" class="project-archive-btn" title="Archive" onclick="return confirm('Are you sure you want to archive this project?')">
                                                <i class='bx bx-archive'></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">
                                    <?php echo empty($search) ? 'No projects found' : 'No results found for "' . htmlspecialchars($search) . '"'; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
<script src="../scripts/project-details.js"></script>
<?php include '../includes/footer.php'; ?>