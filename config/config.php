<?php

// Load .env file if env_loader exists
if (file_exists(__DIR__ . '/env_loader.php')) {
    require_once __DIR__ . '/env_loader.php';
}

// Database Configuration - Load from environment variables (required)
if (!defined('HOST')) {
    define('HOST', getenv('DB_HOST') ?: 'localhost');
}
if (!defined('DBNAME')) {
    $dbName = getenv('DB_NAME');
    if (empty($dbName)) {
        throw new Exception('DB_NAME environment variable is required. Please set it in your .env file.');
    }
    define('DBNAME', $dbName);
}
if (!defined('USER')) {
    $dbUser = getenv('DB_USER');
    if (empty($dbUser)) {
        throw new Exception('DB_USER environment variable is required. Please set it in your .env file.');
    }
    define('USER', $dbUser);
}
if (!defined('PASS')) {
    $dbPass = getenv('DB_PASS');
    if ($dbPass === false) {
        throw new Exception('DB_PASS environment variable is required. Please set it in your .env file.');
    }
    define('PASS', $dbPass);
}

// Email Configuration - Load from environment variables (required)
if (!defined('HOST_MAIL')) {
    define('HOST_MAIL', getenv('MAIL_HOST') ?: '');
}
if (!defined('USERNAME')) {
    define('USERNAME', getenv('MAIL_USERNAME') ?: '');
}
if (!defined('PASSWORD')) {
    define('PASSWORD', getenv('MAIL_PASSWORD') ?: '');
}
if (!defined('SMTPSECURE')) {
    define('SMTPSECURE', getenv('MAIL_ENCRYPTION') ?: 'PHPMailer::ENCRYPTION_STARTTLS');
}
if (!defined('PORT')) {
    define('PORT', (int)(getenv('MAIL_PORT') ?: 587));
}
if (!defined('SMTPDEBUG')) {
    define('SMTPDEBUG', (int)(getenv('MAIL_DEBUG') ?: 0));
}
if (!defined('SENDERNAME')) {
    define('SENDERNAME', getenv('MAIL_SENDER_NAME') ?: '');
}

// config.ph
//define('BASE_URL', 'http://localhost:8000/tascesalary/');


?>
