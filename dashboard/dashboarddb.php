<?php
declare(strict_types=1);

/**
 * Fetches a user's public profile (no password) by StudentID.
 *
 * @return array{StudentID: int, firstname: string, lastname: string, course: string, yearlevel: string, created_at: string}|null
 */
function findUserProfile(mysqli $conn, string $studentId): ?array
{
    $stmt = mysqli_prepare(
        $conn,
        'SELECT StudentID, firstname, lastname, course, yearlevel, created_at FROM users WHERE StudentID = ?'
    );
    mysqli_stmt_bind_param($stmt, 'i', $studentId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);

    return $user ?: null;
}
