<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/techpulse.php';
require_once __DIR__ . '/dashboarddb.php';
require_once __DIR__ . '/../config/monitoring.php';

// Check student authentication
if (empty($_SESSION['StudentID'])) {
    header('Location: ../auth/login/login.php');
    exit;
}

// Get student information
$user = findUserProfile(
    $conn,
    (string) $_SESSION['StudentID']
);

if ($user === null) {
    $_SESSION = [];
    session_destroy();

    header('Location: ../auth/login/login.php');
    exit;
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$monitoringError = null;

// Handle Time In / Time Out
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['monitoring_action'])
) {
    $submittedToken = (string) (
        $_POST['csrf_token'] ?? ''
    );

    if (!hash_equals(
        $_SESSION['csrf_token'],
        $submittedToken
    )) {
        $monitoringError =
            'Invalid security token. Refresh and retry.';
    } else {
        try {
            $action = $_POST['monitoring_action'];

            if ($action === 'time_in') {
                $pc = filter_var(
                    $_POST['computer_id'] ?? null,
                    FILTER_VALIDATE_INT
                );

                if ($pc === false || $pc === null) {
                    throw new RuntimeException(
                        'Please select a valid computer.'
                    );
                }

                monitoringTimeIn(
                    $conn,
                    (int) $user['StudentID'],
                    $pc
                );

            } elseif ($action === 'time_out') {
                monitoringTimeOut(
                    $conn,
                    (int) $user['StudentID']
                );

            } else {
                throw new RuntimeException(
                    'Invalid monitoring action.'
                );
            }

            // Prevent duplicate form submission
            header('Location: dashboard.php');
            exit;

        } catch (Throwable $e) {
            $monitoringError =
                $e instanceof RuntimeException
                ? $e->getMessage()
                : 'Unable to update session. Please retry.';
        }
    }
}

// Load monitoring information
$computers = monitoringComputers($conn);

$activeSession = monitoringActive(
    $conn,
    (int) $user['StudentID']
);

$sessionHistory = monitoringHistory(
    $conn,
    (int) $user['StudentID']
);

$memberSince = date(
    'F j, Y',
    strtotime($user['created_at'])
);
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

    <title>Dashboard - Tech Pulse</title>

    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="dashboard.css">

    <script src="../assets/js/theme.js" defer></script>
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

    <!-- Top Navigation -->
    <div class="auth-topbar">
        <a href="../index.php" class="logo">
            Tech Pulse
        </a>
    </div>

    <div class="auth-wrapper">
        <div class="auth-card">

            <!-- Welcome Header -->
            <div class="auth-header">
                <h1>
                    Welcome back,
                    <?= htmlspecialchars($user['firstname']) ?>

                    <img
                        src="../assets/img/wave.png"
                        alt="Wave"
                    >
                </h1>

                <p>
                    Here's your ComLab account overview.
                </p>
            </div>

            <!-- Student Profile -->
            <dl class="profile-list">

                <div class="profile-row">
                    <dt>Student ID</dt>
                    <dd>
                        <?= htmlspecialchars(
                            (string) $user['StudentID']
                        ) ?>
                    </dd>
                </div>

                <div class="profile-row">
                    <dt>Name</dt>
                    <dd>
                        <?= htmlspecialchars(
                            $user['firstname'] . ' ' .
                            $user['lastname']
                        ) ?>
                    </dd>
                </div>

                <div class="profile-row">
                    <dt>Course</dt>
                    <dd>
                        <?= htmlspecialchars($user['course']) ?>
                    </dd>
                </div>

                <div class="profile-row">
                    <dt>Year Level</dt>
                    <dd>
                        <?= htmlspecialchars($user['yearlevel']) ?>
                    </dd>
                </div>

                <div class="profile-row">
                    <dt>Member since</dt>
                    <dd>
                        <?= htmlspecialchars($memberSince) ?>
                    </dd>
                </div>

            </dl>

            <!-- Computer Monitoring -->
            <section class="monitor-section">

                <h2>
                    Computer Availability
                </h2>

                <p>
                    3 Computer Laboratories — 135 Computers
                </p>

                <!-- Monitoring Error -->
                <?php if ($monitoringError): ?>

                    <p
                        role="alert"
                        class="monitor-error"
                    >
                        <?= htmlspecialchars($monitoringError) ?>
                    </p>

                <?php endif; ?>

                <!-- Active Student Session -->
                <?php if ($activeSession): ?>

                    <div class="monitor-active">

                        <h3>Active Session</h3>

                        <p>
                            ComLab
                            <?= (int) $activeSession['lab_id'] ?>

                            — PC
                            <?= (int) $activeSession['pc_number'] ?>
                        </p>

                        <p>
                            Time In:
                            <?= htmlspecialchars(
                                $activeSession['time_in']
                            ) ?>
                        </p>

                        <!-- Separate Time Out Form -->
                        <form method="POST">

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= htmlspecialchars(
                                    $_SESSION['csrf_token']
                                ) ?>"
                            >

                            <input
                                type="hidden"
                                name="monitoring_action"
                                value="time_out"
                            >

                            <button
                                type="submit"
                                class="monitor-button"
                            >
                                Time Out
                            </button>

                        </form>

                    </div>

                <?php endif; ?>

                <!-- Laboratory Computer Grid -->
                <?php for ($lab = 1; $lab <= 3; $lab++): ?>

                    <h3>
                        ComLab <?= $lab ?>
                    </h3>

                    <div class="monitor-grid">

                        <?php foreach ($computers as $computer): ?>

                            <?php
                            if (
                                (int) $computer['lab_id'] !== $lab
                            ) {
                                continue;
                            }

                            $occupied =
                                $computer['session_id'] !== null;

                            $available =
                                $computer['status'] === 'available'
                                && !$occupied;

                            $pcStatus = $occupied
                                ? 'occupied'
                                : (
                                    $available
                                    ? 'available'
                                    : 'maintenance'
                                );
                            ?>

                            <!-- Separate Time In Form -->
                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= htmlspecialchars(
                                        $_SESSION['csrf_token']
                                    ) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="monitoring_action"
                                    value="time_in"
                                >

                                <input
                                    type="hidden"
                                    name="computer_id"
                                    value="<?= (int) $computer['computer_id'] ?>"
                                >

                                <button
                                    type="submit"
                                    class="monitor-pc <?= $pcStatus ?>"
                                    <?= (
                                        !$available || $activeSession
                                    ) ? 'disabled' : '' ?>
                                    title="<?= ucfirst($pcStatus) ?>"
                                >
                                    PC <?= (int) $computer['pc_number'] ?>
                                </button>

                            </form>

                        <?php endforeach; ?>

                    </div>

                <?php endfor; ?>

                <!-- Computer Status Legend -->
                <div class="monitor-legend">
                    <p>
                        Green = Available
                    </p>

                    <p>
                        Red = Occupied
                    </p>

                    <p>
                        Yellow = Maintenance
                    </p>
                </div>

                <!-- Student Session History -->
                <h3>Recent Sessions</h3>

                <div class="monitor-history">

                    <table>

                        <thead>
                            <tr>
                                <th>Laboratory / PC</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach (
                                $sessionHistory as $record
                            ): ?>

                                <tr>

                                    <td>
                                        ComLab
                                        <?= (int) $record['lab_id'] ?>

                                        / PC
                                        <?= (int) $record['pc_number'] ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $record['time_in']
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $record['time_out'] ?? 'Active'
                                        ) ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                            <?php if (empty($sessionHistory)): ?>

                                <tr>
                                    <td colspan="3">
                                        No session history yet.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </section>
            <!-- End Computer Monitoring -->

            <!-- Separate Logout Form -->
            <form
                method="POST"
                action="../auth/logout/logout.php"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(
                        $_SESSION['csrf_token']
                    ) ?>"
                >

                <button
                    type="submit"
                    class="logout-btn"
                >
                    Log out
                </button>

            </form>

        </div>
    </div>

</body>
</html>