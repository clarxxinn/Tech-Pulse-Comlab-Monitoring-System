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

require_once __DIR__ . '/../config/monitoring.php';

$monitoringError = null;

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['monitoring_action'])
) {
    if (!hash_equals(
        $_SESSION['csrf_token'],
        (string) ($_POST['csrf_token'] ?? '')
    )) {
        $monitoringError =
            'Invalid security token. Refresh and retry.';
    } else {
        try {
            if ($_POST['monitoring_action'] === 'time_in') {
                $pc = filter_var(
                    $_POST['computer_id'] ?? null,
                    FILTER_VALIDATE_INT
                );

                if (!$pc) {
                    throw new RuntimeException('Choose a computer.');
                }

                monitoringTimeIn(
                    $conn,
                    (int) $user['StudentID'],
                    $pc
                );
            } elseif ($_POST['monitoring_action'] === 'time_out') {
                monitoringTimeOut(
                    $conn,
                    (int) $user['StudentID']
                );
            } else {
                throw new RuntimeException('Invalid action.');
            }

            header('Location: dashboard.php');
            exit;

        } catch (Throwable $e) {
            $monitoringError = $e instanceof RuntimeException
                ? $e->getMessage()
                : 'Unable to update session. Please retry.';
        }
    }
}

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

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Tech Pulse</title>

    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
          rel="stylesheet">

    <link rel="stylesheet" href="dashboard.css">

    <script src="../assets/js/theme.js" defer></script>
</head>

<body>
    <button
        type="button"
        class="theme-toggle"
        aria-label="Toggle dark mode"
    >
        <img src="../assets/img/dark-mode.png"
             alt="Dark mode">
    </button>

    <div class="auth-topbar">
        <a href="../index.php" class="logo">
            Tech Pulse
        </a>
    </div>

    <div class="auth-wrapper">
        <div class="auth-card">

            <div class="auth-header">
                <h1>
                    Welcome back,
                    <?= htmlspecialchars($user['firstname']) ?>

                    <img src="../assets/img/wave.png"
                         alt="Wave">
                </h1>

                <p>Here's your ComLab account overview.</p>
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
                    Computer Availability — 3 Labs / 135 PCs
                </h2>

                <?php if ($monitoringError): ?>
                    <p role="alert" class="monitor-error">
                        <?= htmlspecialchars($monitoringError) ?>
                    </p>
                <?php endif; ?>

                <!-- Active Session -->
                <?php if ($activeSession): ?>

                    <p class="monitor-active">
                        Active: ComLab
                        <?= (int) $activeSession['lab_id'] ?>,
                        PC <?= (int) $activeSession['pc_number'] ?>
                        — since
                        <?= htmlspecialchars(
                            $activeSession['time_in']
                        ) ?>
                    </p>

                    <form method="POST">

                        <input type="hidden"
                               name="csrf_token"
                               value="<?= htmlspecialchars(
                                   $_SESSION['csrf_token']
                               ) ?>">

                        <input type="hidden"
                               name="monitoring_action"
                               value="time_out">

                        <button
                            class="monitor-button"
                            type="submit"
                        >
                            Time Out
                        </button>

                    </form>

                <?php endif; ?>

                <!-- ComLab Selector -->
                <div class="lab-selection">

                    <h3>Select Computer Laboratory</h3>

                    <p>
                        Choose a ComLab to see its 45 computers.
                    </p>

                    <div class="lab-options">

                        <?php for ($lab = 1; $lab <= 3; $lab++): ?>

                            <button
                                type="button"
                                class="lab-option"
                                data-lab="<?= $lab ?>"
                                aria-pressed="false"
                                aria-controls="lab-panel-<?= $lab ?>"
                            >
                                <span
                                    class="lab-icon"
                                    aria-hidden="true"
                                >
                                    &#128421;
                                </span>

                                <strong>
                                    ComLab <?= $lab ?>
                                </strong>

                                <small>
                                    45 Computers
                                </small>
                            </button>

                        <?php endfor; ?>

                    </div>
                </div>

                <!-- Selected Laboratory -->
                <div id="lab-display" hidden>

                    <?php for ($lab = 1; $lab <= 3; $lab++): ?>

                        <section
                            class="lab-panel"
                            id="lab-panel-<?= $lab ?>"
                            data-lab="<?= $lab ?>"
                            hidden
                        >

                            <div class="lab-panel-header">
                                <h3>
                                    ComLab <?= $lab ?>
                                </h3>

                                <span>
                                    45 Computers
                                </span>
                            </div>

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

                                    $status = $occupied
                                        ? 'occupied'
                                        : (
                                            $available
                                            ? 'available'
                                            : 'maintenance'
                                        );
                                    ?>

                                    <form
                                        method="POST"
                                        class="pc-form"
                                    >

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
                                            class="monitor-pc <?= $status ?>"
                                            <?= (
                                                !$available ||
                                                $activeSession
                                            ) ? 'disabled' : '' ?>
                                            title="PC <?= (int) $computer['pc_number'] ?> - <?= ucfirst($status) ?>"
                                        >
                                            <span aria-hidden="true">
                                                &#128421;
                                            </span>

                                            <span>
                                                PC <?= str_pad(
                                                    (string) $computer['pc_number'],
                                                    2,
                                                    '0',
                                                    STR_PAD_LEFT
                                                ) ?>
                                            </span>
                                        </button>

                                    </form>

                                <?php endforeach; ?>

                            </div>
                        </section>

                    <?php endfor; ?>

                </div>

                <!-- Status Legend -->
                <p>
                    Green = Available ·
                    Red = Occupied ·
                    Yellow = Maintenance.
                    Select a green PC to Time In.
                </p>

                <!-- Session History -->
                <h3>Recent Sessions</h3>

                <div class="monitor-history">
                    <table>
                        <thead>
                            <tr>
                                <th>Lab / PC</th>
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
                                        Lab <?= (int) $record['lab_id'] ?>
                                        / PC <?= (int) $record['pc_number'] ?>
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

            <!-- Logout -->
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

    <!-- ComLab Selector JavaScript -->
    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {

                const buttons =
                    document.querySelectorAll('.lab-option');

                const panels =
                    document.querySelectorAll('.lab-panel');

                const display =
                    document.getElementById('lab-display');

                buttons.forEach(function (button) {

                    button.addEventListener(
                        'click',
                        function () {

                            const lab = button.dataset.lab;

                            buttons.forEach(function (item) {
                                const selected = item === button;

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
                                    panel.dataset.lab !== lab;
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