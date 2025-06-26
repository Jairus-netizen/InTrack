<?php
// Start session and check authentication
session_start();
require_once 'database/dbconnection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /intrack-cathy/login.php");
    exit();
}

// Function to check and update overdue projects
function updateOverdueProjects($conn)
{
    $currentDate = date('Y-m-d');
    $query = "UPDATE projects SET status = 'over_due' 
              WHERE status = 'in_progress' 
              AND expected_completion < ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $currentDate);
    mysqli_stmt_execute($stmt);
}

// Check and update overdue projects for the current project
if (isset($_GET['id'])) {
    updateOverdueProjects($conn); // Update all overdue projects first

    // Then check specifically for this project
    $currentDate = date('Y-m-d');
    $overdueQuery = "UPDATE projects SET status = 'over_due' 
                    WHERE status = 'in_progress' 
                    AND expected_completion < ? 
                    AND id = ?";
    $stmt = mysqli_prepare($conn, $overdueQuery);
    mysqli_stmt_bind_param($stmt, "si", $currentDate, $_GET['id']);
    mysqli_stmt_execute($stmt);
}

// Handle project completion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_project'])) {
    $projectId = $_GET['id'] ?? null;
    if ($projectId) {
        // Get original expected completion date
        $checkQuery = "SELECT expected_completion FROM projects WHERE id = ?";
        $stmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($stmt, "i", $projectId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $projectData = mysqli_fetch_assoc($result);

        $currentDate = date('Y-m-d');
        $newStatus = 'completed';

        // Check if project is overdue based on original date
        if ($projectData['expected_completion'] < $currentDate) {
            $newStatus = 'over_due';
        }

        // Update status
        $updateQuery = "UPDATE projects SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($stmt, "si", $newStatus, $projectId);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt)) {
            $_SESSION['success_message'] = $newStatus === 'over_due'
                ? "Project marked as completed but is overdue!"
                : "Project marked as completed successfully!";
            header("Location: view-project.php?id=$projectId");
            exit();
        } else {
            $_SESSION['error_message'] = "Failed to update project status.";
        }
    }
}

// Fetch project data
$project = [];
if (isset($_GET['id'])) {
    $projectId = $_GET['id'];
    $query = "SELECT * FROM projects WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $projectId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $project = mysqli_fetch_assoc($result);
}

$notifications = [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intrack | View Project</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="styles/view-project.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <!-- Sidebar Navigation -->
    <?php include 'sidebar.php' ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard Page (default active) -->
        <div id="view-project" class="page-content active">
            <div class="header">
                <h1>View Project</h1>
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
                            <a href="notifications.php" class="view-all">View All Notifications</a>
                        </div>
                    </div>
                    <h4>Hello!</h4>
                    <span><?php echo htmlspecialchars($_SESSION['first_name']); ?></span>
                </div>
            </div>
            <div class="view-project-content">
                <div class="container">
                    <div class="content-wrapper">
                        <?php if (isset($_SESSION['success_message'])): ?>
                            <div class="alert alert-success">
                                <?php echo $_SESSION['success_message'];
                                unset($_SESSION['success_message']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-error">
                                <?php echo $_SESSION['error_message'];
                                unset($_SESSION['error_message']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($project): ?>
                            <div class="view-project-card">
                                <div class="view-project-header">
                                    <h2><?php echo htmlspecialchars($project['project_title']); ?></h2>
                                    <span class="project-status <?php echo str_replace('_', '-', $project['status']); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $project['status'])); ?>
                                    </span>
                                </div>

                                <div class="view-project-body">
                                    <div class="view-project-row">
                                        <div class="view-project-group">
                                            <img src="<?php echo htmlspecialchars($project['images'] ?: './project-images/default-project.jpg'); ?>" alt="project-img" class="project-image">
                                            <input type="file" id="project-image-upload" style="display: none;" accept="image/*">
                                            <button class="edit-image-btn">Change Image</button>
                                        </div>

                                        <div class="view-project-group">
                                            <h3>Details</h3>
                                            <div class="view-project-item">
                                                <span class="view-project-label">Project ID:</span>
                                                <input type="text" class="view-project-input" value="<?php echo htmlspecialchars($project['id']); ?>" readonly>
                                            </div>
                                            <div class="view-project-item">
                                                <span class="view-project-label">Project Title:</span>
                                                <input type="text" class="view-project-input" name="project_title" value="<?php echo htmlspecialchars($project['project_title']); ?>" <?php echo $project['status'] === 'completed' ? 'readonly' : ''; ?>>
                                            </div>
                                            <div class="view-project-item">
                                                <span class="view-project-label">Arranged by:</span>
                                                <input type="text" class="view-project-input" name="arrange_by" value="<?php echo htmlspecialchars($project['arrange_by']); ?>" <?php echo $project['status'] === 'completed' ? 'readonly' : ''; ?>>
                                            </div>
                                            <div class="view-project-item">
                                                <span class="view-project-label">Project Manager:</span>
                                                <input type="text" class="view-project-input" name="project_manager" value="<?php echo htmlspecialchars($project['project_manager']); ?>" <?php echo $project['status'] === 'completed' ? 'readonly' : ''; ?>>
                                            </div>
                                            <div class="view-project-item">
                                                <span class="view-project-label">Expected Completion Date:</span>
                                                <input type="date" class="view-project-input" name="expected_completion" value="<?php echo htmlspecialchars($project['expected_completion']); ?>" <?php echo $project['status'] === 'completed' ? 'readonly' : ''; ?>>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="view-project-group">
                                        <h3>Project Progress</h3>
                                        <textarea class="view-project-textarea" name="project_progress" <?php echo $project['status'] === 'completed' ? 'readonly' : ''; ?>><?php echo htmlspecialchars($project['project_progress']); ?></textarea>
                                    </div>

                                    <div class="view-project-row">
                                        <div class="view-project-group-bottom">
                                            <h3>Team and Collaboration</h3>
                                            <div class="view-project-item">
                                                <span class="view-project-label">Team Lead:</span>
                                                <input type="text" class="view-project-input" name="team_lead" value="<?php echo htmlspecialchars($project['team_lead']); ?>" <?php echo $project['status'] === 'completed' ? 'readonly' : ''; ?>>
                                            </div>
                                            <div class="view-project-item">
                                                <span class="view-project-label">Department:</span>
                                                <input type="text" class="view-project-input" name="department" value="<?php echo htmlspecialchars($project['department']); ?>" <?php echo $project['status'] === 'completed' ? 'readonly' : ''; ?>>
                                            </div>
                                            <div class="view-project-item">
                                                <span class="view-project-label">Availability:</span>
                                                <input type="text" class="view-project-input" name="availability" value="<?php echo htmlspecialchars($project['availability']); ?>" <?php echo $project['status'] === 'completed' ? 'readonly' : ''; ?>>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="view-project-footer">
                                    <button class="project-back-btn">
                                        <i class='bx bx-arrow-back'></i> Back
                                    </button>
                                    <div class="project-action-btns">
                                        <?php if ($project['status'] !== 'completed'): ?>
                                            <form method="POST" id="completeProjectForm" style="display: inline;">
                                                <input type="hidden" name="complete_project" value="1">
                                                <button type="button" class="project-complete-btn">Complete Project</button>
                                            </form>
                                            <button class="project-edit-btn">Edit Project</button>
                                            <button class="project-save-btn" style="display: none;">Save Changes</button>
                                            <button class="project-cancel-btn" style="display: none;">Cancel</button>
                                        <?php else: ?>
                                            <span class="project-completed-message">This project has been completed</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="no-project">
                                <i class="fas fa-exclamation-circle" style="font-size: 5rem; color: #ccc; margin-bottom: 20px;"></i>
                                <p style="font-size: 1.2rem; color: #666;">Project not found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <script src="scripts/view-project.js"></script>
        </div>
    </div>
</body>

</html>