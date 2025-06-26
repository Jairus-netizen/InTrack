<?php
session_start();
$pageStyles = '<link rel="stylesheet" href="../styles/edit_interns.css">';
include '../includes/header.php';
include '/xampp/htdocs/intrack-cathy/user-page/database/dbconnection.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: interns.php");
    exit();
}

$id = intval($_GET['id']);

// Fetch intern data
$query = "SELECT * FROM accounts WHERE account_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$intern = mysqli_fetch_assoc($result);

if (!$intern) {
    header("Location: interns.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $lastName = mysqli_real_escape_string($conn, $_POST['lastname']);
    $firstName = mysqli_real_escape_string($conn, $_POST['firstname']);
    $middleName = mysqli_real_escape_string($conn, $_POST['middlename']);
    $suffix = mysqli_real_escape_string($conn, $_POST['suffix']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['contact']);
    $school = mysqli_real_escape_string($conn, $_POST['school']);
    $birthday = mysqli_real_escape_string($conn, $_POST['birthday']);
    
    // Update query
    $updateQuery = "UPDATE accounts SET 
                    last_name = ?, 
                    first_name = ?, 
                    middle_name = ?, 
                    suffix = ?, 
                    email = ?, 
                    phone = ?, 
                    school = ?, 
                    birthday = ? 
                    WHERE account_id = ?";
    
    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, 'ssssssssi', 
        $lastName, 
        $firstName, 
        $middleName, 
        $suffix, 
        $email, 
        $phone, 
        $school, 
        $birthday, 
        $id
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $success = "Intern information updated successfully!";
        // Refresh the data
        $query = "SELECT * FROM accounts WHERE account_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $intern = mysqli_fetch_assoc($result);
    } else {
        $error = "Error updating intern information: " . mysqli_error($conn);
    }
}
?>

<!-- MAIN -->
<main>
    <div class="head-title">
        <div class="left">
            <h1>Interns Accounts</h1>
            <ul class="breadcrumb">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><i class="bx bx-chevron-right"></i></li>
                <li><a href="interns.php">Interns</a></li>
                <li><i class="bx bx-chevron-right"></i></li>
                <li><a class="active" href="#">Edit Intern</a></li>
            </ul>
        </div>
    </div>

    <div class="edit-interns-container">
        <div class="edit-view-card">
            <div class="edit-interns-header">
                <h2>Edit Intern Account</h2>
                <span class="interns-status <?= $intern['is_active'] ? 'active' : 'inactive' ?>">
                    <?= $intern['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success" style="padding: 15px; background: #e6f7ee; color: #00a651; margin: 0 25px 25px; border-radius: 5px;">
                    <?= $success ?>
                </div>
            <?php elseif (isset($error)): ?>
                <div class="alert alert-error" style="padding: 15px; background: #ffebee; color: #f44336; margin: 0 25px 25px; border-radius: 5px;">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="edit_interns.php?id=<?= $id ?>">
                <div class="edit-interns-body">
                    <div class="edit-interns-row">
                        <div class="edit-interns-group">
                            <h3>Basic Information</h3>
                            <div class="edit-interns-item">
                                <div class="interns-form-group">
                                    <label for="lastname">Lastname</label>
                                    <input type="text" name="lastname" class="interns-form-input" value="<?= htmlspecialchars($intern['last_name']) ?>" required>
                                </div>
                                <div class="interns-form-group">
                                    <label for="firstname">Firstname</label>
                                    <input type="text" name="firstname" class="interns-form-input" value="<?= htmlspecialchars($intern['first_name']) ?>" required>
                                </div>
                                <div class="interns-form-group">
                                    <label for="middlename">Middlename</label>
                                    <input type="text" name="middlename" class="interns-form-input" value="<?= htmlspecialchars($intern['middle_name'] ?? '') ?>">
                                </div>
                                <div class="interns-form-group">
                                    <label for="suffix">Suffix</label>
                                    <input type="text" name="suffix" class="interns-form-input" value="<?= htmlspecialchars($intern['suffix'] ?? '') ?>">
                                </div>
                                <div class="interns-form-group">
                                    <label for="birthday">Birthday</label>
                                    <input type="date" name="birthday" class="interns-form-input" value="<?= htmlspecialchars($intern['birthday'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="edit-interns-group">
                                <h3>Account Details</h3>
                                <div class="interns-form-group">
                                    <label for="email">Email</label>
                                    <input type="email" name="email" class="interns-form-input" value="<?= htmlspecialchars($intern['email']) ?>" required>
                                </div>
                                <div class="interns-form-group">
                                    <label for="contact">Contact No.</label>
                                    <input type="text" name="contact" class="interns-form-input" value="<?= htmlspecialchars($intern['phone']) ?>" required>
                                </div>
                                <div class="interns-form-group">
                                    <h3>Intern School</h3>
                                    <label for="school">School</label>
                                    <input type="text" name="school" class="interns-form-input" value="<?= htmlspecialchars($intern['school'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="edit-interns-footer">
                    <a href="interns.php" class="edit-bck-btn">
                       <i class='bx bx-arrow-back'></i>Back
                    </a>
                    <div class="edit-interns-action">
                        <button type="reset" class="edit-interns-reset">
                            <i class='bx bx-reset'></i>Reset
                        </button>
                        <button type="submit" class="edit-interns-update">
                            <i class='bx bx-save'></i>Update
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

<?php 
mysqli_close($conn);
include '../includes/footer.php'; 
?>