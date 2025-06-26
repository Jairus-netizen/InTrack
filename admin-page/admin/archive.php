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

// Handle retrieval and deletion
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'retrieve') {
        // Get archived data
        $query = "SELECT * FROM archive_admin WHERE archive_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $archive = mysqli_fetch_assoc($result);

        if ($archive) {
            $data = json_decode($archive['archived_data'], true);
            $success = false;

            if ($archive['origin_type'] === 'account') {
                // Insert back into accounts table
                $query = "INSERT INTO accounts (
                    account_id, last_name, first_name, middle_name, suffix, 
                    phone, email, password, account_type, created_at, 
                    is_active, birthday, school, user_profile
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param(
                    $stmt,
                    'isssssssssisss',
                    $data['account_id'],
                    $data['last_name'],
                    $data['first_name'],
                    $data['middle_name'],
                    $data['suffix'],
                    $data['phone'],
                    $data['email'],
                    $data['password'],
                    $data['account_type'],
                    $data['created_at'],
                    $data['is_active'],
                    $data['birthday'],
                    $data['school'],
                    $data['user_profile']
                );
                $success = mysqli_stmt_execute($stmt);
            } elseif ($archive['origin_type'] === 'project') {
                // Insert back into projects table
                $query = "INSERT INTO projects (
                    id, user_id, project_title, arrange_by, project_manager, 
                    expected_completion, project_progress, team_lead, 
                    department, availability, status, images
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param(
                    $stmt,
                    'iissssssssss',
                    $data['id'],
                    $data['user_id'],
                    $data['project_title'],
                    $data['arrange_by'],
                    $data['project_manager'],
                    $data['expected_completion'],
                    $data['project_progress'],
                    $data['team_lead'],
                    $data['department'],
                    $data['availability'],
                    $data['status'],
                    $data['images']
                );
                $success = mysqli_stmt_execute($stmt);
            }

            if ($success) {
                // Delete from archive table
                $query = "DELETE FROM archive_admin WHERE archive_id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'i', $id);
                mysqli_stmt_execute($stmt);

                $_SESSION['archive_message'] = 'Item retrieved successfully!';
                header("Location: archive.php");
                exit();
            }
        }
    } elseif ($action === 'delete') {
        // Permanently delete
        $query = "DELETE FROM archive_admin WHERE archive_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['archive_message'] = 'Item permanently deleted!';
            header("Location: archive.php");
            exit();
        }
    }
}

// Build the base query for filtering
$baseQuery = "SELECT archive_admin.*, accounts.first_name as admin_first, accounts.last_name as admin_last 
              FROM archive_admin 
              LEFT JOIN accounts ON archive_admin.archived_by = accounts.account_id";

// Initialize where clauses array
$whereClauses = [];
$params = [];
$types = '';

// Filter by type if specified
if (isset($_GET['type']) && in_array($_GET['type'], ['account', 'project'])) {
    $whereClauses[] = "archive_admin.origin_type = ?";
    $params[] = $_GET['type'];
    $types .= 's';
}

// Search functionality
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $searchTerm = '%' . trim($_GET['search']) . '%';
    $whereClauses[] = "(archive_admin.archived_data LIKE ?)";
    $params[] = $searchTerm;
    $types .= 's';
}

// Combine where clauses
if (!empty($whereClauses)) {
    $baseQuery .= " WHERE " . implode(" AND ", $whereClauses);
}

// Sorting
$sort = 'DESC'; // Default: newest first
if (isset($_GET['sort']) && $_GET['sort'] === 'oldest') {
    $sort = 'ASC';
}
$baseQuery .= " ORDER BY archive_admin.date_archived " . $sort;

// Prepare and execute the query
$stmt = mysqli_prepare($conn, $baseQuery);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Count total filtered items - fixed this query
$countQuery = "SELECT COUNT(*) as total FROM archive_admin";
if (!empty($whereClauses)) {
    $countQuery .= " WHERE " . implode(" AND ", $whereClauses);
    
    // Remove the accounts table reference from the where clauses for the count query
    $countQuery = str_replace("accounts.first_name", "archive_admin.archived_data", $countQuery);
    $countQuery = str_replace("accounts.last_name", "archive_admin.archived_data", $countQuery);
}

$countStmt = mysqli_prepare($conn, $countQuery);
if (!empty($params)) {
    // For count query, we only need the parameters that apply to archive_admin table
    $countParams = [];
    $countTypes = '';
    
    foreach ($params as $param) {
        // Only include parameters that are for archive_admin fields
        if (strpos($whereClauses[0], 'archive_admin') !== false || 
            strpos($whereClauses[0], '?') !== false) {
            $countParams[] = $param;
            $countTypes .= 's';
        }
    }
    
    if (!empty($countParams)) {
        mysqli_stmt_bind_param($countStmt, $countTypes, ...$countParams);
    }
}
mysqli_stmt_execute($countStmt);
$countResult = mysqli_stmt_get_result($countStmt);
$totalItems = mysqli_fetch_assoc($countResult)['total'];

$pageStyles = '<link rel="stylesheet" href="../styles/archive.css">';
include '../includes/header.php';
?>

<!-- MAIN -->
<main>
    <div class="head-title">
        <div class="left">
            <h1>Archive</h1>
            <ul class="breadcrumb">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><i class="bx bx-chevron-right"></i></li>
                <li><a class="active" href="#">Archive</a></li>
            </ul>
        </div>
    </div>

    <?php if (isset($_SESSION['archive_message'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['archive_message']; unset($_SESSION['archive_message']); ?>
        </div>
    <?php endif; ?>

    <div class="archive-page-container">
        <div class="archive-tools-wrapper">
            <div class="archive-search-filter">
                <div class="archive-filter-group">
                    <form method="GET" action="">
                        <select name="type" class="archive-type-select" onchange="this.form.submit()">
                            <option value="all">All Archives</option>
                            <option value="account" <?= (isset($_GET['type']) && $_GET['type'] === 'account') ? 'selected' : '' ?>>Accounts</option>
                            <option value="project" <?= (isset($_GET['type']) && $_GET['type'] === 'project') ? 'selected' : '' ?>>Projects</option>
                        </select>
                        <select name="sort" class="archive-date-select" onchange="this.form.submit()">
                            <option value="newest" <?= (!isset($_GET['sort']) || $_GET['sort'] === 'newest') ? 'selected' : '' ?>>Newest First</option>
                            <option value="oldest" <?= (isset($_GET['sort']) && $_GET['sort'] === 'oldest') ? 'selected' : '' ?>>Oldest First</option>
                        </select>
                    </form>
                </div>
                <form method="GET" action="" class="archive-search-form">
                    <div class="archive-form-input">
                        <input type="text" name="search" placeholder="Search archives..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                        <button type="submit" class="archive-search-btn">
                            <i class='bx bx-search'></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="archive-table-data">
            <div class="archive-table-head">
                <h3>Archived Items</h3>
                <span>Total: <?= $totalItems ?> items</span>
            </div>

            <div class="archive-table-responsive">
                <table class="archive-table">
                    <thead>
                        <tr>
                            <td>ID</td>
                            <td>Type</td>
                            <td>Name/Title</td>
                            <td>Archived Date</td>
                            <td>Archived By</td>
                            <td>Actions</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)):
                            $data = json_decode($row['archived_data'], true);
                            $name = '';
                            if ($row['origin_type'] === 'account') {
                                $name = $data['last_name'] . ', ' . $data['first_name'];
                                if (!empty($data['middle_name'])) {
                                    $name .= ' ' . substr($data['middle_name'], 0, 1) . '.';
                                }
                            } elseif ($row['origin_type'] === 'project') {
                                $name = $data['project_title'];
                            }
                        ?>
                            <tr>
                                <td>#<?= $row['archive_id'] ?></td>
                                <td><?= ucfirst($row['origin_type']) ?></td>
                                <td><?= htmlspecialchars($name) ?></td>
                                <td><?= date('Y-m-d H:i', strtotime($row['date_archived'])) ?></td>
                                <td><?= htmlspecialchars($row['admin_first'] . ' ' . $row['admin_last']) ?></td>
                                <td>
                                    <div class="archive-actions">
                                        <a href="archive.php?action=retrieve&id=<?= $row['archive_id'] ?><?= isset($_GET['type']) ? '&type=' . urlencode($_GET['type']) : '' ?><?= isset($_GET['sort']) ? '&sort=' . urlencode($_GET['sort']) : '' ?><?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>" class="archive-retrieve-btn" title="Retrieve" onclick="return confirmRetrieve()">
                                            <i class='bx bx-undo'></i>
                                        </a>
                                        <a href="archive.php?action=delete&id=<?= $row['archive_id'] ?><?= isset($_GET['type']) ? '&type=' . urlencode($_GET['type']) : '' ?><?= isset($_GET['sort']) ? '&sort=' . urlencode($_GET['sort']) : '' ?><?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>" class="archive-delete-btn" title="Delete Permanently" onclick="return confirmDelete()">
                                            <i class='bx bx-trash'></i>
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

<script>
function confirmRetrieve() {
    return confirm('Are you sure you want to retrieve this item?');
}

function confirmDelete() {
    return confirm('WARNING: This will permanently delete the item. Are you sure?');
}
</script>

<?php
mysqli_close($conn);
include '../includes/footer.php';
?>