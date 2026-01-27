<?php

declare(strict_types=1);

// Application constants
define('APP_NAME', 'HomeDash');
define('APP_VERSION', '1.0.0');

// Path constants
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('DATA_PATH', BASE_PATH . '/data');
define('DATABASE_PATH', DATA_PATH . '/dashboard.db');

// URL constants
// Reason: Detect if running on HTTPS for secure cookie and redirect handling
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('BASE_URL', $protocol . $host . '/dashboard');

// Session constants
define('SESSION_LIFETIME', 7200); // 2 hours in seconds
define('SESSION_NAME', 'homedash_session');

// Security constants
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour in seconds

// Default settings
define('DEFAULT_ITEMS_PER_ROW', 4);
define('DEFAULT_THEME', 'system');
define('DEFAULT_SITE_TITLE', 'HomeDash');
