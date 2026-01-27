<?php

declare(strict_types=1);

// Load configuration and autoloader
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/helpers/Security.php';
require_once __DIR__ . '/app/helpers/Auth.php';
require_once __DIR__ . '/app/helpers/View.php';
require_once __DIR__ . '/app/models/Page.php';
require_once __DIR__ . '/app/controllers/AuthController.php';

// Start session
Auth::startSession();

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    AuthController::showLoginForm();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AuthController::login();
} else {
    http_response_code(405);
    echo 'Method Not Allowed';
}
