<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../config/techpulse.php';
require_once __DIR__ . '/logindb.php';

if (!empty($_SESSION['is_admin'])) {
    header('Location: ../../admindashboard/admindashboard.php');
    exit;
}

if (!empty($_SESSION['StudentID'])) {
    header('Location: ../../dashboard/dashboard.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$old = ['studentid' => ''];
$justRegistered = isset($_GET['registered']) && $_GET['registered'] === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    }

    $studentId = trim($_POST['studentid'] ?? '');
    $password  = $_POST['password'] ?? '';

    $old = ['studentid' => $studentId];

    if (empty($errors)) {

        if (hash_equals(ADMIN_USERNAME, $studentId)) {
            if ($password !== '' && password_verify($password, ADMIN_PASSWORD_HASH)) {
                session_regenerate_id(true);
                $_SESSION['is_admin'] = true;
                unset($_SESSION['csrf_token']);

                header('Location: ../../admindashboard/admindashboard.php');
                exit;
            }

            $errors[] = 'Invalid Student ID or password.';
        } else {
            if ($studentId === '' || !ctype_digit($studentId)) {
                $errors[] = 'Please enter a valid Student ID.';
            }
            if ($password === '') {
                $errors[] = 'Please enter your password.';
            }

            if (empty($errors)) {
                $user = findUserByStudentId($conn, $studentId);

                if ($user && password_verify($password, $user['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['StudentID'] = $user['StudentID'];
                    $_SESSION['firstname'] = $user['firstname'];
                    $_SESSION['lastname']  = $user['lastname'];
                    unset($_SESSION['csrf_token']); // one-time use

                    header('Location: ../../dashboard/dashboard.php');
                    exit;
                }

                $errors[] = 'Invalid Student ID or password.';
            }
        }
    }
}
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
    <title>Login - Tech Pulse</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="login.css">
    <script src="../../assets/js/theme.js" defer></script>
    <script src="login_auth.js" defer></script>
</head>
<body>
                <li>
                <button class="theme-toggle" aria-label="Toggle dark mode">
                <img src="../../assets/img/dark-mode.png" alt="Dark mode">
                </button>
                </li>

    <div class="auth-topbar">
        <a href="../../index.php" class="logo">Tech Pulse</a>
    </div>

    <div class="auth-wrapper">
        <div class="auth-card">

            <div class="auth-header">
                <h1>Welcome back</h1>
                <p>Log in to access the ComLab monitoring system.</p>
            </div>

            <?php if ($justRegistered && empty($errors)): ?>
                <div class="alert alert-success">
                    Account created successfully. You can now log in.
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <strong>Please fix the following:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" id="loginForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="form-group">
                    <label for="studentid">Student ID</label>
                    <input type="text" id="studentid" name="studentid" inputmode="numeric"
                           value="<?= htmlspecialchars($old['studentid']) ?>" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" required>
                        <button type="button" class="toggle-password" data-target="password">Show</button>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Log In</button>
            </form>

            <p class="auth-switch">Don't have an account? <a href="../register/register.php">Register</a></p>

            <div style="text-align:center;">
                <a href="../../index.php" class="back-home">&larr; Back to home</a>
            </div>
        </div>
    </div>

</body>
</html>