<?php
// Start session and check authentication
session_start();
require_once 'database/dbconnection.php';
require_once 'notification-functions.php';

if (!isset($_SESSION['last_due_check'])) {
    // Update overdue projects and check for due dates
    updateOverdueProjects($conn, $_SESSION['user_id']);
    checkProjectDueDates($conn);

    // Set a session flag to prevent repeated checks
    $_SESSION['last_due_check'] = time();
} elseif (time() - $_SESSION['last_due_check'] > 3600) {
    // If more than 1 hour has passed, check again
    updateOverdueProjects($conn, $_SESSION['user_id']);
    checkProjectDueDates($conn);
    $_SESSION['last_due_check'] = time();
}
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /intrack-cathy/login.php");
    exit();
}

// Function to check and update overdue projects
function updateOverdueProjects($conn, $user_id)
{
    $currentDate = date('Y-m-d');
    $query = "UPDATE projects 
              SET status = 'over_due' 
              WHERE user_id = ? 
              AND status = 'in_progress' 
              AND expected_completion < ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $currentDate);
    mysqli_stmt_execute($stmt);
}

// Update overdue projects for the current user
updateOverdueProjects($conn, $_SESSION['user_id']);

// Fetch projects from database for the current user only
$projects = [];
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM projects WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result) {
    $projects = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$notifications = [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intrack | Projects</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="styles/projects.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <!-- Sidebar Navigation -->
    <?php include 'sidebar.php' ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard Page (default active) -->
        <div id="projects" class="page-content active">
            <div class="header">
                <h1>Projects</h1>
                <div class="user-info">
                    <h4>Hello!</h4>
                    <span><?php echo htmlspecialchars($_SESSION['first_name']); ?></span>
                </div>
            </div>
            <div class="project-content">
                <div class="container">
                    <div class="content-wrapper">
                        <div class="content-header">
                            <i class="fa-solid fa-clipboard add-icon"></i>
                            <h2>Current Projects</h2>
                        </div>
                        <div class="add-project-btn">
                            <button class="add-project" onclick="addProject('./add-project.php')">Add Project</button>
                        </div>
                        <div class="project-cards-container">
                            <?php if (empty($projects)): ?>
                                <div class="no-projects">
                                    <i class="fas fa-folder-open" style="font-size: 5rem; color: #ccc; margin-bottom: 20px;"></i>
                                    <p style="font-size: 1.2rem; color: #666;">No projects yet</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($projects as $project): ?>
                                    <div class="project-cards">
                                        <div class="project-img">
                                            <?php if (!empty($project['images'])): ?>
                                                <img src="<?php echo htmlspecialchars($project['images']); ?>" alt="Project image">
                                            <?php else: ?>
                                                <img src="./project-images/default-project.jpg" alt="Default project image">
                                            <?php endif; ?>
                                        </div>
                                        <div class="project-body">
                                            <h1><?php echo htmlspecialchars($project['project_title']); ?></h1>
                                            <p>Status: <?php echo ucfirst(str_replace('_', ' ', $project['status'])); ?></p>
                                            <button class="action-btn view-project" onclick="viewProject('./view-project.php?id=<?php echo $project['id']; ?>')">View Project</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                function viewProject(url) {
                    window.location.href = url;
                }

                function addProject(url) {
                    window.location.href = url;
                }
            </script>
        </div>
    </div>
</body>

</html>