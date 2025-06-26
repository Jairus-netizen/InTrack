<?php
session_start();
// Verify admin status immediately
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'admin') {
    header("Location: /intrack-cathy/landing-page/login.php");
    exit();
}

// Include database connection
require_once '/xampp/htdocs/intrack-cathy/user-page/database/dbconnection.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Basic validation
    $errors = [];
    
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (!empty($password) && $password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }

    if (empty($errors)) {
        // Prepare update query
        if (!empty($password)) {
            // Update with password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE accounts SET 
                      phone = ?, email = ?, first_name = ?, last_name = ?, password = ?
                      WHERE account_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('sssssi', $phone, $email, $firstName, $lastName, $hashedPassword, $_SESSION['user_id']);
        } else {
            // Update without password
            $query = "UPDATE accounts SET 
                      phone = ?, email = ?, first_name = ?, last_name = ?
                      WHERE account_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ssssi', $phone, $email, $firstName, $lastName, $_SESSION['user_id']);
        }

        if ($stmt->execute()) {
            // Update session variables
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            
            // Set success message
            $successMessage = "Profile updated successfully!";
        } else {
            $errors[] = "Failed to update profile";
        }
    }
}

// Fetch current admin details (whether after update or initial load)
$query = "SELECT first_name, last_name, email, phone FROM accounts WHERE account_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

$pageStyles = '<link rel="stylesheet" href="../styles/admin-profile.css">';
include '../includes/header.php';
?>

<!-- MAIN -->
<main>
    <div class="head-title">
        <div class="left">
            <h1>Admin Profile</h1>
            <ul class="breadcrumb">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><i class="bx bx-chevron-right"></i></li>
                <li><a class="active" href="#">Admin Profile</a></li>
            </ul>
        </div>
    </div>

    <div class="admin-profile-container">
        <div class="admin-view-card">
            <div class="admin-user-header">
                <h2>Admin Account</h2>
            </div>
        </div>
        
        <div class="admin-profile-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($successMessage)): ?>
                <div class="alert alert-success">
                    <p><?php echo htmlspecialchars($successMessage); ?></p>
                </div>
            <?php endif; ?>

            <div class="admin-profile-role">
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?></h1>
                    <span class="admin-profile-role-label">Administrator</span>
                </div>

                <div class="admin-profile-group">
                    <h3>Account Details</h3>
                    <form method="POST">
                        <div class="admin-form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" name="phone" class="admin-form-input" 
                                   value="<?php echo htmlspecialchars($admin['phone'] ?? ''); ?>" required>
                        </div>
                        <div class="admin-form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" class="admin-form-input" 
                                   value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" required>
                        </div>
                        <div class="admin-form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" name="first_name" class="admin-form-input" 
                                   value="<?php echo htmlspecialchars($admin['first_name'] ?? ''); ?>" required>
                        </div>
                        <div class="admin-form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" name="last_name" class="admin-form-input" 
                                   value="<?php echo htmlspecialchars($admin['last_name'] ?? ''); ?>" required>
                        </div>
                        <div class="admin-form-group">
                            <label for="password">New Password (leave blank to keep current)</label>
                            <div class="password-input-wrapper">
                                <input type="password" name="password" class="admin-form-input" placeholder="New password">
                            </div>
                        </div>
                        <div class="admin-form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <div class="password-input-wrapper">
                                <input type="password" name="confirm_password" class="admin-form-input" placeholder="Confirm new password">
                            </div>
                        </div>
                        <div class="admin-profile-footer">
                            <div class="admin-profile-action">
                                <button type="reset" class="admin-reset-btn">
                                    <i class="bx bx-reset"></i>Reset
                                </button>
                                <button type="submit" class="admin-save-btn">
                                    <i class="bx bx-save"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
$pageScripts = '<script src="../scripts/admin-profile.js"></script>';
include '../includes/footer.php'; 
?>