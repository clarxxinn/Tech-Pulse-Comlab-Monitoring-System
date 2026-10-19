<?php
declare(strict_types=1);

/**
 * Fetches a user's public profile (no password) by StudentID.
 *
 * @return array{StudentID: int, firstname: string, lastname: string, course: string, yearlevel: string, created_at: string}|null
 */
function findUserProfile(mysqli $conn, string $studentId): ?array
{
    $stmt = $conn->prepare(
        'SELECT StudentID, firstname, lastname, course, yearlevel, created_at FROM users WHERE StudentID = ?'
    );
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $user ?: null;
}
