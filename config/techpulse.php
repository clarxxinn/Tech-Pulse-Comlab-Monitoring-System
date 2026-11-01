<?php
declare(strict_types=1);

require_once __DIR__ . '/validation.php';

$localConfig = is_file(__DIR__ . '/local.php') ? require __DIR__ . '/local.php' : [];
function techpulseSetting(string $name, string $default = ''): string
{
    global $localConfig;
    $value = getenv($name);
    return $value !== false ? $value : (string) ($localConfig[$name] ?? $default);
}

date_default_timezone_set('Asia/Manila');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    if (techpulseSetting('DB_USER') === '' || techpulseSetting('ADMIN_PASSWORD_HASH') === '') {
        throw new RuntimeException('Configure config/local.php using config/local.example.php.');
    }
    $conn = new mysqli(
        techpulseSetting('DB_HOST', 'localhost'),
        techpulseSetting('DB_USER'),
        techpulseSetting('DB_PASSWORD'),
        techpulseSetting('DB_NAME', 'techpulse'),
        (int) techpulseSetting('DB_PORT', '3306')
    );
    $conn->set_charset('utf8mb4');
    $conn->query("SET SESSION time_zone = '+08:00'");
    $conn->query("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
} catch (Throwable $e) {
    error_log('Tech Pulse configuration/database failure: ' . $e->getMessage());
    http_response_code(503);
    exit('The system is temporarily unavailable. Please contact the administrator.');
}

define('ADMIN_USERNAME', techpulseSetting('ADMIN_USERNAME', 'admin'));
define('ADMIN_PASSWORD_HASH', techpulseSetting('ADMIN_PASSWORD_HASH'));
