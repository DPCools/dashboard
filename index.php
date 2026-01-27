<?php

declare(strict_types=1);

// Load configuration and autoloader
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/version.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/helpers/Security.php';
require_once __DIR__ . '/app/helpers/Auth.php';
require_once __DIR__ . '/app/helpers/View.php';
require_once __DIR__ . '/app/models/Page.php';
require_once __DIR__ . '/app/models/Item.php';
require_once __DIR__ . '/app/controllers/DashboardController.php';
require_once __DIR__ . '/app/controllers/AuthController.php';

// Reason: Load widget system for dashboard display
if (file_exists(__DIR__ . '/app/widgets/Widget.php')) {
    require_once __DIR__ . '/app/widgets/Widget.php';
    require_once __DIR__ . '/app/widgets/WidgetFactory.php';
    require_once __DIR__ . '/app/models/Widget.php';

    // Load all widget implementations
    foreach (glob(__DIR__ . '/app/widgets/*Widget.php') as $widgetFile) {
        require_once $widgetFile;
    }
}

// Start session
Auth::startSession();

// Reason: Simple routing based on URL path
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Parse the request URI to get the path without query string
$parsedUrl = parse_url($requestUri);
$path = $parsedUrl['path'] ?? '/';

// Remove the base path if present
$basePath = '/dashboard';
if (strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}

// Normalize path
if ($path === '' || $path === '/') {
    $path = '/';
}

// Route handling
try {
    // Dashboard routes
    if ($path === '/' || $path === '/index.php') {
        DashboardController::index();
        exit;
    }

    // Settings routes
    if ($path === '/settings' || $path === '/settings/') {
        Auth::requireAdmin();
        require_once __DIR__ . '/app/controllers/PageController.php';
        require_once __DIR__ . '/app/controllers/ItemController.php';
        $controller = new PageController();
        $controller->settings();
        exit;
    }

    // Page routes
    if (strpos($path, '/pages/') === 0) {
        Auth::requireAdmin();
        require_once __DIR__ . '/app/controllers/PageController.php';

        $controller = new PageController();

        if ($path === '/pages/create' && $requestMethod === 'GET') {
            $controller->create();
        } elseif ($path === '/pages/create' && $requestMethod === 'POST') {
            $controller->store();
        } elseif ($path === '/pages/edit' && $requestMethod === 'GET') {
            $controller->edit();
        } elseif ($path === '/pages/update' && $requestMethod === 'POST') {
            $controller->update();
        } elseif ($path === '/pages/delete' && $requestMethod === 'POST') {
            $controller->delete();
        } else {
            View::notFound();
        }
        exit;
    }

    // Item routes
    if (strpos($path, '/items/') === 0) {
        require_once __DIR__ . '/app/controllers/ItemController.php';

        $controller = new ItemController();

        // Status check endpoint (public - no auth required)
        if ($path === '/items/status' && $requestMethod === 'GET') {
            $controller->checkStatus();
            exit;
        }

        // Password verification endpoint (public - no auth required)
        if ($path === '/items/verify-password' && $requestMethod === 'POST') {
            $controller->verifyPassword();
            exit;
        }

        // All other item routes require admin auth
        Auth::requireAdmin();

        if ($path === '/items/create' && $requestMethod === 'GET') {
            $controller->create();
        } elseif ($path === '/items/create' && $requestMethod === 'POST') {
            $controller->store();
        } elseif ($path === '/items/edit' && $requestMethod === 'GET') {
            $controller->edit();
        } elseif ($path === '/items/update' && $requestMethod === 'POST') {
            $controller->update();
        } elseif ($path === '/items/delete' && $requestMethod === 'POST') {
            $controller->delete();
        } elseif ($path === '/items/reorder' && $requestMethod === 'POST') {
            $controller->reorder();
        } else {
            View::notFound();
        }
        exit;
    }

    // Health check route (no auth required - public endpoint)
    if ($path === '/health/check' && $requestMethod === 'GET') {
        require_once __DIR__ . '/app/controllers/HealthController.php';
        $controller = new HealthController();
        $controller->check();
        exit;
    }

    // Icon routes
    if (strpos($path, '/icons') === 0) {
        Auth::requireAdmin();
        require_once __DIR__ . '/app/controllers/IconController.php';

        $controller = new IconController();

        if ($path === '/icons' && $requestMethod === 'GET') {
            $controller->index();
        } elseif ($path === '/icons/upload' && $requestMethod === 'GET') {
            $controller->upload();
        } elseif ($path === '/icons/upload' && $requestMethod === 'POST') {
            $controller->store();
        } elseif ($path === '/icons/delete' && $requestMethod === 'POST') {
            $controller->delete();
        } elseif ($path === '/icons/scan' && $requestMethod === 'POST') {
            $controller->scan();
        } elseif ($path === '/icons/get' && $requestMethod === 'GET') {
            $controller->getIcons();
        } else {
            View::notFound();
        }
        exit;
    }

    // Widget routes
    if (strpos($path, '/widgets/') === 0) {
        // Reason: Load widget system files
        require_once __DIR__ . '/app/widgets/Widget.php';
        require_once __DIR__ . '/app/widgets/WidgetFactory.php';
        require_once __DIR__ . '/app/models/Widget.php';
        require_once __DIR__ . '/app/controllers/WidgetController.php';

        // Reason: Load all widget implementations
        foreach (glob(__DIR__ . '/app/widgets/*Widget.php') as $widgetFile) {
            require_once $widgetFile;
        }

        $controller = new WidgetController();

        // Reason: fetchData endpoint is public (no auth) for AJAX refresh
        if ($path === '/widgets/fetch-data' && $requestMethod === 'GET') {
            $controller->fetchData();
            exit;
        }

        // All other widget routes require admin auth
        Auth::requireAdmin();

        if ($path === '/widgets/create' && $requestMethod === 'GET') {
            $controller->create();
        } elseif ($path === '/widgets/create' && $requestMethod === 'POST') {
            $controller->store();
        } elseif ($path === '/widgets/edit' && $requestMethod === 'GET') {
            $controller->edit();
        } elseif ($path === '/widgets/update' && $requestMethod === 'POST') {
            $controller->update();
        } elseif ($path === '/widgets/delete' && $requestMethod === 'POST') {
            $controller->delete();
        } elseif ($path === '/widgets/reorder' && $requestMethod === 'POST') {
            $controller->reorder();
        } elseif ($path === '/widgets/clean-cache' && $requestMethod === 'POST') {
            $controller->cleanCache();
        } else {
            View::notFound();
        }
        exit;
    }

    // 404 for all other routes
    View::notFound();
} catch (Exception $e) {
    // Log error and show generic error page
    error_log('Application error: ' . $e->getMessage());
    http_response_code(500);
    echo '<h1>500 Internal Server Error</h1>';
    echo '<p>An error occurred while processing your request.</p>';

    if (Auth::isAdmin()) {
        echo '<pre>' . Security::escape($e->getMessage()) . '</pre>';
        echo '<pre>' . Security::escape($e->getTraceAsString()) . '</pre>';
    }
}
