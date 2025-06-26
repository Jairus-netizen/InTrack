<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/about.css">
    <link rel="stylesheet" href="./styles/style.css">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <?php include 'header.php' ?>

    <section class="dream-section">
        <div class="container">
            <div class="dream-wrapper">
                <div class="dream-content">
                    <h4 class="how-it-started">How It Started</h4>
                    <h1 class="dream-title">Our Dream is Bridging The Gap Between Students and Employers.</h1>
                    <div class="typing-section">
                        <div class="typing-container">
                            <p class="typing-text" id="typing-animation"></p>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const text = "This website was created with the vision of supporting career growth from the ground up—helping students take their first step into the professional world, while giving employers access to a pool of eager, skilled candidates. It's more than just job matching—it's about creating opportunities, building networks, and empowering futures.";
                            const element = document.getElementById('typing-animation');
                            let i = 0;
                            const speed = 10; // Typing speed in milliseconds

                            function typeWriter() {
                                if (i < text.length) {
                                    element.innerHTML += text.charAt(i);
                                    i++;
                                    setTimeout(typeWriter, speed);
                                } else {
                                    // Remove cursor when done
                                    element.style.borderRight = 'none';
                                }
                            }

                            // Start animation when section is in view
                            const observer = new IntersectionObserver((entries) => {
                                if (entries[0].isIntersecting) {
                                    typeWriter();
                                    observer.unobserve(entries[0].target);
                                }
                            }, {
                                threshold: 0.5
                            });

                            observer.observe(document.querySelector('.typing-section'));
                        });
                    </script>
                </div>

                <div class="dream-stats">
                    <div class="stats-image">
                        <!-- Replace with your actual image -->
                        <img src="styles/images/abt.png" alt="">
                    </div>
                    <div class="stats-counter">
                        <div class="counter-item">
                            <span class="counter" data-target="267">0</span>
                            <p>Active Interns</p>
                        </div>
                        <div class="counter-item">
                            <span class="counter" data-target="8395">0</span>
                            <p>Registered Interns</p>
                        </div>
                        <div class="counter-item">
                            <span class="counter" data-target="378">0</span>
                            <p>Verified Employers</p>
                        </div>
                        <div class="counter-item">
                            <span class="counter" data-target="19765">0</span>
                            <p>Completed Interns</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="vision-mission-section">
        <div class="vm-container">
            <div class="vm-column vision-column">
                <h4 class="vm-subtitle">Our Vision</h4>
                <h2 class="vm-title">Empowering Futures Through Opportunity</h2>
                <p class="vm-text">Our vision is to empower students and organizations by making internships and early career opportunities more accessible. We aim to shape a world where every student can grow, gain real-world experience, and kick-start their career with confidence.</p>
            </div>

            <div class="vm-column mission-column">
                <h4 class="vm-subtitle">Our Mission</h4>
                <h2 class="vm-title">Connecting Talent with Real-World Experience</h2>
                <p class="vm-text">Our mission is to create a trusted space where students find meaningful internships and employers discover passionate, driven individuals. By simplifying connections and building a strong, opportunity-rich ecosystem, we help shape career journeys and support long-term success.</p>
            </div>
        </div>
    </section>


    <section class="team-section">
        <div class="team-container">
            <h4 class="team-subtitle">Meet the Team</h4>
            <h2 class="team-title">The People Powering the Platform</h2>

            <div class="team-grid">
                <!-- Row 1 -->
                <div class="team-member">
                    <div class="member-image">
                        <img src="styles/images/cath.jpg" alt="Team Member">
                    </div>
                    <h3 class="member-name">Cathyhannel G. Gruezo</h3>
                </div>

                <div class="team-member">
                    <div class="member-image">
                        <img src="styles/images/matt.jpg" alt="Team Member">
                    </div>
                    <h3 class="member-name">Matthew T. Maruya</h3>
                </div>

                <div class="team-member">
                    <div class="member-image">
                        <img src="./styles/images/jairus.jpg" alt="Team Member">
                    </div>
                    <h3 class="member-name">Jairus Christian B. Bacsa</h3>
                </div>

                <!-- Row 2 -->
                <div class="team-member">
                    <div class="member-image">
                        <img src="./styles/images/keith.png" alt="Team Member">
                    </div>
                    <h3 class="member-name">Keith AJ O. Fernandez</h3>
                </div>

                <div class="team-member">
                    <div class="member-image">
                        <img src="./styles/images/pat.jpg" alt="Team Member">
                    </div>
                    <h3 class="member-name">Patrick Dela Paz</h3>
                </div>

                <div class="team-member">
                    <div class="member-image">
                        <img src="./styles/images/Hannesa.png" alt="Team Member">
                    </div>
                    <h3 class="member-name">Hanessa Kimberly Latina</h3>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <?php include 'footer.php'; ?>

    <script>
        // Counter Animation Script
        document.addEventListener('DOMContentLoaded', () => {
            const counters = document.querySelectorAll('.counter');
            const speed = 10000; // Animation duration in ms

            const animateCounters = () => {
                counters.forEach(counter => {
                    const target = +counter.getAttribute('data-target');
                    const count = +counter.innerText;
                    const increment = target / speed;

                    if (count < target) {
                        counter.innerText = Math.ceil(count + increment);
                        setTimeout(animateCounters, 1);
                    } else {
                        counter.innerText = target.toLocaleString(); // Adds commas for thousands
                    }
                });
            };

            // Trigger animation when section comes into view
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateCounters();
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.5
            });

            observer.observe(document.querySelector('.dream-section'));
        });
    </script>
</body>

</html>