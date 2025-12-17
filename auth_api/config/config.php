<?php
// Load .env file if env_loader exists
$envLoaderPath = dirname(__DIR__, 2) . '/config/env_loader.php';
if (file_exists($envLoaderPath)) {
    require_once $envLoaderPath;
}

return [
    'database' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'username' => getenv('DB_USER') ?: '',
        'password' => getenv('DB_PASS') ?: '',
        'dbname' => getenv('DB_NAME') ?: ''
    ],
    'jwt' => [
        'secret' => getenv('JWT_SECRET') ?: '',
        'expiry' => (int)(getenv('JWT_EXPIRY') ?: 3600) // 1 hour in seconds
    ]
];