<?php
declare(strict_types=1);

function monitoringLaboratories(mysqli $db): array {
    return $db->query('SELECT l.lab_id, l.lab_name, COUNT(c.computer_id) AS pc_count
        FROM laboratories l LEFT JOIN computers c ON c.lab_id = l.lab_id
        GROUP BY l.lab_id, l.lab_name ORDER BY l.lab_id')->fetch_all(MYSQLI_ASSOC);
}

function monitoringComputers(mysqli $db): array {
    return $db->query("
        SELECT c.computer_id, c.lab_id, c.pc_number,
               c.status, s.session_id, s.StudentID
        FROM computers c
        LEFT JOIN computer_sessions s
            ON s.active_computer = c.computer_id
        ORDER BY c.lab_id, c.pc_number
    ")->fetch_all(MYSQLI_ASSOC);
}

function monitoringActive(mysqli $db, int $student): ?array {
    $q = $db->prepare("
        SELECT s.session_id, s.time_in,
               c.pc_number, c.lab_id
        FROM computer_sessions s
        JOIN computers c ON c.computer_id = s.computer_id
        WHERE s.active_student = ?
    ");
    $q->bind_param('i', $student);
    $q->execute();
    $r = $q->get_result()->fetch_assoc();
    $q->close();
    return $r ?: null;
}

function monitoringHistory(mysqli $db, int $student): array {
    $q = $db->prepare("
        SELECT s.time_in, s.time_out,
               c.lab_id, c.pc_number
        FROM computer_sessions s
        JOIN computers c ON c.computer_id = s.computer_id
        WHERE s.StudentID = ?
        ORDER BY s.time_in DESC, s.session_id DESC
        LIMIT 15
    ");
    $q->bind_param('i', $student);
    $q->execute();
    $r = $q->get_result()->fetch_all(MYSQLI_ASSOC);
    $q->close();
    return $r;
}

function monitoringTimeIn(mysqli $db, int $student, int $pc): void {
    $db->begin_transaction();

    try {
        $q = $db->prepare(
            'SELECT StudentID FROM users WHERE StudentID=? FOR UPDATE'
        );
        $q->bind_param('i', $student);
        $q->execute();
        $found = $q->get_result()->fetch_assoc();
        $q->close();

        if (!$found) {
            throw new RuntimeException('Student not found.');
        }

        $q = $db->prepare(
            'SELECT computer_id,status FROM computers WHERE computer_id=? FOR UPDATE'
        );
        $q->bind_param('i', $pc);
        $q->execute();
        $computer = $q->get_result()->fetch_assoc();
        $q->close();

        if (!$computer || $computer['status'] !== 'available') {
            throw new RuntimeException('Computer is unavailable.');
        }

        $q = $db->prepare(
            'INSERT INTO computer_sessions (StudentID,computer_id) VALUES (?,?)'
        );
        $q->bind_param('ii', $student, $pc);
        $q->execute();
        $q->close();

        $db->commit();
    } catch (Throwable $e) {
        $db->rollback();

        if ($e instanceof mysqli_sql_exception &&
            (int)$e->getCode() === 1062) {
            throw new RuntimeException(
                'You or this PC already have an active session.'
            );
        }

        throw $e;
    }
}

function monitoringTimeOut(mysqli $db, int $student): void {
    $q = $db->prepare("
        UPDATE computer_sessions
        SET time_out = NOW()
        WHERE active_student = ?
    ");
    $q->bind_param('i', $student);
    $q->execute();
    $changed = $q->affected_rows;
    $q->close();

    if (!$changed) {
        throw new RuntimeException('No active session found.');
    }
}

/** Shared by the administrator's recovery action; an ended session cannot be reopened. */
function monitoringAdminTimeOut(mysqli $db, int $sessionId): void {
    $q = $db->prepare('UPDATE computer_sessions SET time_out = NOW() WHERE session_id = ? AND time_out IS NULL');
    $q->bind_param('i', $sessionId);
    $q->execute();
    $changed = $q->affected_rows;
    $q->close();
    if (!$changed) {
        throw new RuntimeException('This session has already ended or no longer exists.');
    }
}
