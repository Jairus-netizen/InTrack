    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Page</title>
        <!-- STYLES -->
        <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link rel="stylesheet" href="../styles.css">
        <link rel="stylesheet" href="../styles/notication-dropdown.css">
        <link rel="stylesheet" href="../styles/admin-dropdown.css">
        <?php if (isset($pageStyles)) echo $pageStyles; ?>
    </head>

    <body>
        <?php

        // Prevent caching of protected pages
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        // Include database connection
        require_once  '/xampp/htdocs/intrack-cathy/user-page/database/dbconnection.php';
        // Redirect to login if not authenticated
        if (!isset($_SESSION['user_id'])) {
            header("Location: /intrack-cathy/landing-page/login.php");
            exit();
        }

        // Fetch user details if not in session
        if (isset($_SESSION['user_id']) && (empty($_SESSION['first_name']) || empty($_SESSION['last_name']))) {
            $query = "SELECT first_name, last_name FROM accounts WHERE account_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($user = mysqli_fetch_assoc($result)) {
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
            } else {
                // Handle case where user isn't found in database
                $_SESSION['first_name'] = 'Admin';
                $_SESSION['last_name'] = '';
            }
            mysqli_stmt_close($stmt);
        }
        ?>
        <!-- SIDEBAR -->
        <section id="sidebar">
            <div class="Inlogo">
                <a href="#" class="logo-img">
                    <img src="../styles/images/logo.png" alt="Logo">
                </a>
                <a href="dashboard.php" class="logo-text">InTrack</a>
            </div>
            <ul class="side-menu-top">
                <li data-page="dashboard" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['dashboard.php', 'admin_profile.php', 'all-notification.php']) ? 'active' : ''; ?>">
                    <a href="dashboard.php">
                        <i class="bx bxs-dashboard"></i>
                        <span class="text">Dashboard</span>
                    </a>
                </li>
                <li data-page="interns" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['users.php', 'edit_user.php']) ?
                                                    'active' : ''; ?>">
                    <a href="interns.php">
                        <i class="bx bxs-user"></i>
                        <span class="text">Interns</span>
                    </a>
                </li>
                <li data-page="project-monitor" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['project-monitor.php', 'approvals_view.php']) ? 'active' : ''; ?>">
                    <a href="project-monitor.php">
                        <i class='bx bx-clipboard'></i>
                        <span class="text">Project Monitor</span>
                    </a>
                </li>
                <li data-page="time-log" class="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['project-monitor.php', 'approvals_view.php']) ? 'active' : ''; ?>">
                    <a href="time-log.php">
                        <i class='bx bx-time'></i>
                        <span class="text">Time Log</span>
                    </a>
                </li>
            </ul>
            <ul class="side-menu-bottom">
                <li data-page="archive" class="<?php echo basename($_SERVER['PHP_SELF']) == 'archive.php' ? 'active' : ''; ?>">
                    <a href="archive.php">
                        <i class="bx bxs-archive"></i>
                        <span class="text">Archive</span>
                    </a>
                </li>
                <li data-page="logout">
                    <a href="/intrack-cathy/admin-page/admin/logout.php" class="logout-link">
                        <i class="bx bxs-log-out"></i>
                        <span class="text">Logout</span>
                    </a>
                </li>
            </ul>
        </section>

        <!-- CONTENT -->
        <section id="content">
            <!-- NAVBAR -->
            <nav>
                <i class="bx bx-menu toggle-sidebar"></i>
                <a href="#" class="nav-link">Categories</a>
                <!-- Notification Dropdown -->
                <div class="notification-dropdown" id="notification-dropdown">
                    <div class="notification-header">
                        <h4>Notifications</h4>
                        <a href="#" class="mark-all-read">Mark all as read</a>
                    </div>
                    <div class="notification-list">
                        <div class="notification-item unread">
                            <div class="notification-icon">
                                <i class='bx bxs-user-plus'></i>
                            </div>
                            <div class="notification-content">
                                <p><strong>John Doe</strong> sent you a connection request</p>
                                <span class="notification-time">2 mins ago</span>
                            </div>
                        </div>
                        <div class="notification-item unread">
                            <div class="notification-icon">
                                <i class='bx bxs-message-alt'></i>
                            </div>
                            <div class="notification-content">
                                <p><strong>Sarah Smith</strong> sent you a message</p>
                                <span class="notification-time">15 mins ago</span>
                            </div>
                        </div>
                        <div class="notification-item">
                            <div class="notification-icon">
                                <i class='bx bxs-badge-check'></i>
                            </div>
                            <div class="notification-content">
                                <p>Your job posting for <strong>Web Developer</strong> was approved</p>
                                <span class="notification-time">1 hour ago</span>
                            </div>
                        </div>
                        <div class="notification-item">
                            <div class="notification-icon">
                                <i class='bx bxs-calendar'></i>
                            </div>
                            <div class="notification-content">
                                <p>Interview scheduled with <strong>Tech Solutions Inc.</strong> tomorrow</p>
                                <span class="notification-time">3 hours ago</span>
                            </div>
                        </div>
                        <div class="notification-item">
                            <div class="notification-icon">
                                <i class='bx bxs-group'></i>
                            </div>
                            <div class="notification-content">
                                <p><strong>5 new applicants</strong> for your job posting</p>
                                <span class="notification-time">1 day ago</span>
                            </div>
                        </div>
                    </div>
                    <div class="notification-footer">
                        <a href="all-notification.php" class="view-all">View All Notifications</a>
                    </div>
                </div>
                <a href="#" class="profile">
                    <h4>
                        <?php
                        if (!empty($_SESSION['first_name'])) {
                            echo htmlspecialchars($_SESSION['first_name']);
                            if (!empty($_SESSION['last_name'])) {
                                echo ' ' . htmlspecialchars($_SESSION['last_name']);
                            }
                        } else {
                            echo 'Admin';
                        }
                        ?>
                    </h4>
                </a>
                <!-- And in the profile dropdown: -->
                <!-- <div class="profile-info">
                    <h4></h4>
                    <span>Administrator</span>
                </div> -->
                <div class="profile-dropdown" id="profile-dropdown">
                    <div class="profile-header">
                        <div class="profile-info">
                            <h4><?php
                        if (isset($_SESSION['first_name'])) {
                            echo htmlspecialchars($_SESSION['first_name']);
                            if (isset($_SESSION['last_name'])) {
                                echo ' ' . htmlspecialchars($_SESSION['last_name']);
                            }
                        } else {
                            echo 'Admin';
                        }
                        ?></h4>
                            <span>Administrator</span>
                        </div>
                    </div>
                    <div class="profile-footer">
                        <a href="admin_profile.php" class="view-profile">View Profile</a>
                    </div>
                </div>
                <!-- <div class="profile-footer">
                    <a href="admin_profile.php" class="view-profile">View Profile</a>
                </div> -->
            </nav>