<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/contac.css">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .alert {
            padding: 15px;
            margin: 20px auto;
            max-width: 800px;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            animation: fadeIn 0.5s, fadeOut 0.5s 4.5s;
            animation-fill-mode: forwards;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @keyframes fadeIn {
            from { opacity: 0; top: 0; }
            to { opacity: 1; top: 20px; }
        }

        @keyframes fadeOut {
            from { opacity: 1; top: 20px; }
            to { opacity: 0; top: 0; }
        }
    </style>
</head>

<body>
    <?php 
    if (isset($_GET['status'])) {
        if ($_GET['status'] == 'success') {
            echo '<div class="alert alert-success">Your message has been sent successfully!</div>';
        } elseif ($_GET['status'] == 'error') {
            $error_message = $_GET['message'] ?? 'An error occurred while sending your message.';
            echo '<div class="alert alert-danger">' . htmlspecialchars($error_message) . '</div>';
        }
    }
    ?>
    
    <?php include 'header.php' ?>
    
    <section class="banner-contact">
        <div class="container">
            <div class="wrapper">
                <h1>GET IN TOUCH!</h1>
                <div class="contac-wrap">
                    <div class="cont">
                        <div class="contact-content">
                            <i class='bx  bx-envelope'></i>
                            <h1>EMAIL</h1>
                            <h4>Reach out Via E-mail</h4>
                            <h3>intrack@gmail.com</h3>
                        </div>
                    </div>

                    <div class="cont">
                        <div class="contact-content">
                            <i class='bx  bx-phone'></i>
                            <h1>PHONE</h1>
                            <h4>Give us a call</h4>
                            <h3>+63 91234 123 6789</h3>
                        </div>
                    </div>

                    <div class="cont">
                        <div class="contact-content">
                            <i class='bx  bx-map'></i>
                            <h1>ADDRESS</h1>
                            <h4>Here's where you can find us!</h4>
                            <h3>Laguna. Philippines</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="msg">
        <div class="container">
            <div class="msg-wrapper">
                <div class="msg-desc">
                    <h1>Message</h1>
                    <p>This form is intended for general inquiries, support concerns, or questions related to our platform and services. If you're a student seeking assistance, an employer with questions about using our platform, or simply someone who wants to know more, feel free to reach out here. For internship applications or job-related submissions, please use the designated sections of the website to ensure proper processing. Our team will review your message and get back to you as soon as possible.</p>
                </div>
                <form action="submit_message.php" method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first-name">First Name</label>
                            <input type="text" id="first-name" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="last-name">Last Name</label>
                            <input type="text" id="last-name" name="last_name" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" required></textarea>
                    </div>

                    <button type="submit">Send Message</button>
                </form>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <?php include 'footer.php'; ?>
</body>

</html>