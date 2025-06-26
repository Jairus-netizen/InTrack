<?php
// MUST be at VERY TOP - before any output
session_start();

// Verify admin status immediately
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'admin') {
    header("Location: /intrack-cathy/landing-page/login.php");
    exit();
}

// Check if project ID is provided
if (!isset($_GET['id'])) {
    header("Location: project-monitor.php");
    exit();
}

$project_id = intval($_GET['id']);

// Include database connection
require_once '/xampp/htdocs/intrack-cathy/user-page/database/dbconnection.php';

// Fetch project details
$query = "SELECT p.*, a.first_name, a.last_name 
          FROM projects p
          LEFT JOIN accounts a ON p.user_id = a.account_id
          WHERE p.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $project_id);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();

if (!$project) {
    header("Location: project-monitor.php");
    exit();
}

// Format status for display
$status_map = [
    'in_progress' => ['class' => 'ongoing', 'text' => 'Ongoing'],
    'over_due' => ['class' => 'on-hold', 'text' => 'Over Due'],
    'completed' => ['class' => 'completed', 'text' => 'Completed']
];
$status_info = $status_map[$project['status']] ?? ['class' => '', 'text' => 'Unknown'];

$pageStyles = '<link rel="stylesheet" href="../styles/project-details.css">';
include '../includes/header.php';
?>

<!-- MAIN -->
<main>
    <div class="head-title">
        <div class="left">
            <h1>Project Details</h1>
            <ul class="breadcrumb">
                <li>
                    <a href="dashboard.php">Dashboard</a>
                </li>
                <li>
                    <i class="bx bx-chevron-right"></i>
                </li>
                <li>
                    <a href="project-monitor.php">Project Monitor</a>
                </li>
                <li>
                    <i class="bx bx-chevron-right"></i>
                </li>
                <li>
                    <a class="active" href="#">Project Details</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="project-details-container">
        <div class="project-details-card">
            <div class="project-details-header">
                <h2><?php echo htmlspecialchars($project['project_title']); ?></h2>
                <span class="project-status <?php echo $status_info['class']; ?>"><?php echo $status_info['text']; ?></span>
            </div>

            <div class="project-details-body">
                <div class="project-details-row">
                    <?php if (!empty($project['images'])): ?>
                        <div class="project-details-group">
                            <img src="<?php echo htmlspecialchars($project['images']); ?>" alt="project-img" class="project-image">
                        </div>
                    <?php endif; ?>

                    <div class="project-details-group">
                        <h3>Details</h3>
                        <div class="project-details-item">
                            <span class="project-details-label">Project ID:</span>
                            <span class="project-details-value">PRJ-<?php echo str_pad($project['id'], 4, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div class="project-details-item">
                            <span class="project-details-label">Project Title:</span>
                            <span class="project-details-value"><?php echo htmlspecialchars($project['project_title']); ?></span>
                        </div>
                        <div class="project-details-item">
                            <span class="project-details-label">Arranged by:</span>
                            <span class="project-details-value"><?php echo htmlspecialchars($project['arrange_by'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="project-details-item">
                            <span class="project-details-label">Project Manager:</span>
                            <span class="project-details-value"><?php echo htmlspecialchars($project['project_manager'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="project-details-item">
                            <span class="project-details-label">Expected Completion Date:</span>
                            <span class="project-details-value">
                                <?php echo $project['expected_completion'] ? date('m/d/Y', strtotime($project['expected_completion'])) : 'Not set'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="project-details-group">
                    <h3>Project Progress</h3>
                    <div class="project-details-text">
                        <p><?php echo htmlspecialchars($project['project_progress'] ?? 'No progress details available.'); ?></p>
                    </div>
                </div>

                <div class="project-details-row">
                    <div class="project-details-group-bottom">
                        <h3>Team and Collaboration</h3>
                        <div class="project-details-item">
                            <span class="project-details-label">Team Lead:</span>
                            <span class="project-details-value"><?php echo htmlspecialchars($project['team_lead'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="project-details-item">
                            <span class="project-details-label">Department:</span>
                            <span class="project-details-value"><?php echo htmlspecialchars($project['department'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="project-details-item">
                            <span class="project-details-label">Availability:</span>
                            <span class="project-details-value"><?php echo htmlspecialchars($project['availability'] ?? 'N/A'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="project-details-footer">
                <a href="project-monitor.php" class="project-back-btn">
                    <i class='bx bx-arrow-back'></i> Back
                </a>
            </div>
        </div>
    </div>

    <?php if (!empty($project['images'])): ?>
        <div id="imageLightbox" class="lightbox">
            <span class="close-btn">&times;</span>
            <img class="lightbox-content" id="lightboxImage">
            <div class="lightbox-caption"></div>
        </div>
    <?php endif; ?>
</main>

<?php 
$pageScript = ' <script src="../scripts/project-details.js"></script>';
include '../includes/footer.php'; 
?>