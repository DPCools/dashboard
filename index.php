<?php

declare(strict_types=1);

// Load configuration and autoloader
require_once __DIR__ . '/config/constants.php';
// Note: version.php is loaded by constants.php
require_once __DIR__ . '/config/database.php';

// Load Composer autoloader for external dependencies
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}
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

// Reason: Load command system files (required by CommandWidget)
if (file_exists(__DIR__ . '/app/models/CommandHost.php')) {
    require_once __DIR__ . '/app/models/CommandHost.php';
    require_once __DIR__ . '/app/models/CommandTemplate.php';
    require_once __DIR__ . '/app/models/CommandExecution.php';
    require_once __DIR__ . '/app/helpers/CredentialEncryption.php';
    require_once __DIR__ . '/app/helpers/CommandValidator.php';
}

// Start session
Auth::startSession();

// Reason: Simple routing based on URL path
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Parse the request URI to get the path without query string
$parsedUrl = parse_url($requestUri);
$path = $parsedUrl['path'] ?? '/';

// Determine the base path to strip from REQUEST_URI
// This is the actual filesystem path where the app is installed
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$scriptBasePath = str_replace('\\', '/', dirname($scriptName));
$scriptBasePath = ($scriptBasePath === '/' || $scriptBasePath === '.') ? '' : $scriptBasePath;

// Remove the script base path if present in the request path
if (!empty($scriptBasePath) && strpos($path, $scriptBasePath) === 0) {
    $path = substr($path, strlen($scriptBasePath));
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

    if ($path === '/settings/update' && $requestMethod === 'POST') {
        Auth::requireAdmin();
        require_once __DIR__ . '/app/controllers/PageController.php';
        $controller = new PageController();
        $controller->updateGlobalSettings();
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

    // File sharing routes
    if (strpos($path, '/files') === 0) {
        require_once __DIR__ . '/app/helpers/FileHelper.php';
        require_once __DIR__ . '/app/models/SharedFile.php';
        require_once __DIR__ . '/app/models/SharedFileAccessLog.php';
        require_once __DIR__ . '/app/controllers/FileShareController.php';

        $controller = new FileShareController();

        // All admin routes require authentication
        if ($path === '/files' || $path === '/files/') {
            Auth::requireAdmin();
            $controller->index();
        } elseif ($path === '/files/upload' && $requestMethod === 'GET') {
            Auth::requireAdmin();
            $controller->upload();
        } elseif ($path === '/files/upload' && $requestMethod === 'POST') {
            Auth::requireAdmin();
            $controller->store();
        } elseif ($path === '/files/edit' && $requestMethod === 'GET') {
            Auth::requireAdmin();
            $controller->edit();
        } elseif ($path === '/files/update' && $requestMethod === 'POST') {
            Auth::requireAdmin();
            $controller->update();
        } elseif ($path === '/files/delete' && $requestMethod === 'POST') {
            Auth::requireAdmin();
            $controller->delete();
        } elseif ($path === '/files/cleanup' && $requestMethod === 'POST') {
            Auth::requireAdmin();
            $controller->cleanup();
        } else {
            View::notFound();
        }
        exit;
    }

    // Public share link routes (/s/{token})
    if (preg_match('#^/s/([a-f0-9]{64})$#', $path, $matches)) {
        require_once __DIR__ . '/app/helpers/FileHelper.php';
        require_once __DIR__ . '/app/models/SharedFile.php';
        require_once __DIR__ . '/app/models/SharedFileAccessLog.php';
        require_once __DIR__ . '/app/controllers/FileShareController.php';

        $_GET['token'] = $matches[1];
        $controller = new FileShareController();
        $controller->show();
        exit;
    }

    // Password verification route (AJAX, public)
    if (preg_match('#^/s/([a-f0-9]{64})/verify$#', $path, $matches) && $requestMethod === 'POST') {
        require_once __DIR__ . '/app/helpers/FileHelper.php';
        require_once __DIR__ . '/app/models/SharedFile.php';
        require_once __DIR__ . '/app/models/SharedFileAccessLog.php';
        require_once __DIR__ . '/app/controllers/FileShareController.php';

        $_POST['token'] = $matches[1];
        $controller = new FileShareController();
        $controller->verifyPassword();
        exit;
    }

    // Download route (public)
    if (preg_match('#^/s/([a-f0-9]{64})/download$#', $path, $matches)) {
        require_once __DIR__ . '/app/helpers/FileHelper.php';
        require_once __DIR__ . '/app/models/SharedFile.php';
        require_once __DIR__ . '/app/models/SharedFileAccessLog.php';
        require_once __DIR__ . '/app/controllers/FileShareController.php';

        $_GET['token'] = $matches[1];
        $controller = new FileShareController();
        $controller->download();
        exit;
    }

    // Command execution routes
    if (strpos($path, '/commands') === 0) {
        // Reason: Load additional command execution dependencies (models/helpers already loaded globally)
        require_once __DIR__ . '/app/services/transport/TransportInterface.php';
        require_once __DIR__ . '/app/services/transport/SshTransport.php';
        require_once __DIR__ . '/app/services/transport/TransportFactory.php';
        require_once __DIR__ . '/app/services/CommandExecutor.php';
        require_once __DIR__ . '/app/controllers/CommandController.php';

        // Reason: Execute endpoint handles auth internally to return JSON errors for AJAX
        if ($path === '/commands/execute' && $requestMethod === 'POST') {
            CommandController::execute();
            exit;
        }

        // All other command routes require admin authentication
        Auth::requireAdmin();

        if ($path === '/commands' || $path === '/commands/') {
            CommandController::index();
        } elseif ($path === '/commands/hosts' && $requestMethod === 'GET') {
            CommandController::hosts();
        } elseif ($path === '/commands/templates' && $requestMethod === 'GET') {
            CommandController::templates();
        } elseif ($path === '/commands/history' && $requestMethod === 'GET') {
            CommandController::history();
        } elseif ($path === '/commands/hosts/create' && $requestMethod === 'POST') {
            CommandController::storeHost();
        } elseif ($path === '/commands/hosts/update' && $requestMethod === 'POST') {
            CommandController::updateHost();
        } elseif ($path === '/commands/hosts/get' && $requestMethod === 'GET') {
            CommandController::getHost();
        } elseif ($path === '/commands/hosts/delete' && $requestMethod === 'POST') {
            CommandController::deleteHost();
        } elseif ($path === '/commands/hosts/test' && $requestMethod === 'POST') {
            CommandController::testConnection();
        } elseif ($path === '/commands/templates/create' && $requestMethod === 'POST') {
            CommandController::storeTemplate();
        } elseif ($path === '/commands/templates/update' && $requestMethod === 'POST') {
            CommandController::updateTemplate();
        } elseif ($path === '/commands/templates/delete' && $requestMethod === 'POST') {
            CommandController::deleteTemplate();
        } elseif ($path === '/commands/templates/compatible' && $requestMethod === 'GET') {
            CommandController::getCompatibleTemplates();
        } elseif (preg_match('#^/commands/execution/(\d+)$#', $path, $matches)) {
            $_GET['id'] = $matches[1];
            CommandController::showExecution();
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
