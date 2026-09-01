<?php
declare(strict_types=1);

/**
 * Looks up a user by StudentID.
 *
 * @return array{StudentID: int, firstname: string, lastname: string, password: string}|null
 */
function findUserByStudentId(mysqli $conn, string $studentId): ?array
{
    $stmt = mysqli_prepare(
        $conn,
        'SELECT StudentID, firstname, lastname, password FROM users WHERE StudentID = ?'
    );
    mysqli_stmt_bind_param($stmt, 'i', $studentId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);

    return $user ?: null;
}