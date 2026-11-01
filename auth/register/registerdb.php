<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/validation.php';
require_once __DIR__ . '/../../config/options.php';

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
    $firstName = trim($firstName);
    $lastName = trim($lastName);
    $errors = validateAccount($studentId, $firstName, $lastName, $course, $yearLevel, $password);
    if ($errors) {
        return ['success' => false, 'error' => implode(' ', $errors)];
    }
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $conn->prepare(
            'INSERT INTO users (StudentID, firstname, lastname, course, yearlevel, password) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('isssss', $studentId, $firstName, $lastName, $course, $yearLevel, $hashedPassword);

        $success = $stmt->execute();
        $stmt->close();

        if ($success) {
            return ['success' => true, 'error' => null];
        }

        return ['success' => false, 'error' => 'Something went wrong while creating your account. Please try again.'];
    } catch (mysqli_sql_exception $e) {
        if (isset($stmt)) {
            $stmt->close();
        }

        if ($e->getCode() === 1062) {
            return ['success' => false, 'error' => 'That Student ID is already registered. Please check your ID and try again.'];
        }

        error_log('Tech Pulse registration failed: ' . $e->getMessage());
        return ['success' => false, 'error' => 'Something went wrong while creating your account. Please try again.'];
    }
}
