<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../config/techpulse.php';
require_once __DIR__ . '/dashboarddb.php';

if (empty($_SESSION['StudentID'])) {
    header('Location: ../auth/login/login.php');
    exit;
}

$user = findUserProfile($conn, (string) $_SESSION['StudentID']);

if ($user === null) {
    $_SESSION = [];
    session_destroy();
    header('Location: ../auth/login/login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$memberSince = date('F j, Y', strtotime($user['created_at']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script>
        (function () {
            var theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Tech Pulse</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <script src="../assets/js/theme.js" defer></script>
</head>
<body>
                <li>
                <button class="theme-toggle" aria-label="Toggle dark mode">
                <img src="../../assets/img/dark-mode.png" alt="Dark mode">
                </button>
                </li>

    <div class="auth-topbar">
        <a href="../index.php" class="logo">Tech Pulse</a>
    </div>

    <div class="auth-wrapper">
        <div class="auth-card">

            <div class="auth-header">
            <h1>
                Welcome back, <?= htmlspecialchars($user['firstname']) ?>
                <img src="../assets/img/wave.png" alt="Wave">
            </h1>
                <p>Here's your ComLab account overview.</p>
            </div>

            <dl class="profile-list">
                <div class="profile-row">
                    <dt>Student ID</dt>
                    <dd><?= htmlspecialchars((string) $user['StudentID']) ?></dd>
                </div>
                <div class="profile-row">
                    <dt>Name</dt>
                    <dd><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></dd>
                </div>
                <div class="profile-row">
                    <dt>Course</dt>
                    <dd><?= htmlspecialchars($user['course']) ?></dd>
                </div>
                <div class="profile-row">
                    <dt>Year Level</dt>
                    <dd><?= htmlspecialchars($user['yearlevel']) ?></dd>
                </div>
                <div class="profile-row">
                    <dt>Member since</dt>
                    <dd><?= htmlspecialchars($memberSince) ?></dd>
                </div>
            </dl>

            <form method="POST" action="../auth/logout/logout.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <button type="submit" class="logout-btn">Log out</button>
            </form>

        </div>
    </div>

</body>
</html>
