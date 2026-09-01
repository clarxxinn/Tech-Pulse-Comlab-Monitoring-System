<?php
declare(strict_types=1);

/**
 * Attempts to insert a new user into the database.
 *
 * @return array{success: bool, error: ?string}
 */
function registerUser(
    mysqli $conn,
    string $studentid,
    string $firstname,
    string $lastname,
    string $course,
    string $yearlevel,
    string $password
): array {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = mysqli_prepare(
        $conn,
        'INSERT INTO users (StudentID, firstname, lastname, course, yearlevel, password) VALUES (?, ?, ?, ?, ?, ?)'
    );
    mysqli_stmt_bind_param($stmt, 'isssss', $studentid, $firstname, $lastname, $course, $yearlevel, $hashedPassword);

    try {
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($success) {
            return ['success' => true, 'error' => null];
        }

        return ['success' => false, 'error' => 'Something went wrong while creating your account. Please try again.'];
    } catch (mysqli_sql_exception $e) {
        mysqli_stmt_close($stmt);

        if ($e->getCode() === 1062) {
            return ['success' => false, 'error' => 'That Student ID is already registered. Please check your ID and try again.'];
        }

        return ['success' => false, 'error' => 'Something went wrong while creating your account. Please try again.'];
    }
}