<?php
declare(strict_types=1);

function inputString(array $values, string $key): string
{
    return isset($values[$key]) && is_string($values[$key]) ? $values[$key] : '';
}

function validStudentId(string $studentId): bool
{
    return preg_match('/^[1-9][0-9]{0,9}$/D', $studentId) === 1
        && (strlen($studentId) < 10 || strcmp($studentId, '2147483647') <= 0);
}

/** @return list<string> */
function validateAccount(
    string $studentId,
    string $firstName,
    string $lastName,
    string $course,
    string $yearLevel,
    string $password,
    bool $passwordOptional = false
): array {
    global $allowedCourses, $allowedYearLevels;
    $errors = [];
    if (!validStudentId($studentId)) {
        $errors[] = 'Student ID must be 1 to 2147483647, without leading zeros.';
    }
    foreach (['first' => $firstName, 'last' => $lastName] as $label => $name) {
        if (!mb_check_encoding($name, 'UTF-8') || trim($name) === '' || mb_strlen($name, 'UTF-8') > 67 || preg_match('/[\x00-\x1F\x7F]/u', $name)) {
            $errors[] = "Please enter a valid {$label} name (up to 67 characters).";
        }
    }
    if (!array_key_exists($course, $allowedCourses)) {
        $errors[] = 'Please select a valid course.';
    }
    if (!in_array($yearLevel, $allowedYearLevels, true)) {
        $errors[] = 'Please select a valid year level.';
    }
    if ((!$passwordOptional || $password !== '') && (strlen($password) < 8 || strlen($password) > 72 || str_contains($password, "\0"))) {
        $errors[] = 'Password must contain 8 to 72 bytes and no null characters.';
    }
    return $errors;
}
