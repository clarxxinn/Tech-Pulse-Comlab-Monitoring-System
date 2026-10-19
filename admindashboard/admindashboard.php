<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/techpulse.php';
require_once __DIR__ . '/../config/options.php';
require_once __DIR__ . '/admindashboarddb.php';
require_once __DIR__ . '/../auth/register/registerdb.php';

if (empty($_SESSION['is_admin'])) {
    header('Location: ../auth/login/login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$formValues = [
    'studentid' => '',
    'firstname' => '',
    'lastname' => '',
    'course' => '',
    'yearlevel' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $validCsrfToken = hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '');

    if (!$validCsrfToken) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    }

    if ($validCsrfToken && $action === 'delete') {
        $studentId = trim($_POST['studentid'] ?? '');

        if ($studentId !== '' && ctype_digit($studentId)) {
            $deleted = deleteUser($conn, $studentId);
            $_SESSION['admin_flash'] = $deleted
                ? ['type' => 'success', 'message' => "Account {$studentId} has been deleted."]
                : ['type' => 'error', 'message' => "Account {$studentId} could not be deleted."];
        } else {
            $_SESSION['admin_flash'] = ['type' => 'error', 'message' => 'Invalid account selected for deletion.'];
        }

        header('Location: admindashboard.php');
        exit;
    }

    if ($validCsrfToken && $action === 'create') {
        $firstName = trim($_POST['firstname'] ?? '');
        $lastName = trim($_POST['lastname'] ?? '');
        $studentId = trim($_POST['studentid'] ?? '');
        $course = trim($_POST['course'] ?? '');
        $yearLevel = trim($_POST['yearlevel'] ?? '');
        $password = $_POST['password'] ?? '';

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

        if (empty($errors)) {
            $result = registerUser($conn, $studentId, $firstName, $lastName, $course, $yearLevel, $password);

            if ($result['success']) {
                $_SESSION['admin_flash'] = ['type' => 'success', 'message' => "Account {$studentId} has been created."];
                header('Location: admindashboard.php');
                exit;
            }

            $errors[] = $result['error'];
        }
    }
}

$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

$users = getAllUsers($conn);
$totalUsers = count($users);
$latestSignup = $totalUsers > 0 ? date('M j, Y', strtotime($users[0]['created_at'])) : '—';
$csrfToken = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script>
        document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light');
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Tech Pulse</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admindashboard.css">
    <script src="../assets/js/theme.js" defer></script>
    <script src="../assets/js/password-toggle.js" defer></script>
    <script src="admin.js" defer></script>
</head>
<body>
    <button type="button" class="theme-toggle" aria-label="Toggle dark mode">
        <img src="../assets/img/dark-mode.png" alt="Dark mode">
    </button>

    <div class="auth-topbar">
        <a href="../index.php" class="logo">Tech Pulse <span class="admin-badge">Admin</span></a>
    </div>

    <div class="admin-shell">
        <header class="admin-head">
            <div>
                <h1>Admin Dashboard</h1>
                <p>Manage student accounts and review sign-up history.</p>
            </div>
            <form method="POST" action="../auth/logout/logout.php" class="logout-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <button type="submit" class="btn-logout">Log out</button>
            </form>
        </header>

        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="stats">
            <div class="stat">
                <span class="stat-value"><?= htmlspecialchars((string) $totalUsers) ?></span>
                <span class="stat-label">Total accounts</span>
            </div>
            <div class="stat">
                <span class="stat-value"><?= htmlspecialchars($latestSignup) ?></span>
                <span class="stat-label">Latest registration</span>
            </div>
        </section>

        <section class="panel">
            <h2 class="panel-title">Add account</h2>

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

            <form method="POST" action="admindashboard.php" id="createForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="action" value="create">

                <div class="form-row">
                    <div class="form-group">
                        <label for="firstname">First Name</label>
                        <input type="text" id="firstname" name="firstname" maxlength="67"
                            value="<?= htmlspecialchars($formValues['firstname']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="lastname">Last Name</label>
                        <input type="text" id="lastname" name="lastname" maxlength="67"
                            value="<?= htmlspecialchars($formValues['lastname']) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="studentid">Student ID</label>
                    <input type="number" id="studentid" name="studentid" inputmode="numeric" placeholder="25010992"
                        value="<?= htmlspecialchars($formValues['studentid']) ?>" required>
                </div>

                <div class="form-row">
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
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" minlength="8" required>
                        <button type="button" class="toggle-password" data-target="password">Show</button>
                    </div>
                    <p class="field-hint">At least 8 characters.</p>
                </div>

                <button type="submit" class="submit-btn">Add account</button>
            </form>
        </section>

        <section class="panel">
            <h2 class="panel-title">All accounts <span class="count-chip"><?= htmlspecialchars((string) $totalUsers) ?></span></h2>

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Course</th>
                            <th>Year Level</th>
                            <th>Created</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($totalUsers === 0): ?>
                            <tr>
                                <td colspan="6" class="empty-row">No accounts yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td data-label="Student ID"><?= htmlspecialchars((string) $user['StudentID']) ?></td>
                                    <td data-label="Name"><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></td>
                                    <td data-label="Course"><?= htmlspecialchars($user['course']) ?></td>
                                    <td data-label="Year Level"><?= htmlspecialchars($user['yearlevel']) ?></td>
                                    <td data-label="Created"><?= htmlspecialchars(date('M j, Y', strtotime($user['created_at']))) ?></td>
                                    <td data-label="Actions" class="col-actions">
                                        <div class="actions">
                                            <a class="btn-sm btn-edit" href="editaccount.php?id=<?= (int) $user['StudentID'] ?>">Edit</a>
                                            <form method="POST" action="admindashboard.php" class="inline-form"
                                                data-confirm="Delete account <?= htmlspecialchars((string) $user['StudentID']) ?>? This cannot be undone.">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="studentid" value="<?= htmlspecialchars((string) $user['StudentID']) ?>">
                                                <button type="submit" class="btn-sm btn-delete">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</body>
</html>
