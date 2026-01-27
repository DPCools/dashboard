<?php

declare(strict_types=1);

// Load configuration and autoloader
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/app/helpers/Security.php';
require_once __DIR__ . '/app/helpers/Auth.php';
require_once __DIR__ . '/app/helpers/View.php';

// Start session and logout
Auth::logout();
View::redirect(BASE_URL);
