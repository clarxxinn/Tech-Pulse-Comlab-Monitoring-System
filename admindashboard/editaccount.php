<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../config/techpulse.php';
require_once __DIR__ . '/../config/options.php';
require_once __DIR__ . '/admindashboarddb.php';
require_once __DIR__ . '/../dashboard/dashboarddb.php';

if (empty($_SESSION['is_admin'])) {
    header('Location: ../auth/login/login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$studentId = trim($_GET['id'] ?? $_POST['studentid'] ?? '');

if ($studentId === '' || !ctype_digit($studentId)) {
    $_SESSION['admin_flash'] = ['type' => 'error', 'message' => 'Invalid account.'];
    header('Location: admindashboard.php');
    exit;
}

$user = findUserProfile($conn, $studentId);

if ($user === null) {
    $_SESSION['admin_flash'] = ['type' => 'error', 'message' => "Account {$studentId} was not found."];
    header('Location: admindashboard.php');
    exit;
}

$errors = [];
$old = [
    'firstname' => $user['firstname'],
    'lastname'  => $user['lastname'],
    'course'    => $user['course'],
    'yearlevel' => $user['yearlevel'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    }

    $firstname   = trim($_POST['firstname'] ?? '');
    $lastname    = trim($_POST['lastname'] ?? '');
    $course      = trim($_POST['course'] ?? '');
    $yearlevel   = trim($_POST['yearlevel'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';

    $old = compact('firstname', 'lastname', 'course', 'yearlevel');

    if ($firstname === '' || mb_strlen($firstname) > 67) {
        $errors[] = 'Please enter a valid first name.';
    }
    if ($lastname === '' || mb_strlen($lastname) > 67) {
        $errors[] = 'Please enter a valid last name.';
    }
    if (!array_key_exists($course, $allowedCourses)) {
        $errors[] = 'Please select a valid course.';
    }
    if (!in_array($yearlevel, $allowedYearLevels, true)) {
        $errors[] = 'Please select a valid year level.';
    }
    if ($newPassword !== '' && strlen($newPassword) < 8) {
        $errors[] = 'New password must be at least 8 characters long (or leave it blank to keep the current one).';
    }

    if (empty($errors)) {
        $result = adminUpdateUser(
            $conn,
            $studentId,
            $firstname,
            $lastname,
            $course,
            $yearlevel,
            $newPassword !== '' ? $newPassword : null
        );

        if ($result['success']) {
            $_SESSION['admin_flash'] = ['type' => 'success', 'message' => "Account {$studentId} has been updated."];
            header('Location: admindashboard.php');
            exit;
        }

        $errors[] = $result['error'];
    }
}

$memberSince = date('F j, Y', strtotime($user['created_at']));
$csrfToken = $_SESSION['csrf_token'];
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
    <title>Edit Account - Tech Pulse</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admindashboard.css">
    <script src="../assets/js/theme.js" defer></script>
    <script src="admin.js" defer></script>
</head>
<body>
    <button class="theme-toggle" aria-label="Toggle dark mode">🌙</button>

    <div class="auth-topbar">
        <a href="../index.php" class="logo">Tech Pulse <span class="admin-badge">Admin</span></a>
    </div>

    <div class="auth-wrapper">
        <div class="auth-card">

            <div class="auth-header">
                <h1>Edit account</h1>
                <p>Member since <?= htmlspecialchars($memberSince) ?>.</p>
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

            <form method="POST" action="editaccount.php?id=<?= (int) $studentId ?>" id="editForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="studentid" value="<?= htmlspecialchars((string) $studentId) ?>">

                <div class="form-group">
                    <label for="studentid_display">Student ID</label>
                    <input type="text" id="studentid_display" value="<?= htmlspecialchars((string) $studentId) ?>" disabled>
                    <p class="field-hint">The Student ID is the login identifier and can't be changed.</p>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="firstname">First Name</label>
                        <input type="text" id="firstname" name="firstname" maxlength="67"
                               value="<?= htmlspecialchars($old['firstname']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="lastname">Last Name</label>
                        <input type="text" id="lastname" name="lastname" maxlength="67"
                               value="<?= htmlspecialchars($old['lastname']) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="course">Course</label>
                    <select id="course" name="course" required>
                        <option value="" disabled <?= $old['course'] === '' ? 'selected' : '' ?>>-- Select Course --</option>
                        <?php foreach ($allowedCourses as $code => $label): ?>
                            <option value="<?= htmlspecialchars($code) ?>" <?= $old['course'] === $code ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($old['course'] !== '' && !array_key_exists($old['course'], $allowedCourses)): ?>
                        <p class="field-hint">Stored value "<?= htmlspecialchars($old['course']) ?>" isn't in the current list — pick a valid course to save.</p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="yearlevel">Year Level</label>
                    <select id="yearlevel" name="yearlevel" required>
                        <option value="" disabled <?= $old['yearlevel'] === '' ? 'selected' : '' ?>>-- Select Year Level --</option>
                        <?php foreach ($allowedYearLevels as $yl): ?>
                            <option value="<?= htmlspecialchars($yl) ?>" <?= $old['yearlevel'] === $yl ? 'selected' : '' ?>>
                                <?= htmlspecialchars($yl) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($old['yearlevel'] !== '' && !in_array($old['yearlevel'], $allowedYearLevels, true)): ?>
                        <p class="field-hint">Stored value "<?= htmlspecialchars($old['yearlevel']) ?>" isn't in the current list — pick a valid year level to save.</p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div class="password-field">
                        <input type="password" id="new_password" name="new_password" minlength="8">
                        <button type="button" class="toggle-password" data-target="new_password">Show</button>
                    </div>
                    <p class="field-hint">Leave blank to keep the current password.</p>
                </div>

                <button type="submit" class="submit-btn">Save changes</button>
            </form>

            <div style="text-align:center;">
                <a href="admindashboard.php" class="back-home">&larr; Back to dashboard</a>
            </div>
        </div>
    </div>

</body>
</html>
