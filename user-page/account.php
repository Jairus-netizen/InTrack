<?php
// Start session and check authentication
session_start();
require_once 'database/dbconnection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ./intrack-cathy/landing-page/login.php");
    exit();
}

// Get user information from database
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM accounts WHERE account_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Set default profile image path
$defaultImage = '/intrack-cathy/user-page/styles/images/default-profile.jpg';
$profileImage = isset($user['user_profile']) && !empty($user['user_profile']) ? 
    '/intrack-cathy/user-page/users-profile/' . $user['user_profile'] : 
    $defaultImage;

// Format full name
$fullName = $user['last_name'] . ', ' . $user['first_name'];
if (!empty($user['middle_name'])) {
    $fullName .= ' ' . $user['middle_name'];
}
if (!empty($user['suffix'])) {
    $fullName .= ' ' . $user['suffix'];
}

// Format birthdate if available
$birthdate = '';
if (!empty($user['birthday'])) {
    $birthdate = date('F j, Y', strtotime($user['birthday']));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="styles/account.css">
</head>
<body>
    <?php include 'sidebar.php'?>
    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard Page (default active) -->
        <div id="account" class="page-content active">
            <div class="header">
                <h1>Account Settings</h1>
            </div>
        
            <div class="account-container">
                <!-- Account Information Section -->
                <div class="account-info">
                    <div class="account-header">
                        <h2><?php echo htmlspecialchars($fullName); ?></h2>
                        <div class="account-id">Account ID: USR-<?php echo str_pad($user['account_id'], 5, '0', STR_PAD_LEFT); ?></div>
                    </div>

                    <div class="divider"></div>

                    <div class="info-section">
                        <div class="info-item">
                            <i class='bx bx-user'></i>
                            <div>
                                <div class="info-label">Personal Information</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class='bx bx-envelope'></i>
                            <div>
                                <div class="info-label">Email</div>
                                <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class='bx bx-phone'></i>
                            <div>
                                <div class="info-label">Contact</div>
                                <div class="info-value"><?php echo htmlspecialchars($user['phone']); ?></div>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class='bx bx-building-house'></i>
                            <div>
                                <div class="info-label">School</div>
                                <div class="info-value"><?php echo htmlspecialchars($user['school']); ?></div>
                            </div>
                        </div>
                        <?php if (!empty($birthdate)): ?>
                        <div class="info-item">
                            <i class='bx bx-cake'></i>
                            <div>
                                <div class="info-label">Birthdate</div>
                                <div class="info-value"><?php echo $birthdate; ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <button class="edit-btn" id="editProfileBtn">Edit Profile</button>
                </div>

                <!-- Profile Image Section -->
                <div class="profile-image-container" id="profileImageContainer">
                    <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Image" class="profile-image">
                    <input type="file" id="profileImageUpload" accept="image/*" style="display: none;">
                </div>

                <!-- Edit Form (hidden by default) -->
                <div class="edit-form-container" id="editFormContainer">
                    <h3>Edit Profile</h3>
                    <form id="profileForm" action="update-profile.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="firstName">First Name</label>
                            <input type="text" id="firstName" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name</label>
                            <input type="text" id="lastName" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="middleName">Middle Name</label>
                            <input type="text" id="middleName" name="middle_name" value="<?php echo htmlspecialchars($user['middle_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="suffix">Suffix</label>
                            <input type="text" id="suffix" name="suffix" value="<?php echo htmlspecialchars($user['suffix'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="phone">Contact</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="school">School</label>
                            <input type="text" id="school" name="school" value="<?php echo htmlspecialchars($user['school']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="birthdate">Birthdate</label>
                            <input type="date" id="birthdate" name="birthday" value="<?php echo htmlspecialchars($user['birthday'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="profileImage">Profile Image</label>
                            <input type="file" id="profileImage" name="profile_image" accept="image/*">
                        </div>
                        <div class="form-actions">
                            <button type="button" class="cancel-btn" id="cancelEditBtn">Cancel</button>
                            <button type="submit" class="save-btn" id="saveProfileBtn">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="scripts/account.js"></script>
</body>
</html>