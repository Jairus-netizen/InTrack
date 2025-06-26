    <?php
    session_start();
    require_once 'database/dbconnection.php';

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("HTTP/1.1 401 Unauthorized");
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }

    $user_id = $_SESSION['user_id'];

    // Set upload directory - using absolute server path
    $targetDir = $_SERVER['DOCUMENT_ROOT'] . '/intrack-cathy/user-page/users-profile/';

    // Create directory if it doesn't exist
    if (!file_exists($targetDir)) {
        if (!mkdir($targetDir, 0755, true)) {
            echo json_encode(['success' => false, 'message' => 'Server configuration error']);
            exit();
        }
    }

    // Check if directory is writable
    if (!is_writable($targetDir)) {
        echo json_encode(['success' => false, 'message' => 'Server configuration error']);
        exit();
    }

    // Initialize variables
    $fileName = null;
    $oldImage = null;

    // Handle image upload if present
    if (!empty($_FILES['profile_image']['name']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        // Sanitize filename
        $originalName = basename($_FILES["profile_image"]["name"]);
        $fileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.\-]/', '_', $originalName);
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        
        // Allow certain file formats
        $allowTypes = ['jpg', 'png', 'jpeg', 'gif'];
        
        if (!in_array($fileType, $allowTypes)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, JPEG, GIF allowed']);
            exit();
        }

        // Check file size (max 2MB)
        if ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 2MB']);
            exit();
        }
        
        // Get old image filename before updating
        $stmt = mysqli_prepare($conn, "SELECT user_profile FROM accounts WHERE account_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $oldImage = mysqli_fetch_assoc($result)['user_profile'];
        
        // Attempt to move the file
        if (!move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFilePath)) {
            echo json_encode(['success' => false, 'message' => 'Failed to save file']);
            exit();
        }
    }

    // Validate required fields
    $requiredFields = ['first_name', 'last_name', 'email'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
            exit();
        }
    }

    // Validate email format
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit();
    }

    // Prepare update query
    $query = "UPDATE accounts SET 
            first_name = ?,
            last_name = ?,
            middle_name = ?,
            suffix = ?,
            email = ?,
            phone = ?,
            school = ?,
            birthday = ?" . 
            ($fileName ? ", user_profile = ?" : "");

    $params = [
        $_POST['first_name'] ?? '',
        $_POST['last_name'] ?? '',
        $_POST['middle_name'] ?? '',
        $_POST['suffix'] ?? '',
        $_POST['email'] ?? '',
        $_POST['phone'] ?? '',
        $_POST['school'] ?? '',
        $_POST['birthday'] ?? ''
    ];

    if ($fileName) {
        $params[] = $fileName;
    }

    $query .= " WHERE account_id = ?";
    $params[] = $user_id;

    // Prepare and execute the statement
    $stmt = mysqli_prepare($conn, $query);
    $types = str_repeat('s', count($params));
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    $success = mysqli_stmt_execute($stmt);

    if ($success) {
        // Delete old profile image if it exists and isn't the default
        if ($oldImage && file_exists($targetDir . $oldImage) && strpos($oldImage, 'default-profile.jpg') === false) {
            unlink($targetDir . $oldImage);
        }
        
        // Return success with new image path if changed
        $response = [
            'success' => true,
            'message' => 'Profile updated successfully'
        ];
        
        if ($fileName) {
            $response['newImage'] = '/intrack-cathy/user-page/users-profile/' . $fileName;
        }
        
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    }

    exit();
    ?>