<?php
// Copy to local.php, supply your own credentials, and keep local.php out of Git.
// Generate an administrator hash with password_hash('your-password', PASSWORD_DEFAULT).
return [
    'DB_HOST' => 'localhost',
    'DB_PORT' => '3306',
    'DB_NAME' => 'techpulse',
    'DB_USER' => 'techpulse_app',
    'DB_PASSWORD' => 'replace-with-your-database-password',
    'ADMIN_USERNAME' => 'admin',
    'ADMIN_PASSWORD_HASH' => 'replace-with-a-password_hash-result',
];
