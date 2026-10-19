<?php
declare(strict_types=1);

/**
 * Attempts to insert a new user into the database.
 *
 * @return array{success: bool, error: ?string}
 */
function registerUser(
    mysqli $conn,
    string $studentId,
    string $firstName,
    string $lastName,
    string $course,
    string $yearLevel,
    string $password
): array {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        'INSERT INTO users (StudentID, firstname, lastname, course, yearlevel, password) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('isssss', $studentId, $firstName, $lastName, $course, $yearLevel, $hashedPassword);

    try {
        $success = $stmt->execute();
        $stmt->close();

        if ($success) {
            return ['success' => true, 'error' => null];
        }

        return ['success' => false, 'error' => 'Something went wrong while creating your account. Please try again.'];
    } catch (mysqli_sql_exception $e) {
        $stmt->close();

        if ($e->getCode() === 1062) {
            return ['success' => false, 'error' => 'That Student ID is already registered. Please check your ID and try again.'];
        }

        return ['success' => false, 'error' => 'Something went wrong while creating your account. Please try again.'];
    }
}