<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../config/techpulse.php';
require_once __DIR__ . '/registerdb.php';
require_once __DIR__ . '/../../config/options.php';

if (!empty($_SESSION['StudentID'])) {
    header('Location: ../../dashboard/dashboard.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$formValues = [
    'firstname' => '',
    'lastname' => '',
    'studentid' => '',
    'course' => '',
    'yearlevel' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    }

    $firstName = trim($_POST['firstname'] ?? '');
    $lastName = trim($_POST['lastname'] ?? '');
    $studentId = trim($_POST['studentid'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $yearLevel = trim($_POST['yearlevel'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $formValues = [
        'firstname' => $firstName,
        'lastname' => $lastName,
        'studentid' => $studentId,
        'course' => $course,
        'yearlevel' => $yearLevel,
    ];

    if ($firstName === '' || mb_strlen($firstName) > 67) {
        $errors[] = 'Please enter a valid first name.';
    }

    if ($lastName === '' || mb_strlen($lastName) > 67) {
        $errors[] = 'Please enter a valid last name.';
    }

    if ($studentId === '' || !ctype_digit($studentId)) {
        $errors[] = 'Please enter a valid Student ID (numbers only).';
    }

    if (!array_key_exists($course, $allowedCourses)) {
        $errors[] = 'Please select a valid course.';
    }

    if (!in_array($yearLevel, $allowedYearLevels, true)) {
        $errors[] = 'Please select a valid year level.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $result = registerUser($conn, $studentId, $firstName, $lastName, $course, $yearLevel, $password);

        if ($result['success']) {
            unset($_SESSION['csrf_token']);
            header('Location: ../login/login.php?registered=1');
            exit;
        }

        $errors[] = $result['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script>
        document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light');
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Tech Pulse</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/auth.css">
    <link rel="stylesheet" href="register.css">
    <script src="../../assets/js/theme.js" defer></script>
    <script src="../../assets/js/password-toggle.js" defer></script>
    <script src="register_auth.js" defer></script>
</head>
<body>
    <button type="button" class="theme-toggle" aria-label="Toggle dark mode">
        <img src="../../assets/img/dark-mode.png" alt="Dark mode">
    </button>

    <div class="auth-topbar">
        <a href="../../index.php" class="logo">Tech Pulse</a>
    </div>

    <div class="auth-wrapper">
        <div class="auth-card wide">
            <div class="auth-header">
                <h1>Create your account</h1>
                <p>Register to start using the ComLab monitoring system.</p>
            </div>

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

            <form method="POST" action="register.php" id="registerForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="firstname">First Name</label>
                        <input type="text" id="firstname" name="firstname" maxlength="67" placeholder="Robert James"
                            value="<?= htmlspecialchars($formValues['firstname']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="lastname">Last Name</label>
                        <input type="text" id="lastname" name="lastname" maxlength="67" placeholder="Fischer"
                            value="<?= htmlspecialchars($formValues['lastname']) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="studentid">Student ID</label>
                    <input type="number" id="studentid" name="studentid" inputmode="numeric"
                        placeholder="25010992"
                        value="<?= htmlspecialchars($formValues['studentid']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="course">Course</label>
                    <select id="course" name="course" required>
                        <option value="" disabled <?= $formValues['course'] === '' ? 'selected' : '' ?>>-- Select Course --</option>
                        <?php foreach ($allowedCourses as $code => $label): ?>
                            <option value="<?= htmlspecialchars($code) ?>" <?= $formValues['course'] === $code ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="yearlevel">Year Level</label>
                    <select id="yearlevel" name="yearlevel" required>
                        <option value="" disabled <?= $formValues['yearlevel'] === '' ? 'selected' : '' ?>>-- Select Year Level --</option>
                        <?php foreach ($allowedYearLevels as $yearLevelOption): ?>
                            <option value="<?= htmlspecialchars($yearLevelOption) ?>" <?= $formValues['yearlevel'] === $yearLevelOption ? 'selected' : '' ?>>
                                <?= htmlspecialchars($yearLevelOption) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" minlength="8" required>
                        <button type="button" class="toggle-password" data-target="password">Show</button>
                    </div>
                    <p class="field-hint">At least 8 characters.</p>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="password-field">
                        <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
                        <button type="button" class="toggle-password" data-target="confirm_password">Show</button>
                    </div>
                    <p class="field-error" id="confirmError">Passwords do not match.</p>
                </div>

                <button type="submit" class="submit-btn">Create Account</button>
            </form>

            <p class="auth-switch">Already have an account? <a href="../login/login.php">Log in</a></p>

            <div class="back-link">
                <a href="../../index.php" class="back-home">&larr; Back to home</a>
            </div>
        </div>
    </div>
</body>
</html>
