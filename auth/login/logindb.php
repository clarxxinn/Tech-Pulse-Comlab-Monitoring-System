<?php
declare(strict_types=1);

/**
 * Looks up a user by StudentID.
 *
 * @return array{StudentID: int, firstname: string, lastname: string, password: string}|null
 */
function findUserByStudentId(mysqli $conn, string $studentId): ?array
{
    $stmt = $conn->prepare(
        'SELECT StudentID, firstname, lastname, password FROM users WHERE StudentID = ?'
    );
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $user ?: null;
}