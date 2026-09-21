<!DOCTYPE html>
<html lang="en" class="bg-softGray">
<head>
    <meta charset="UTF-8">
    <script>
        (function () {
            var theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tech Pulse - Computer Laboratory Monitoring System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/theme.js" defer></script>
    <script src="assets/js/script.js" defer></script>

</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <span class="logo-full">Tech Pulse - Comlab Monitoring System </span>
                <span class="logo-short">Tech Pulse</span>
            </div>
            <ul class="nav-menu" id="nav-menu">
                <li><a href="#home">Home</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#how-it-works">How It Works</a></li>
                <li><a href="#benefits">Benefits</a></li>
                <li><a href="#cta">Register</a></li>
                <li><a href="auth/login/login.php">Login</a></li>
                <li>
                <button class="theme-toggle" aria-label="Toggle dark mode">
                <img src="assets/img/dark-mode.png" alt="Dark mode">
                </button>
                </li>
            </ul>
            <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="nav-menu">
                <span class="nav-toggle-bar"></span>
                <span class="nav-toggle-bar"></span>
                <span class="nav-toggle-bar"></span>
            </button>
        </div>
    </nav>

<section id="home" class="hero">
    <div class="hero-container">

        <div class="hero-content animate-on-scroll">
            <h1>
                Tech Pulse ComLab <span class="highlight">Monitoring</span><br>
                System
            </h1>

            <p>
                Simplify computer laboratory management with efficient PC monitoring, student session tracking, and organized laboratory records. Keep track of computer availability and usage with ease.
            </p>

            <div class="hero-buttons">
                <a href="#cta" class="primary-cta">Register Free</a>
                <a href="#features" class="secondary-cta">View Features</a>
            </div>
        </div>

        <div class="hero-image animate-on-scroll">
            <img src="assets/img/hero-image.jpg" alt="Tech Pulse ComLab Monitoring System Preview" />
        </div>

    </div>
</section>

    <section id="features" class="section">
        <h2 class="section-title animate-on-scroll">Everything You Need for Modern Laboratory Management</h2>
        <div class="features-grid">
            <div class="feature-card animate-on-scroll">
            <div class="feature-icon">
                <img src="assets/img/computer.png" alt="Computer">
            </div>
                <h3>Computer Status Monitoring</h3>
                <p>Monitor the status of laboratory computers in one place. Easily identify which computers are working, available, occupied, or unavailable for better laboratory management.</p>
            </div>
            <div class="feature-card animate-on-scroll">
            <div class="feature-icon">
                <img src="assets/img/tracking.png" alt="Tracking">
            </div>
                <h3>Student Session Tracking</h3>
                <p>Keep accurate records of students using the laboratory, including their assigned computer, course and year, time in, and time out for organized session monitoring.</p>
            </div>
        </div>
    </section>

    <section id="how-it-works" class="section">
        <h2 class="section-title animate-on-scroll">How It Works (3 Minutes Setup)</h2>
        <div class="steps-container">
            <div class="step animate-on-scroll">
                <div class="step-number">1</div>
                <h3>Register</h3>
                <p>Set up your account and provide your student information to access the computer laboratory monitoring system.</p>
            </div>
            <div class="step animate-on-scroll">
                <div class="step-number">2</div>
                <h3>Select a Computer</h3>
                <p>Choose an available laboratory computer and record your assigned PC number before starting your laboratory session.</p>
            </div>
            <div class="step animate-on-scroll">
                <div class="step-number">3</div>
                <h3>Monitor Your Session</h3>
                <p>Track your laboratory session through recorded time in and time out while keeping computer usage organized and easy to monitor.</p>
            </div>
        </div>
    </section>

    <section id="benefits" class="section">
        <h2 class="section-title animate-on-scroll">Why Choose Tech Pulse?</h2>
        <div class="benefits-container">
            <div class="benefit-item animate-on-scroll">
            <div class="benefit-icon">
                <img src="assets/img/energy.png" alt="Energy">
            </div>
                <div class="benefit-text">
                    <h4>Efficient Laboratory Management</h4>
                    <p>Reduce manual monitoring and make it easier to manage students, computer assignments, and laboratory sessions.</p>
                </div>
            </div>
            <div class="benefit-item animate-on-scroll">
            <div class="benefit-icon">
                <img src="assets/img/records.png" alt="Records">
            </div>
                <div class="benefit-text">
                    <h4>Accurate Laboratory Records</h4>
                    <p>Maintain organized records of student sessions, computer usage, PC status, and time in and time out.</p>
                </div>
            </div>
            <div class="benefit-item animate-on-scroll">
            <div class="benefit-icon">
                <img src="assets/img/management.png" alt="Management">
            </div>
                <div class="benefit-text">
                    <h4>Better Computer Management</h4>
                    <p>Quickly identify available and occupied computers, helping students and laboratory staff manage resources efficiently.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="cta" class="cta-section animate-on-scroll">
        <h2>Manage Your Computer Laboratory<br>Smarter Today</h2>
        <p>Join Tech Pulse and simplify computer laboratory monitoring. Keep track of students, computer usage, and laboratory sessions in one organized system.</p>
        <a href="auth/register/register.php" class="primary-cta">Register for Free</a>
    </section>

    <footer class="footer">
        <ul class="footer-links">
            <li><a href="#features">Features</a></li>
            <li><a href="#how-it-works">How It Works</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#">Privacy</a></li>
            <li><a href="#">Terms</a></li>
            <li><a href="#">Support</a></li>
        </ul>
        <p>&copy; 2026 Tech Pulse. Computer Laboratory Monitoring System.</p>
    </footer>

</body>
</html>