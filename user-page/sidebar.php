<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="styles/images/logo.png" alt="InTrack Logo">
            <h1>InTrack</h1>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'time.php' ? 'active' : ''; ?>">
                <a href="time.php" class="nav-link">
                    <i class="fas fa-search"></i>
                    <span>Time In/Out</span>
                </a>
            </li>
            <li class="nav-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['projects.php', 'add-project.php', 'view-project.php']) ? 'active' : ''; ?>">
                <a href="projects.php" class="nav-link">
                    <i class="fas fa-briefcase"></i>
                    <span>Projects</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename(basename($_SERVER['PHP_SELF'])) == 'notif.php'? 'active' : ''; ?>">
                <a href="notif.php" class="nav-link">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Notifications</span>
                </a>
            </li>
            <li class="nav-item <?php echo basename(basename($_SERVER['PHP_SELF'])) == 'account.php'? 'active' : ''; ?>">
                <a href="account.php" class="nav-link">
                    <i class="fas fa-cog"></i>
                    <span>Account Settings</span>
                </a>
            </li>
        </ul>
        
        <div class="logout-section">
            <button class="logout-btn" id="logoutBtn" onclick="logoutOnclick('../landing-page/login.php')">
                <i class="fas fa-sign-out-alt"></i>
                <a href="">Logout</a>
            </button>
        </div>
    </div>

    <script>
        function logoutOnclick(url) {
            window.location.href = url;
        }
    </script>
    <script src="./scripts/sidebar.js"></script>
</body>
</html>