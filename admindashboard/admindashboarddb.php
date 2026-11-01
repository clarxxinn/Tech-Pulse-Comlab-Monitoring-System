<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/validation.php';
require_once __DIR__ . '/../config/options.php';

/**
 * Every account, newest first — the account-creation history.
 *
 * @return list<array{StudentID: int, firstname: string, lastname: string, course: string, yearlevel: string, created_at: string}>
 */
function getAllUsers(mysqli $conn, int $page = 1, int $pageSize = 25): array
{
    $pageSize = max(1, min(100, $pageSize));
    $offset = (max(1, $page) - 1) * $pageSize;
    $stmt = $conn->prepare(
        'SELECT u.StudentID, u.firstname, u.lastname, u.course, u.yearlevel, u.created_at
         FROM (SELECT StudentID, created_at FROM users
               ORDER BY created_at DESC, StudentID DESC LIMIT ? OFFSET ?) AS account_page
         JOIN users u ON u.StudentID = account_page.StudentID
         ORDER BY account_page.created_at DESC, account_page.StudentID DESC'
    );
    $stmt->bind_param('ii', $pageSize, $offset);
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $users;
}

function getUserSummary(mysqli $conn): array
{
    return $conn->query('SELECT COUNT(*) AS total, MAX(created_at) AS latest FROM users')->fetch_assoc();
}

/**
 * A blank password keeps the current hash; StudentID stays unchanged.
 *
 * @return array{success: bool, error: ?string}
 */
function adminUpdateUser(
    mysqli $conn,
    string $studentId,
    string $firstName,
    string $lastName,
    string $course,
    string $yearLevel,
    string $newPassword = ''
): array {
    $firstName = trim($firstName);
    $lastName = trim($lastName);
    $errors = validateAccount($studentId, $firstName, $lastName, $course, $yearLevel, $newPassword, true);
    if ($errors) {
        return ['success' => false, 'error' => implode(' ', $errors)];
    }
    try {
        $conn->begin_transaction();
        $stmt = $conn->prepare('SELECT StudentID FROM users WHERE StudentID = ? FOR UPDATE');
        $stmt->bind_param('i', $studentId);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$exists) {
            $conn->rollback();
            return ['success' => false, 'error' => 'This account no longer exists.'];
        }
        if ($newPassword !== '') {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare(
                'UPDATE users SET firstname = ?, lastname = ?, course = ?, yearlevel = ?, password = ? WHERE StudentID = ?'
            );
            $stmt->bind_param('sssssi', $firstName, $lastName, $course, $yearLevel, $hashedPassword, $studentId);
        } else {
            $stmt = $conn->prepare(
                'UPDATE users SET firstname = ?, lastname = ?, course = ?, yearlevel = ? WHERE StudentID = ?'
            );
            $stmt->bind_param('ssssi', $firstName, $lastName, $course, $yearLevel, $studentId);
        }

        $stmt->execute();
        $stmt->close();
        $conn->commit();
        return ['success' => true, 'error' => null];
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        error_log('Tech Pulse account update failed: ' . $e->getMessage());
        return ['success' => false, 'error' => 'Something went wrong while saving the account. Please try again.'];
    }
}

/** Delete an account while preserving its anonymized completed usage records. */
function deleteUser(mysqli $conn, string $studentId): array
{
    if (!validStudentId($studentId)) {
        return ['success' => false, 'error' => 'Invalid Student ID.'];
    }
    try {
        $conn->begin_transaction();
        $stmt = $conn->prepare('SELECT StudentID FROM users WHERE StudentID = ? FOR UPDATE');
        $stmt->bind_param('i', $studentId);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$exists) {
            $conn->rollback();
            return ['success' => false, 'error' => 'This account no longer exists.'];
        }
        $stmt = $conn->prepare('SELECT session_id FROM computer_sessions WHERE active_student = ? LIMIT 1');
        $stmt->bind_param('i', $studentId);
        $stmt->execute();
        $active = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($active) {
            $conn->rollback();
            return ['success' => false, 'error' => 'Time out this student before deleting the account.'];
        }
        $stmt = $conn->prepare('UPDATE computer_sessions SET StudentID = NULL WHERE StudentID = ? AND time_out IS NOT NULL');
        $stmt->bind_param('i', $studentId);
        $stmt->execute();
        $stmt->close();
        $stmt = $conn->prepare('DELETE FROM users WHERE StudentID = ?');
        $stmt->bind_param('i', $studentId);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        $conn->commit();
        return ['success' => $affected > 0, 'error' => null];
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        error_log('Tech Pulse account deletion failed: ' . $e->getMessage());
        return ['success' => false, 'error' => 'Unable to delete the account. Please try again.'];
    }
}
