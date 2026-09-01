<?php
declare(strict_types=1);

/**
 * Data-access helpers for the admin dashboard. Pure functions over the shared
 * mysqli $conn using prepared statements — mirrors auth/login/logindb.php and
 * dashboard/dashboarddb.php.
 *
 * Account creation reuses registerUser() (auth/register/registerdb.php) and
 * single-profile reads reuse findUserProfile() (dashboard/dashboarddb.php), so
 * this file only holds the admin-only list / update / delete operations.
 */

/**
 * Every account, newest first — the account-creation history.
 *
 * @return list<array{StudentID: int, firstname: string, lastname: string, course: string, yearlevel: string, created_at: string}>
 */
function getAllUsers(mysqli $conn): array
{
    $result = mysqli_query(
        $conn,
        'SELECT StudentID, firstname, lastname, course, yearlevel, created_at
         FROM users
         ORDER BY created_at DESC, StudentID DESC'
    );

    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

/**
 * Updates an account's profile fields. The password is only rewritten when
 * $newPassword is a non-empty string (blank/null = keep the current password).
 * StudentID is the primary key / login identifier and is never changed here.
 *
 * @return array{success: bool, error: ?string}
 */
function adminUpdateUser(
    mysqli $conn,
    string $studentId,
    string $firstname,
    string $lastname,
    string $course,
    string $yearlevel,
    ?string $newPassword = null
): array {
    try {
        if ($newPassword !== null && $newPassword !== '') {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare(
                $conn,
                'UPDATE users SET firstname = ?, lastname = ?, course = ?, yearlevel = ?, password = ? WHERE StudentID = ?'
            );
            mysqli_stmt_bind_param($stmt, 'sssssi', $firstname, $lastname, $course, $yearlevel, $hashedPassword, $studentId);
        } else {
            $stmt = mysqli_prepare(
                $conn,
                'UPDATE users SET firstname = ?, lastname = ?, course = ?, yearlevel = ? WHERE StudentID = ?'
            );
            mysqli_stmt_bind_param($stmt, 'ssssi', $firstname, $lastname, $course, $yearlevel, $studentId);
        }

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return ['success' => true, 'error' => null];
    } catch (mysqli_sql_exception $e) {
        return ['success' => false, 'error' => 'Something went wrong while saving the account. Please try again.'];
    }
}

function deleteUser(mysqli $conn, string $studentId): bool
{
    try {
        $stmt = mysqli_prepare($conn, 'DELETE FROM users WHERE StudentID = ?');
        mysqli_stmt_bind_param($stmt, 'i', $studentId);
        mysqli_stmt_execute($stmt);
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        return $affected > 0;
    } catch (mysqli_sql_exception $e) {
        return false;
    }
}
