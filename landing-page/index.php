<!-- 
<!DOCTYPE html>
<html lang="en">
    <head>
    
    // Network restriction check
    $allowed_network = "BEANS & DREAMS 5G";
    $current_network = exec("netsh wlan show interfaces | findstr SSID");
    
    if (!str_contains($current_network, $allowed_network)): 
        // Start styled denial page
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Restricted - InTrack</title>
    <link rel="stylesheet" href="./styles/style.css">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .access-denied {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            text-align: center;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .access-denied i {
            font-size: 60px;
            color: #ff4757;
            margin-bottom: 20px;
        }
        .network-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: left;
        }
        .action-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #4361ee;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    

    <section class="access-denied">
        <i class='bx bx-wifi-off'></i>
        <h1>Network Access Required</h1>
        
        <div class="network-info">
            <p><strong>You need to connect to:</strong></p>
            <p><i class='bx bx-wifi'></i> <?php echo htmlspecialchars($allowed_network); ?></p>
            
            <p><strong>Your current network:</strong></p>
            <p><i class='bx bx-network-chart'></i> <?php echo htmlspecialchars($current_network ?: 'Not detected'); ?></p>
        </div>

        <p>To access InTrack, please connect to the organization's WiFi network.</p>
        
        <div class="troubleshooting">
            <h3><i class='bx bx-help-circle'></i> Having trouble?</h3>
            <ol>
                <li>Check your WiFi settings</li>
                <li>Select "<?php echo htmlspecialchars($allowed_network); ?>" from available networks</li>
                <li>Contact IT support if needed</li>
            </ol>
        </div>

        <a href="#" class="action-button" onclick="window.location.reload()">
            <i class='bx bx-refresh'></i> Try Again After Connecting
        </a>
    </section>


</body>
</html>--->
<!-- php
    exit();
endif; -->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to InTrack!</title>
    <link rel="stylesheet" href="./styles/style.css">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <?php include 'header.php' ?>

    <section class="index-banner">
        <div class="container">
            <div class="index-banner-nav">
                <div class="index-banner-content">
                    <h4>WELCOME TO...</h4>
                    <h1>InTrack</h1>
                    <p>A platform designed to streamline the internship management process for students, educators,
                        and organizations. InTrack ensures efficient communication, centralized records, and real-time status updates
                        — all in one user-friendly dashboard.
                    </p>
                    <nav>
                        <ul>
                            <li><a href="sign.php" class="action">Get Started!</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <section class="why">
        <div class="container">
            <div class="why-nav">
                <h1>Why Choose InTrack?</h1>
                <div class="underline"></div>

                <div class="why-cards">
                    <div class="why-card">
                        <div class="card-content">
                            <i class='bx bx-laptop'></i>
                            <h3>For Interns</h3>
                            <h4>Interns can easily track their time-in and time-out, manage project assignments, and monitor progress all in one place. InTrack helps interns stay organized and focused throughout their internship journey.</h4>
                        </div>
                    </div>

                    <div class="why-card">
                        <div class="card-content">
                            <i class='bx  bx-like'></i>
                            <h3>Simple & Easier</h3>
                            <h4>InTrack is built for simplicity making it easy to clock in, manage tasks, and update progress with just a few clicks. No clutter, no confusion just a smooth and efficient experience for interns and supervisors.</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="how">
        <div class="container">
            <div class="how-nav">
                <h1>How It Works?</h1>
                <div class="underline"></div>

                <div class="how-cards">
                    <div class="how-card">
                        <div class="card-content">
                            <i class='bx bx-user-circle'></i>
                            <h3>Create Account</h3>
                            <h4>Sign up as an intern with your email to access InTrack's time and project management tools.</h4>
                        </div>
                    </div>

                    <div class="how-card">
                        <div class="card-content">
                            <i class='bx bx-search'></i>
                            <h3>Track Your Work</h3>
                            <h4>Log your time in/out, monitor project progress, and submit reports - all in one dashboard.</h4>
                        </div>
                    </div>

                    <div class="how-card">
                        <div class="card-content">
                            <i class="bx bx-link"></i>
                            <h3>Manage Projects</h3>
                            <h4>View all your assigned tasks, track deadlines, and communicate with supervisors directly through the platform.</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <?php include 'footer.php'; ?>
</body>

</html>