<?php
// Start session and check authentication
session_start();
require_once 'database/dbconnection.php';
require_once 'notification-functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /intrack-cathy/login.php");
    exit();
}

$notifications = [];
$error_message = '';


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required = ['project_title', 'expected_completion', 'project_description'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Required field '$field' is missing");
            }
        }

        // Get form data with null coalescing
        $project_title = $_POST['project_title'] ?? '';
        $arrange_by = $_POST['arrange_by'] ?? '';
        $project_manager = $_POST['project_manager'] ?? '';
        $expected_completion = $_POST['expected_completion'] ?? '';
        $project_progress = $_POST['project_description'] ?? '';
        $team_lead = $_POST['team_lead'] ?? '';
        $department = $_POST['department'] ?? '';
        $availability = $_POST['availability'] ?? '';
        $status = 'in_progress'; // Default status

        // Handle image upload
        $images = '';
        if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "C:/xampp/htdocs/intrack-cathy/user-page/project-images/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // Validate image file
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = mime_content_type($_FILES['project_image']['tmp_name']);

            if (!in_array($file_type, $allowed_types)) {
                throw new Exception("Only JPG, PNG, and GIF images are allowed.");
            }

            $file_extension = pathinfo($_FILES['project_image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $filename;

            if (move_uploaded_file($_FILES['project_image']['tmp_name'], $target_file)) {
                // Store relative path in database
                $images = "/intrack-cathy/user-page/project-images/" . $filename;
            } else {
                throw new Exception("Failed to upload image.");
            }
        }

        // Insert into database using MySQLi
        $query = "INSERT INTO projects (
            user_id, project_title, arrange_by, project_manager, 
            expected_completion, project_progress, team_lead, 
            department, availability, status, images
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($query);
        $stmt->bind_param(
            "issssssssss",
            $_SESSION['user_id'],
            $project_title,
            $arrange_by,
            $project_manager,
            $expected_completion,
            $project_progress,
            $team_lead,
            $department,
            $availability,
            $status,
            $images
        );

        if (!$stmt->execute()) {
            throw new Exception("Database error: " . $stmt->error);
        }

        // Success - redirect back to add-project with success parameter
        header("Location: add-project.php?success=true");
        $projectId = $conn->insert_id;
        sendNewProjectNotification($_SESSION['user_id'], $project_title, $projectId);
        exit();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intrack | Add Project</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="styles/add-project.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <!-- Sidebar Navigation -->
    <?php include 'sidebar.php' ?>

    <!-- Main Content -->
    <div class="main-content">
        <div id="add-project" class="page-content active">
            <div class="header">
                <h1>Add New Project</h1>
                <div class="user-info">
                    
                    <h4>Hello!</h4>
                    <span><?php echo htmlspecialchars($_SESSION['first_name']); ?></span>
                </div>
            </div>

            <div class="add-project-content">
                <div class="container">
                    <div class="content-wrapper">
                        <form class="project-form-card" method="POST" action="add-project.php" enctype="multipart/form-data">
                            <div class="form-header">
                                <h2>New Project</h2>
                                <span class="project-status not-started">Not Started</span>
                            </div>

                            <div class="form-body">
                                <div class="form-row">
                                    <div class="form-group">
                                        <div class="image-upload-preview">
                                            <i class='bx bx-image-add'></i>
                                        </div>
                                        <label for="project-image-upload" class="custom-file-label">
                                            Upload Image
                                        </label>
                                        <input type="file" id="project-image-upload" name="project_image" accept="image/*" style="display: none;">

                                    </div>

                                    <div class="form-group">
                                        <h3>Details</h3>
                                        <div class="form-item">
                                            <span class="form-label">Project ID:</span>
                                            <input type="text" class="form-input" placeholder="Auto-generated" readonly>
                                        </div>
                                        <div class="form-item">
                                            <span class="form-label">Project Title:</span>
                                            <input type="text" name="project_title" class="form-input" placeholder="Enter project title" required>
                                        </div>
                                        <div class="form-item">
                                            <span class="form-label">Arranged by:</span>
                                            <input type="text" name="arrange_by" class="form-input" placeholder="Enter a person">
                                        </div>
                                        <div class="form-item">
                                            <span class="form-label">Project Manager:</span>
                                            <input type="text" name="project_manager" class="form-input" placeholder="Select project manager">
                                        </div>
                                        <div class="form-item">
                                            <span class="form-label">Expected Completion Date:</span>
                                            <input type="date" name="expected_completion" class="form-input" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <h3>Project Description</h3>
                                    <textarea name="project_description" class="form-textarea" placeholder="Enter detailed project description..." required></textarea>
                                </div>

                                <div class="form-row">
                                    <div class="form-group-bottom">
                                        <h3>Team and Collaboration</h3>
                                        <div class="form-item">
                                            <span class="form-label">Team Lead:</span>
                                            <input type="text" name="team_lead" class="form-input" placeholder="Select team lead">
                                        </div>
                                        <div class="form-item">
                                            <span class="form-label">Department:</span>
                                            <input type="text" name="department" class="form-input" placeholder="Enter department">
                                        </div>
                                        <div class="form-item">
                                            <span class="form-label">Availability:</span>
                                            <input type="text" name="availability" class="form-input" placeholder="e.g. Monday - Friday, 9:00 - 18:00">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-footer">
                                <button type="button" class="cancel-btn">
                                    <i class='bx bx-arrow-back'></i> Cancel
                                </button>
                                <div class="action-btns">
                                    <button type="submit" class="submit-btn">Create Project</button>
                                    <button type="button" class="reset-btn">Reset Form</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <script src="./scripts/add-project.js"></script>
        </div>
    </div>
</body>

</html>