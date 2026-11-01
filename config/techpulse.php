<?php
declare(strict_types=1);

require_once __DIR__ . '/validation.php';

$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'techpulse';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
} catch (mysqli_sql_exception $e) {
    http_response_code(503);
    die('Connection failed. Check that MySQL is running and the techpulse database exists.');
}

$conn->set_charset('utf8mb4');
date_default_timezone_set('Asia/Manila');
$conn->query("SET SESSION time_zone = '+08:00'");

const ADMIN_USERNAME = 'admin';
const ADMIN_PASSWORD_HASH = '$2y$10$hSF2izQHZ3i63sHHcrcXzOJOucVoVCFMiOHsaRduAQCRSW6iaW2S6';
