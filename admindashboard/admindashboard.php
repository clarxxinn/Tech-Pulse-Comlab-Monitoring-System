<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/techpulse.php';
require_once __DIR__ . '/../config/options.php';
require_once __DIR__ . '/admindashboarddb.php';
require_once __DIR__ . '/../auth/register/registerdb.php';
require_once __DIR__ . '/../config/monitoring.php';

if (empty($_SESSION['is_admin'])) {
    header('Location: ../auth/login/login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$monitoringError = null;

$formValues = [
    'studentid' => '',
    'firstname' => '',
    'lastname' => '',
    'course' => '',
    'yearlevel' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $token = (string) ($_POST['csrf_token'] ?? '');

    $validCsrfToken = hash_equals(
        $_SESSION['csrf_token'],
        $token
    );

    if (!$validCsrfToken) {
        $errors[] =
            'Your session expired. Please refresh the page and try again.';
    }

    // Update Computer Status
    if ($validCsrfToken && $action === 'computer_status') {
        $pcId = filter_var(
            $_POST['computer_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        $newStatus = (string) ($_POST['status'] ?? '');

        if (
            !$pcId ||
            !in_array(
                $newStatus,
                ['available', 'maintenance'],
                true
            )
        ) {
            $monitoringError = 'Invalid computer or status.';
        } else {
            try {
                $conn->begin_transaction();

                $stmt = $conn->prepare(
                    'SELECT computer_id
                     FROM computers
                     WHERE computer_id = ?
                     FOR UPDATE'
                );
                $stmt->bind_param('i', $pcId);
                $stmt->execute();
                $computerExists =
                    $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$computerExists) {
                    throw new RuntimeException(
                        'Computer not found.'
                    );
                }

                $stmt = $conn->prepare(
                    'SELECT session_id
                     FROM computer_sessions
                     WHERE computer_id = ?
                       AND time_out IS NULL
                     LIMIT 1'
                );
                $stmt->bind_param('i', $pcId);
                $stmt->execute();
                $activePcSession =
                    $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($activePcSession) {
                    throw new RuntimeException(
                        'Cannot change an occupied computer.'
                    );
                }

                $stmt = $conn->prepare(
                    'UPDATE computers
                     SET status = ?
                     WHERE computer_id = ?'
                );
                $stmt->bind_param(
                    'si',
                    $newStatus,
                    $pcId
                );
                $stmt->execute();
                $stmt->close();

                $conn->commit();

                $_SESSION['admin_flash'] = [
                    'type' => 'success',
                    'message' => 'Computer status updated.'
                ];

                header('Location: admindashboard.php');
                exit;

            } catch (Throwable $e) {
                $conn->rollback();

                $monitoringError =
                    $e instanceof RuntimeException
                    ? $e->getMessage()
                    : 'Unable to update computer status.';
            }
        }
    }

    // Delete Student Account
    if ($validCsrfToken && $action === 'delete') {
        $studentId = trim(
            (string) ($_POST['studentid'] ?? '')
        );

        if (
            $studentId !== '' &&
            ctype_digit($studentId)
        ) {
            $deleted = deleteUser($conn, $studentId);

            $_SESSION['admin_flash'] = $deleted
                ? [
                    'type' => 'success',
                    'message' =>
                        "Account {$studentId} has been deleted."
                ]
                : [
                    'type' => 'error',
                    'message' =>
                        "Account {$studentId} could not be deleted."
                ];
        } else {
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'message' =>
                    'Invalid account selected for deletion.'
            ];
        }

        header('Location: admindashboard.php');
        exit;
    }

    // Create Student Account
    if ($validCsrfToken && $action === 'create') {
        $firstName = trim(
            (string) ($_POST['firstname'] ?? '')
        );

        $lastName = trim(
            (string) ($_POST['lastname'] ?? '')
        );

        $studentId = trim(
            (string) ($_POST['studentid'] ?? '')
        );

        $course = trim(
            (string) ($_POST['course'] ?? '')
        );

        $yearLevel = trim(
            (string) ($_POST['yearlevel'] ?? '')
        );

        $password = (string) ($_POST['password'] ?? '');

        $formValues = [
            'firstname' => $firstName,
            'lastname' => $lastName,
            'studentid' => $studentId,
            'course' => $course,
            'yearlevel' => $yearLevel,
        ];

        if (
            $firstName === '' ||
            mb_strlen($firstName) > 67
        ) {
            $errors[] =
                'Please enter a valid first name.';
        }

        if (
            $lastName === '' ||
            mb_strlen($lastName) > 67
        ) {
            $errors[] =
                'Please enter a valid last name.';
        }

        if (
            $studentId === '' ||
            !ctype_digit($studentId)
        ) {
            $errors[] =
                'Please enter a valid Student ID (numbers only).';
        }

        if (!array_key_exists($course, $allowedCourses)) {
            $errors[] =
                'Please select a valid course.';
        }

        if (!in_array(
            $yearLevel,
            $allowedYearLevels,
            true
        )) {
            $errors[] =
                'Please select a valid year level.';
        }

        if (strlen($password) < 8) {
            $errors[] =
                'Password must be at least 8 characters long.';
        }

        if (empty($errors)) {
            $result = registerUser(
                $conn,
                $studentId,
                $firstName,
                $lastName,
                $course,
                $yearLevel,
                $password
            );

            if ($result['success']) {
                $_SESSION['admin_flash'] = [
                    'type' => 'success',
                    'message' =>
                        "Account {$studentId} has been created."
                ];

                header('Location: admindashboard.php');
                exit;
            }

            $errors[] = $result['error'];
        }
    }
}

// Flash Messages
$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

// Existing Account Management Data
$users = getAllUsers($conn);
$totalUsers = count($users);

$latestSignup = $totalUsers > 0
    ? date(
        'M j, Y',
        strtotime($users[0]['created_at'])
    )
    : '—';

$csrfToken = $_SESSION['csrf_token'];

// Computer Monitoring Data
$adminComputers = monitoringComputers($conn);

$activeSessions = $conn->query(
    "SELECT
        s.session_id,
        s.time_in,
        u.StudentID,
        u.firstname,
        u.lastname,
        c.lab_id,
        c.pc_number
     FROM computer_sessions s
     JOIN users u
         ON u.StudentID = s.StudentID
     JOIN computers c
         ON c.computer_id = s.computer_id
     WHERE s.time_out IS NULL
     ORDER BY s.time_in DESC"
)->fetch_all(MYSQLI_ASSOC);

$sessionHistory = $conn->query(
    "SELECT
        s.session_id,
        s.time_in,
        s.time_out,
        u.StudentID,
        u.firstname,
        u.lastname,
        c.lab_id,
        c.pc_number
     FROM computer_sessions s
     JOIN users u
         ON u.StudentID = s.StudentID
     JOIN computers c
         ON c.computer_id = s.computer_id
     ORDER BY s.time_in DESC
     LIMIT 30"
)->fetch_all(MYSQLI_ASSOC);

$totalComputers = count($adminComputers);
$availableCount = 0;
$occupiedCount = 0;
$maintenanceCount = 0;

foreach ($adminComputers as $computer) {
    if ($computer['session_id'] !== null) {
        $occupiedCount++;
    } elseif ($computer['status'] === 'maintenance') {
        $maintenanceCount++;
    } else {
        $availableCount++;
    }
}

$activeByComputer = [];

foreach ($activeSessions as $session) {
    $key =
        (int) $session['lab_id'] . '-' .
        (int) $session['pc_number'];

    $activeByComputer[$key] = $session;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <script>
        document.documentElement.setAttribute(
            'data-theme',
            localStorage.getItem('theme') || 'light'
        );
    </script>

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Dashboard - Tech Pulse</title>

    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="admindashboard.css"
    >

    <script
        src="../assets/js/theme.js"
        defer
    ></script>

    <script
        src="../assets/js/password-toggle.js"
        defer
    ></script>

    <script
        src="admin.js"
        defer
    ></script>
</head>

<body>

    <!-- Theme Toggle -->
    <button
        type="button"
        class="theme-toggle"
        aria-label="Toggle dark mode"
    >
        <img
            src="../assets/img/dark-mode.png"
            alt="Dark mode"
        >
    </button>

    <!-- Top Bar -->
    <div class="auth-topbar">
        <a href="../index.php" class="logo">
            Tech Pulse
            <span class="admin-badge">Admin</span>
        </a>
    </div>

    <div class="admin-shell">

        <!-- Dashboard Header -->
        <header class="admin-head">
            <div>
                <h1>Admin Dashboard</h1>
                <p>
                    Manage student accounts and monitor
                    computer laboratories.
                </p>
            </div>

            <form
                method="POST"
                action="../auth/logout/logout.php"
                class="logout-form"
            >
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($csrfToken) ?>"
                >

                <button
                    type="submit"
                    class="btn-logout"
                >
                    Log out
                </button>
            </form>
        </header>

        <!-- Flash Message -->
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <!-- Existing Account Statistics -->
        <section class="stats">

            <div class="stat">
                <span class="stat-value">
                    <?= htmlspecialchars(
                        (string) $totalUsers
                    ) ?>
                </span>

                <span class="stat-label">
                    Total Accounts
                </span>
            </div>

            <div class="stat">
                <span class="stat-value">
                    <?= htmlspecialchars($latestSignup) ?>
                </span>

                <span class="stat-label">
                    Latest Registration
                </span>
            </div>

        </section>

        <!-- Computer Monitoring Panel -->
        <section class="panel admin-monitoring">

            <h2 class="panel-title">
                Computer Laboratory Monitoring
            </h2>

            <p class="monitor-description">
                Select a laboratory to monitor its computers.
            </p>

            <?php if ($monitoringError): ?>
                <div class="alert alert-error" role="alert">
                    <?= htmlspecialchars($monitoringError) ?>
                </div>
            <?php endif; ?>

            <!-- Monitoring Statistics -->
            <div class="monitor-stats">

                <div class="monitor-stat">
                    <strong>
                        <?= $totalComputers ?>
                    </strong>
                    <span>Total PCs</span>
                </div>

                <div class="monitor-stat is-available">
                    <strong>
                        <?= $availableCount ?>
                    </strong>
                    <span>Available</span>
                </div>

                <div class="monitor-stat is-occupied">
                    <strong>
                        <?= $occupiedCount ?>
                    </strong>
                    <span>Occupied</span>
                </div>

                <div class="monitor-stat is-maintenance">
                    <strong>
                        <?= $maintenanceCount ?>
                    </strong>
                    <span>Maintenance</span>
                </div>

            </div>

            <!-- ComLab Selection -->
            <h3 class="monitor-subtitle">
                Select Computer Laboratory
            </h3>

            <div class="admin-lab-options">

                <?php for ($lab = 1; $lab <= 3; $lab++): ?>

                    <button
                        type="button"
                        class="admin-lab-option"
                        data-lab="<?= $lab ?>"
                        aria-pressed="false"
                        aria-controls="admin-lab-panel-<?= $lab ?>"
                    >
                        <span class="admin-lab-icon">
                            &#128421;
                        </span>

                        <strong>
                            ComLab <?= $lab ?>
                        </strong>

                        <small>45 Computers</small>
                    </button>

                <?php endfor; ?>

            </div>

            <!-- Selected Laboratory -->
            <div id="admin-lab-display" hidden>

                <?php for ($lab = 1; $lab <= 3; $lab++): ?>

                    <section
                        id="admin-lab-panel-<?= $lab ?>"
                        class="admin-lab-panel"
                        data-lab="<?= $lab ?>"
                        hidden
                    >

                        <div class="admin-lab-header">
                            <h3>
                                ComLab <?= $lab ?>
                            </h3>

                            <span>45 Computers</span>
                        </div>

                        <div class="admin-pc-grid">

                            <?php foreach (
                                $adminComputers as $computer
                            ): ?>

                                <?php
                                if (
                                    (int) $computer['lab_id'] !== $lab
                                ) {
                                    continue;
                                }

                                $occupied =
                                    $computer['session_id'] !== null;

                                $status = $occupied
                                    ? 'occupied'
                                    : $computer['status'];

                                $pcNumber =
                                    (int) $computer['pc_number'];

                                $computerId =
                                    (int) $computer['computer_id'];

                                $sessionKey =
                                    $lab . '-' . $pcNumber;

                                $pcSession =
                                    $activeByComputer[$sessionKey]
                                    ?? null;
                                ?>

                                <div
                                    class="admin-pc-card <?= htmlspecialchars($status) ?>"
                                >

                                    <span class="admin-pc-icon">
                                        &#128421;
                                    </span>

                                    <strong>
                                        PC <?= str_pad(
                                            (string) $pcNumber,
                                            2,
                                            '0',
                                            STR_PAD_LEFT
                                        ) ?>
                                    </strong>

                                    <small>
                                        <?= ucfirst(
                                            htmlspecialchars($status)
                                        ) ?>
                                    </small>

                                    <?php if ($pcSession): ?>

                                        <div class="pc-student">
                                            <strong>
                                                <?= htmlspecialchars(
                                                    $pcSession['firstname'] .
                                                    ' ' .
                                                    $pcSession['lastname']
                                                ) ?>
                                            </strong>

                                            <small>
                                                ID:
                                                <?= htmlspecialchars(
                                                    (string) $pcSession['StudentID']
                                                ) ?>
                                            </small>
                                        </div>

                                    <?php endif; ?>

                                    <?php if (!$occupied): ?>

                                        <form
                                            method="POST"
                                            action="admindashboard.php"
                                            class="admin-pc-form"
                                        >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= htmlspecialchars($csrfToken) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="computer_status"
                                            >

                                            <input
                                                type="hidden"
                                                name="computer_id"
                                                value="<?= $computerId ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="status"
                                                value="<?= $status === 'maintenance'
                                                    ? 'available'
                                                    : 'maintenance' ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="admin-pc-action"
                                            >
                                                <?= $status === 'maintenance'
                                                    ? 'Set Available'
                                                    : 'Maintenance' ?>
                                            </button>

                                        </form>

                                    <?php else: ?>

                                        <small>In Use</small>

                                    <?php endif; ?>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    </section>

                <?php endfor; ?>

            </div>

            <!-- Status Legend -->
            <div class="admin-monitor-legend">
                <span>
                    <i class="legend-dot available-dot"></i>
                    Available
                </span>

                <span>
                    <i class="legend-dot occupied-dot"></i>
                    Occupied
                </span>

                <span>
                    <i class="legend-dot maintenance-dot"></i>
                    Maintenance
                </span>
            </div>

        </section>

        <!-- Active Sessions -->
        <section class="panel">

            <h2 class="panel-title">
                Active Computer Sessions
                <span class="count-chip">
                    <?= count($activeSessions) ?>
                </span>
            </h2>

            <div class="table-wrap">
                <table class="data-table">

                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>ComLab</th>
                            <th>PC</th>
                            <th>Time In</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (empty($activeSessions)): ?>

                            <tr>
                                <td
                                    colspan="5"
                                    class="empty-row"
                                >
                                    No active sessions.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach (
                                $activeSessions as $session
                            ): ?>

                                <tr>
                                    <td data-label="Student ID">
                                        <?= htmlspecialchars(
                                            (string) $session['StudentID']
                                        ) ?>
                                    </td>

                                    <td data-label="Student Name">
                                        <?= htmlspecialchars(
                                            $session['firstname'] .
                                            ' ' .
                                            $session['lastname']
                                        ) ?>
                                    </td>

                                    <td data-label="ComLab">
                                        ComLab
                                        <?= (int) $session['lab_id'] ?>
                                    </td>

                                    <td data-label="PC">
                                        PC
                                        <?= (int) $session['pc_number'] ?>
                                    </td>

                                    <td data-label="Time In">
                                        <?= htmlspecialchars(
                                            $session['time_in']
                                        ) ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </tbody>
                </table>
            </div>

        </section>

        <!-- Recent Session History -->
        <section class="panel">

            <h2 class="panel-title">
                Recent Session History
            </h2>

            <div class="table-wrap">
                <table class="data-table">

                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>ComLab</th>
                            <th>PC</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (empty($sessionHistory)): ?>

                            <tr>
                                <td
                                    colspan="5"
                                    class="empty-row"
                                >
                                    No session history yet.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach (
                                $sessionHistory as $session
                            ): ?>

                                <tr>
                                    <td data-label="Student">
                                        <?= htmlspecialchars(
                                            $session['firstname'] .
                                            ' ' .
                                            $session['lastname']
                                        ) ?>
                                    </td>

                                    <td data-label="ComLab">
                                        ComLab
                                        <?= (int) $session['lab_id'] ?>
                                    </td>

                                    <td data-label="PC">
                                        PC
                                        <?= (int) $session['pc_number'] ?>
                                    </td>

                                    <td data-label="Time In">
                                        <?= htmlspecialchars(
                                            $session['time_in']
                                        ) ?>
                                    </td>

                                    <td data-label="Time Out">
                                        <?= htmlspecialchars(
                                            $session['time_out']
                                            ?? 'Active'
                                        ) ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </tbody>
                </table>
            </div>

        </section>

        <!-- Existing Add Account -->
        <section class="panel">

            <h2 class="panel-title">
                Add Account
            </h2>

            <?php if (!empty($errors)): ?>

                <div class="alert alert-error">
                    <strong>
                        Please fix the following:
                    </strong>

                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li>
                                <?= htmlspecialchars($error) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

            <?php endif; ?>

            <form
                method="POST"
                action="admindashboard.php"
                id="createForm"
                novalidate
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($csrfToken) ?>"
                >

                <input
                    type="hidden"
                    name="action"
                    value="create"
                >

                <div class="form-row">

                    <div class="form-group">
                        <label for="firstname">
                            First Name
                        </label>

                        <input
                            type="text"
                            id="firstname"
                            name="firstname"
                            maxlength="67"
                            value="<?= htmlspecialchars($formValues['firstname']) ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="lastname">
                            Last Name
                        </label>

                        <input
                            type="text"
                            id="lastname"
                            name="lastname"
                            maxlength="67"
                            value="<?= htmlspecialchars($formValues['lastname']) ?>"
                            required
                        >
                    </div>

                </div>

                <div class="form-group">
                    <label for="studentid">
                        Student ID
                    </label>

                    <input
                        type="number"
                        id="studentid"
                        name="studentid"
                        inputmode="numeric"
                        placeholder="25010992"
                        value="<?= htmlspecialchars($formValues['studentid']) ?>"
                        required
                    >
                </div>

                <div class="form-row">

                    <div class="form-group">
                        <label for="course">
                            Course
                        </label>

                        <select
                            id="course"
                            name="course"
                            required
                        >
                            <option
                                value=""
                                disabled
                                <?= $formValues['course'] === ''
                                    ? 'selected'
                                    : '' ?>
                            >
                                -- Select Course --
                            </option>

                            <?php foreach (
                                $allowedCourses as $code => $label
                            ): ?>

                                <option
                                    value="<?= htmlspecialchars($code) ?>"
                                    <?= $formValues['course'] === $code
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= htmlspecialchars($label) ?>
                                </option>

                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="yearlevel">
                            Year Level
                        </label>

                        <select
                            id="yearlevel"
                            name="yearlevel"
                            required
                        >
                            <option
                                value=""
                                disabled
                                <?= $formValues['yearlevel'] === ''
                                    ? 'selected'
                                    : '' ?>
                            >
                                -- Select Year Level --
                            </option>

                            <?php foreach (
                                $allowedYearLevels as $yearLevelOption
                            ): ?>

                                <option
                                    value="<?= htmlspecialchars($yearLevelOption) ?>"
                                    <?= $formValues['yearlevel'] === $yearLevelOption
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= htmlspecialchars($yearLevelOption) ?>
                                </option>

                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <div class="password-field">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            minlength="8"
                            required
                        >

                        <button
                            type="button"
                            class="toggle-password"
                            data-target="password"
                        >
                            Show
                        </button>

                    </div>

                    <p class="field-hint">
                        At least 8 characters.
                    </p>

                </div>

                <button
                    type="submit"
                    class="submit-btn"
                >
                    Add Account
                </button>

            </form>

        </section>

        <!-- Existing Account List -->
        <section class="panel">

            <h2 class="panel-title">
                All Accounts
                <span class="count-chip">
                    <?= $totalUsers ?>
                </span>
            </h2>

            <div class="table-wrap">

                <table class="data-table">

                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Course</th>
                            <th>Year Level</th>
                            <th>Created</th>
                            <th class="col-actions">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if ($totalUsers === 0): ?>

                            <tr>
                                <td
                                    colspan="6"
                                    class="empty-row"
                                >
                                    No accounts yet.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($users as $user): ?>

                                <tr>

                                    <td data-label="Student ID">
                                        <?= htmlspecialchars(
                                            (string) $user['StudentID']
                                        ) ?>
                                    </td>

                                    <td data-label="Name">
                                        <?= htmlspecialchars(
                                            $user['firstname'] .
                                            ' ' .
                                            $user['lastname']
                                        ) ?>
                                    </td>

                                    <td data-label="Course">
                                        <?= htmlspecialchars(
                                            $user['course']
                                        ) ?>
                                    </td>

                                    <td data-label="Year Level">
                                        <?= htmlspecialchars(
                                            $user['yearlevel']
                                        ) ?>
                                    </td>

                                    <td data-label="Created">
                                        <?= htmlspecialchars(
                                            date(
                                                'M j, Y',
                                                strtotime(
                                                    $user['created_at']
                                                )
                                            )
                                        ) ?>
                                    </td>

                                    <td
                                        data-label="Actions"
                                        class="col-actions"
                                    >

                                        <div class="actions">

                                            <a
                                                class="btn-sm btn-edit"
                                                href="editaccount.php?id=<?= (int) $user['StudentID'] ?>"
                                            >
                                                Edit
                                            </a>

                                            <form
                                                method="POST"
                                                action="admindashboard.php"
                                                class="inline-form"
                                                data-confirm="Delete account <?= htmlspecialchars((string) $user['StudentID']) ?>? This cannot be undone."
                                            >

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= htmlspecialchars($csrfToken) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="action"
                                                    value="delete"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="studentid"
                                                    value="<?= htmlspecialchars(
                                                        (string) $user['StudentID']
                                                    ) ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn-sm btn-delete"
                                                >
                                                    Delete
                                                </button>

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

    <!-- ComLab Selector JavaScript -->
    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                const buttons = document.querySelectorAll(
                    '.admin-lab-option'
                );

                const panels = document.querySelectorAll(
                    '.admin-lab-panel'
                );

                const display = document.getElementById(
                    'admin-lab-display'
                );

                buttons.forEach(function (button) {
                    button.addEventListener(
                        'click',
                        function () {
                            const selectedLab =
                                button.dataset.lab;

                            buttons.forEach(function (item) {
                                const selected =
                                    item === button;

                                item.classList.toggle(
                                    'selected',
                                    selected
                                );

                                item.setAttribute(
                                    'aria-pressed',
                                    String(selected)
                                );
                            });

                            panels.forEach(function (panel) {
                                panel.hidden =
                                    panel.dataset.lab !== selectedLab;
                            });

                            display.hidden = false;
                        }
                    );
                });
            }
        );
    </script>

</body>
</html>