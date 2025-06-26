<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/style.css">
    <script src="./scripts/header.js"></script>
</head>

<body>
    <section class="header">
        <div class="header-container">
            <img src="styles/images/logo.png" alt="">

            <div class="header-wrap">
                <div class="header-nav">
                    <a href="index.php"><button class="nav-btn <?php if (basename($_SERVER['PHP_SELF']) == 'index.php') echo 'active'; ?>">HOME</button></a>
                    <a href="about.php"><button class="nav-btn <?php if (basename($_SERVER['PHP_SELF']) == 'about.php') echo 'active'; ?>">ABOUT US</button></a>
                    <a href="contact.php"><button class="nav-btn <?php if (basename($_SERVER['PHP_SELF']) == 'contact.php') echo 'active'; ?>">CONTACT US</button></a>
                    <div class="nav-right">
                        <div class="log-buttons">
                            <button class="login-btn" onclick="window.location.href='login.php'">LogIn/SignUp</button>
                        </div>
                    </div>
                </div>
                <div class="menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </section>
</body>

</html>