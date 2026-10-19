<?php
declare(strict_types=1);

/**
 * Every account, newest first — the account-creation history.
 *
 * @return list<array{StudentID: int, firstname: string, lastname: string, course: string, yearlevel: string, created_at: string}>
 */
function getAllUsers(mysqli $conn): array
{
    $result = $conn->query(
        'SELECT StudentID, firstname, lastname, course, yearlevel, created_at
         FROM users
         ORDER BY created_at DESC, StudentID DESC'
    );

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
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
    try {
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

        return ['success' => true, 'error' => null];
    } catch (mysqli_sql_exception $e) {
        return ['success' => false, 'error' => 'Something went wrong while saving the account. Please try again.'];
    }
}

function deleteUser(mysqli $conn, string $studentId): bool
{
    try {
        $stmt = $conn->prepare('DELETE FROM users WHERE StudentID = ?');
        $stmt->bind_param('i', $studentId);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        return $affected > 0;
    } catch (mysqli_sql_exception $e) {
        return false;
    }
}
