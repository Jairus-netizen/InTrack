<?php
session_start();
// 2. Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 3. Start output buffering
ob_start();

// 4. Verify admin status immediately
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'admin') {
    header("Location: /intrack-cathy/landing-page/login.php");
    exit();
}

// 5. Fix database connection path (make sure it matches your file structure)
 require_once '/xampp/htdocs/intrack-cathy/user-page/database/dbconnection.php';

// Rest of your existing code...
$pageStyles = '<link rel="stylesheet" href="../styles/interns.css">';

// Handle status change
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'activate' || $action === 'deactivate') {
        $newStatus = $action === 'activate' ? 1 : 0;
        $query = "UPDATE accounts SET is_active = ? WHERE account_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $newStatus, $id);
        mysqli_stmt_execute($stmt);

        // Redirect to avoid form resubmission
        header("Location: interns.php");
        ob_end_flush();
        exit();
    } elseif ($action === 'archive') {
        // Define valid tables for archiving
        $validTables = [
            'accounts' => ['type' => 'account', 'id_field' => 'account_id'],
            'project_monitors' => ['type' => 'project', 'id_field' => 'project_id']
        ];

        $sourceTable = 'accounts'; // Since we're archiving from accounts table
        $originType = $validTables[$sourceTable]['type'];
        $idField = $validTables[$sourceTable]['id_field'];

        // Archive the account
        $query = "SELECT * FROM accounts WHERE account_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $account = mysqli_fetch_assoc($result);

        if ($account) {
            // Convert account data to JSON
            $archivedData = json_encode($account);

            // Check if admin is logged in and get their ID - CHANGED TO user_id
            if (!isset($_SESSION['user_id'])) {
                die("Error: Admin session not found. Please log in.");
            }
            $archivedBy = $_SESSION['user_id'];

            // Insert into archive_admin table
            $query = "INSERT INTO archive_admin (origin_type, original_id, archived_data, archived_by, reason) 
                  VALUES (?, ?, ?, ?, 'Archived by admin')";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'sisi', $originType, $id, $archivedData, $archivedBy);

            if (!mysqli_stmt_execute($stmt)) {
                die("Error archiving account: " . mysqli_error($conn));
            }

            // Delete from accounts table
            $query = "DELETE FROM accounts WHERE account_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $id);

            if (!mysqli_stmt_execute($stmt)) {
                die("Error deleting account: " . mysqli_error($conn));
            }

            header("Location: interns.php");
            ob_end_flush();
            exit();
        }
    }
}

// Get search and filter parameters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$statusFilter = isset($_GET['status-filter']) ? $_GET['status-filter'] : '';

// Build the query with project count
$query = "SELECT a.*, COUNT(p.id) as project_count 
          FROM accounts a 
          LEFT JOIN projects p ON a.account_id = p.user_id 
          WHERE a.account_type = 'user'";
$params = array();

if (!empty($search)) {
    $query .= " AND (a.first_name LIKE ? OR a.last_name LIKE ? OR a.email LIKE ? OR a.school LIKE ?)";
    $searchTerm = "%$search%";
    $params = array($searchTerm, $searchTerm, $searchTerm, $searchTerm);
}

if ($statusFilter === 'active') {
    $query .= " AND a.is_active = 1";
} elseif ($statusFilter === 'inactive') {
    $query .= " AND a.is_active = 0";
}

// Add GROUP BY to count projects correctly
$query .= " GROUP BY a.account_id";

// Prepare and execute the query
$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Now include the header after any potential redirects
include '../includes/header.php';
?>

<!-- MAIN -->
<main>
    <div class="head-title">
        <div class="left">
            <h1>Interns Accounts</h1>
            <ul class="breadcrumb">
                <li>
                    <a href="dashboard.php">Dashboard</a>
                </li>
                <li>
                    <i class="bx bx-chevron-right"></i>
                </li>
                <li>
                    <a class="active" href="#">Interns</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="interns-page-container">
        <div class="interns-tools-wrapper">
            <div class="interns-search-filter">
                <div class="interns-filter-group">
                    <form action="interns.php" method="GET" class="interns-search-form">
                        <select name="status-filter" id="status-filter" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                        <div class="interns-form-input">
                            <input type="text" name="search" placeholder="Search students..." value="<?= htmlspecialchars($search) ?>">
                            <button type="submit" class="interns-search-btn"><i class="bx bx-search"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="table-data">
            <div class="head">
                <h3>Interns Accounts</h3>
                <span>Update as of <?= date('F j, Y') ?></span>
            </div>
            <div class="interns-table-responsive">
                <table class="interns-table">
                    <thead>
                        <tr>
                            <td>Full Name</td>
                            <td>Email</td>
                            <td>School</td>
                            <td>Registered</td>
                            <td>Projects</td>
                            <td>Status</td>
                            <td>Actions</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['last_name'] . ', ' . $row['first_name'] . ' ' . ($row['middle_name'] ? substr($row['middle_name'], 0, 1) . '.' : '') . ($row['suffix'] ? ' ' . $row['suffix'] : '')) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['school'] ?? 'N/A') ?></td>
                                <td><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
                                <td><?= $row['project_count'] ?></td> <!-- Updated to show actual project count -->
                                <td>
                                    <span class="interns-status <?= $row['is_active'] ? 'active' : 'inactive' ?>">
                                        <?= $row['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="interns-actions">
                                        <a href="edit_interns.php?id=<?= $row['account_id'] ?>" class="interns-edit-btn" title="Edit">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <?php if ($row['is_active']): ?>
                                            <a href="interns.php?action=deactivate&id=<?= $row['account_id'] ?>" class="interns-deactivate-btn" title="Deactivate">
                                                <i class="bx bx-user-x"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="interns.php?action=activate&id=<?= $row['account_id'] ?>" class="interns-activate-btn" title="Activate">
                                                <i class="bx bx-user-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="interns.php?action=archive&id=<?= $row['account_id'] ?>" class="interns-archive-btn" title="Archive" onclick="return confirm('Are you sure you want to archive this intern?')">
                                            <i class="bx bx-archive"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
<?php
mysqli_close($conn);
include '../includes/footer.php';
?>