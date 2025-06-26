<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/signuser.css">
    <title>Sign Up</title>
    <style>
        .error-message {
            color: red;
            margin-bottom: 15px;
            padding: 10px;
            background: #ffeeee;
            border: 1px solid #ffcccc;
            border-radius: 4px;
        }

        .error-message ul {
            list-style-type: none;
            margin: 5px 0;
            padding-left: 20px;
        }

        .error-message li {
            margin-bottom: 5px;
        }

        input.error {
            border-color: red !important;
        }

        .success-message {
            color: green;
            margin-bottom: 15px;
            padding: 10px;
            background: #eeffee;
            border: 1px solid #ccffcc;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <section class="animated-signup">
        <div class="gradient-bg"></div>

        <div class="centered-container">
            <div class="form-container">
                <div class="signup-image">
                    <img src="styles/images/user.jpg" alt="Students collaborating">
                </div>

                <div class="signup-form-container">
                    <div class="form-logo">
                        <img src="styles/images/logo.png" alt="Company Logo">
                    </div>

                    <h1 class="form-title">Create account</h1>

                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        require_once './database/dbconnection.php';

                        // Get form data with trim to remove whitespace
                        $last_name = trim($_POST['last_name'] ?? '');
                        $first_name = trim($_POST['first_name'] ?? '');
                        $middle_name = !empty($_POST['middle_name']) ? trim($_POST['middle_name']) : null;
                        $suffix = !empty($_POST['suffix']) ? trim($_POST['suffix']) : null;
                        $phone = trim($_POST['phone'] ?? '');
                        $email = trim($_POST['email'] ?? '');
                        $password = $_POST['password'] ?? '';
                        $confirm_password = $_POST['confirm_password'] ?? '';
                        $school = !empty($_POST['school']) ? trim($_POST['school']) : null;
                        $birthday = !empty($_POST['birthday']) ? $_POST['birthday'] : null;

                        // Validate required fields
                        $errors = [];

                        if (empty($last_name)) $errors[] = 'Last name is required';
                        if (empty($first_name)) $errors[] = 'First name is required';
                        if (empty($phone)) $errors[] = 'Phone number is required';
                        if (empty($email)) $errors[] = 'Email is required';
                        if (empty($password)) $errors[] = 'Password is required';

                        // Optional: Add validation for school/birthday if they're required
                        // if (empty($school)) $errors[] = 'School is required';
                        // if (empty($birthday)) $errors[] = 'Birthday is required';

                        // Validate password match
                        if ($password !== $confirm_password) {
                            $errors[] = 'Passwords do not match';
                        }

                        // Validate password strength (optional)
                        if (strlen($password) < 8) {
                            $errors[] = 'Password must be at least 8 characters long';
                        }

                        // Validate email format
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $errors[] = 'Invalid email format';
                        }

                        // Validate birthday format if provided
                        if ($birthday && !strtotime($birthday)) {
                            $errors[] = 'Invalid birthday format';
                        }

                        if (empty($errors)) {
                            // Hash the password
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                            // Prepare SQL statement (updated to include school and birthday)
                            $stmt = $conn->prepare("INSERT INTO accounts 
                            (last_name, first_name, middle_name, suffix, phone, email, password, school, birthday) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

                            if ($stmt === false) {
                                echo '<div class="error-message">Database error: ' . htmlspecialchars($conn->error) . '</div>';
                            } else {
                                // Bind parameters (added school and birthday)
                                $stmt->bind_param(
                                    "sssssssss", // Added two more 's' for school (string) and birthday (string)
                                    $last_name,
                                    $first_name,
                                    $middle_name,
                                    $suffix,
                                    $phone,
                                    $email,
                                    $hashed_password,
                                    $school,
                                    $birthday
                                );

                                // Execute the query
                                if ($stmt->execute()) {
                                    echo '<div class="success-message">Registration successful! You can now <a href="login.php">login</a>.</div>';

                                    // Clear form fields on success
                                    echo '<script>
                                            document.querySelector(".signup-form").reset();
                                        </script>';
                                } else {
                                    if ($conn->errno == 1062) { // Duplicate entry error code
                                        echo '<div class="error-message">This email is already registered.</div>';
                                    } else {
                                        echo '<div class="error-message">Registration failed. Error: ' . htmlspecialchars($stmt->error) . '</div>';
                                    }
                                }
                                $stmt->close();
                            }
                        } else {
                            // Display all errors
                            echo '<div class="error-message"><ul>';
                            foreach ($errors as $error) {
                                echo '<li>' . htmlspecialchars($error) . '</li>';
                            }
                            echo '</ul></div>';
                        }
                        $conn->close();
                    }
                    ?>

                    <form class="signup-form" method="POST" action="">
                        <!-- Name Fields -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first-name">First Name <span class="required">*</span></label>
                                <input type="text" id="first-name" name="first_name" required
                                    value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="last-name">Last Name <span class="required">*</span></label>
                                <input type="text" id="last-name" name="last_name" required
                                    value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                            </div>
                        </div>

                        <!-- Optional Fields -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="middle-name">Middle Name <span class="optional">(If there is any)</span></label>
                                <input type="text" id="middle-name" name="middle_name"
                                    value="<?php echo isset($_POST['middle_name']) ? htmlspecialchars($_POST['middle_name']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="suffix">Suffix <span class="optional">(e.g. Jr.)</span></label>
                                <input type="text" id="suffix" name="suffix"
                                    value="<?php echo isset($_POST['suffix']) ? htmlspecialchars($_POST['suffix']) : ''; ?>">
                            </div>
                        </div>

                        <!-- School and Birthday Fields -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="school">School/University</label>
                                <input type="text" id="school" name="school"
                                    value="<?php echo isset($_POST['school']) ? htmlspecialchars($_POST['school']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="birthday">Birthday</label>
                                <input type="date" id="birthday" name="birthday"
                                    value="<?php echo isset($_POST['birthday']) ? htmlspecialchars($_POST['birthday']) : ''; ?>">
                            </div>
                        </div>

                        <!-- Contact Info -->
                        <div class="form-group">
                            <label for="phone">Phone no.</label>
                            <input type="tel" id="phone" name="phone" required
                                value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>

                        <!-- Password Fields -->
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>

                        <div class="form-group">
                            <label for="confirm-password">Confirm Password</label>
                            <input type="password" id="confirm-password" name="confirm_password" required>
                        </div>

                        <!-- Privacy Policy -->
                        <div class="form-checkbox">
                            <input type="checkbox" id="agree-terms" required>
                            <label for="agree-terms">I agree to the <a href="#">Privacy Policy</a></label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="signup-button">Sign up</button>

                        <!-- Login Link -->
                        <div class="login-link">
                            Already have an account? <a href="login.php">Log in</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</body>

</html>