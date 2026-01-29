<?php
declare(strict_types=1);

/**
 * CommandController
 *
 * Handles remote command execution management interface
 */
class CommandController
{
    /**
     * Display main command management panel
     */
    public static function index(): void
    {
        Auth::requireAdmin();

        $tab = $_GET['tab'] ?? 'hosts';

        View::render('commands/index', [
            'tab' => $tab,
            'hosts' => CommandHost::getAll(),
            'templates' => CommandTemplate::getAllGroupedByCategory(),
            'categories' => CommandTemplate::getCategories(),
            'statistics' => CommandExecution::getStatistics()
        ]);
    }

    /**
     * Display hosts management tab
     */
    public static function hosts(): void
    {
        Auth::requireAdmin();

        $hosts = CommandHost::getAll();

        View::render('commands/hosts', [
            'hosts' => $hosts
        ]);
    }

    /**
     * Display templates management tab
     */
    public static function templates(): void
    {
        Auth::requireAdmin();

        $templates = CommandTemplate::getAllGroupedByCategory();
        $categories = CommandTemplate::getCategories();

        View::render('commands/templates', [
            'templates' => $templates,
            'categories' => $categories
        ]);
    }

    /**
     * Display execution history tab
     */
    public static function history(): void
    {
        Auth::requireAdmin();

        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $filters = [];
        if (!empty($_GET['host_id'])) {
            $filters['host_id'] = (int)$_GET['host_id'];
        }
        if (!empty($_GET['template_id'])) {
            $filters['template_id'] = (int)$_GET['template_id'];
        }
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }

        $executions = CommandExecution::getAll($filters, $limit, $offset);
        $totalCount = CommandExecution::getCount($filters);
        $totalPages = (int)ceil($totalCount / $limit);

        View::render('commands/history', [
            'executions' => $executions,
            'filters' => $filters,
            'page' => $page,
            'totalPages' => $totalPages,
            'hosts' => CommandHost::getAll(),
            'templates' => CommandTemplate::getAll()
        ]);
    }

    /**
     * Create a new host (POST)
     */
    public static function storeHost(): void
    {
        Auth::requireAdmin();

        // Debug logging (removed - was exposing passwords)

        $providedToken = $_POST['csrf_token'] ?? null;
        if (!Security::validateCSRFToken($providedToken)) {
            // Debug logging for CSRF issues
            error_log('CSRF validation failed in storeHost');
            error_log('Provided token: ' . ($providedToken ? substr($providedToken, 0, 20) . '...' : 'NULL'));
            error_log('Session has token: ' . (isset($_SESSION[CSRF_TOKEN_NAME]) ? 'YES' : 'NO'));

            self::jsonResponse(['success' => false, 'error' => 'Invalid CSRF token. Please refresh the page (F5) and try again.'], 403);
            return;
        }

        try {
            // Parse su elevation settings
            $connectionSettings = [];

            if (!empty($_POST['use_su_elevation'])) {
                $suUsername = trim($_POST['su_username'] ?? '');
                $suPassword = trim($_POST['su_password'] ?? '');
                $suShell = trim($_POST['su_shell'] ?? '/bin/bash');

                if (empty($suUsername) || empty($suPassword)) {
                    throw new InvalidArgumentException('Su username and password are required when su elevation is enabled');
                }

                $connectionSettings['use_su_elevation'] = true;
                $connectionSettings['su_username'] = $suUsername;
                $connectionSettings['su_shell'] = $suShell;
            }

            $data = [
                'name' => $_POST['name'] ?? '',
                'host_type' => $_POST['host_type'] ?? 'ssh',
                'hostname' => $_POST['hostname'] ?? '',
                'port' => (int)($_POST['port'] ?? 22),
                'username' => $_POST['username'] ?? '',
                'connection_settings' => !empty($connectionSettings) ? $connectionSettings : null
            ];

            $hostId = CommandHost::create($data);

            // Store credentials if provided
            if (!empty($_POST['ssh_key'])) {
                CommandHost::storeCredential($hostId, 'ssh_key', $_POST['ssh_key']);
            }
            if (!empty($_POST['password'])) {
                CommandHost::storeCredential($hostId, 'password', $_POST['password']);
            }

            // Store su password if su elevation is enabled
            if (!empty($_POST['use_su_elevation']) && !empty($_POST['su_password'])) {
                CommandHost::storeSuPassword($hostId, $_POST['su_password']);
            }

            self::jsonResponse(['success' => true, 'host_id' => $hostId]);
        } catch (Exception $e) {
            // Reason: Provide user-friendly error messages for common issues
            $errorMessage = $e->getMessage();

            // Check for duplicate host constraint violation
            if (strpos($errorMessage, 'UNIQUE constraint failed: command_hosts.hostname') !== false) {
                $errorMessage = 'A host with this hostname, port, and username already exists. Please use a different combination or edit the existing host.';
            }

            self::jsonResponse(['success' => false, 'error' => $errorMessage], 400);
        }
    }

    /**
     * Update an existing host (POST)
     */
    public static function updateHost(): void
    {
        Auth::requireAdmin();

        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? null)) {
            self::jsonResponse(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        try {
            $hostId = (int)($_POST['id'] ?? 0);

            if ($hostId <= 0) {
                throw new InvalidArgumentException('Invalid host ID');
            }

            $data = [];
            if (isset($_POST['name'])) $data['name'] = $_POST['name'];
            if (isset($_POST['host_type'])) $data['host_type'] = $_POST['host_type'];
            if (isset($_POST['hostname'])) $data['hostname'] = $_POST['hostname'];
            if (isset($_POST['port'])) $data['port'] = (int)$_POST['port'];
            if (isset($_POST['username'])) $data['username'] = $_POST['username'];

            // Handle su elevation settings update
            // Note: Unchecked checkboxes don't send POST data, so we must always process this during updates
            $connectionSettings = [];

            if (!empty($_POST['use_su_elevation'])) {
                // Checkbox is CHECKED - enable su elevation
                $suUsername = trim($_POST['su_username'] ?? '');
                $suPassword = trim($_POST['su_password'] ?? '');
                $suShell = trim($_POST['su_shell'] ?? '/bin/bash');

                if (empty($suUsername)) {
                    throw new InvalidArgumentException('Su username is required when su elevation is enabled');
                }

                $connectionSettings['use_su_elevation'] = true;
                $connectionSettings['su_username'] = $suUsername;
                $connectionSettings['su_shell'] = $suShell;

                // Update su password if provided
                if (!empty($suPassword)) {
                    CommandHost::storeSuPassword($hostId, $suPassword);
                }
            }
            // If checkbox is NOT in POST (unchecked), $connectionSettings remains empty array

            // Always update connection_settings (even if empty) to clear su elevation when disabled
            $data['connection_settings'] = $connectionSettings;

            CommandHost::update($hostId, $data);

            // Update credentials if provided
            if (isset($_POST['ssh_key'])) {
                CommandHost::storeCredential($hostId, 'ssh_key', $_POST['ssh_key']);
            }
            if (isset($_POST['password'])) {
                CommandHost::storeCredential($hostId, 'password', $_POST['password']);
            }

            self::jsonResponse(['success' => true]);
        } catch (Exception $e) {
            // Reason: Provide user-friendly error messages
            $errorMessage = $e->getMessage();

            if (strpos($errorMessage, 'UNIQUE constraint failed: command_hosts.hostname') !== false) {
                $errorMessage = 'A host with this hostname, port, and username already exists. Please use a different combination.';
            }

            self::jsonResponse(['success' => false, 'error' => $errorMessage], 400);
        }
    }

    /**
     * Get a single host by ID (GET - AJAX)
     */
    public static function getHost(): void
    {
        Auth::requireAdmin();

        try {
            $hostId = (int)($_GET['id'] ?? 0);

            if ($hostId <= 0) {
                self::jsonResponse(['success' => false, 'error' => 'Invalid host ID'], 400);
                return;
            }

            $host = CommandHost::getById($hostId);

            if (!$host) {
                self::jsonResponse(['success' => false, 'error' => 'Host not found'], 404);
                return;
            }

            // Check if host has SSH key credential
            $host['has_ssh_key'] = CommandHost::hasCredential($hostId, 'ssh_key');

            // Don't expose actual credentials in response for security
            self::jsonResponse([
                'success' => true,
                'host' => $host
            ]);
        } catch (Exception $e) {
            self::jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Delete a host (POST)
     */
    public static function deleteHost(): void
    {
        Auth::requireAdmin();

        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? null)) {
            self::jsonResponse(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        try {
            $hostId = (int)($_POST['id'] ?? 0);

            if ($hostId <= 0) {
                throw new InvalidArgumentException('Invalid host ID');
            }

            CommandHost::delete($hostId);

            self::jsonResponse(['success' => true]);
        } catch (Exception $e) {
            self::jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Test host connection (POST - AJAX)
     */
    public static function testConnection(): void
    {
        Auth::requireAdmin();

        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? null)) {
            self::jsonResponse(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        try {
            $hostId = (int)($_POST['host_id'] ?? 0);

            if ($hostId <= 0) {
                throw new InvalidArgumentException('Invalid host ID');
            }

            $result = CommandExecutor::testConnection($hostId);

            self::jsonResponse([
                'success' => $result['success'],
                'error' => $result['error'],
                'time_ms' => $result['time_ms'],
                'message' => $result['message'] ?? null
            ]);
        } catch (Exception $e) {
            self::jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Create a new template (POST)
     */
    public static function storeTemplate(): void
    {
        Auth::requireAdmin();

        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? null)) {
            self::jsonResponse(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        try {
            $data = [
                'name' => $_POST['name'] ?? '',
                'category' => $_POST['category'] ?? '',
                'command_template' => $_POST['command_template'] ?? '',
                'description' => $_POST['description'] ?? null,
                'host_types' => json_decode($_POST['host_types'] ?? '[]', true),
                'parameters' => json_decode($_POST['parameters'] ?? '[]', true),
                'timeout_seconds' => (int)($_POST['timeout_seconds'] ?? 30),
                'requires_confirmation' => (int)($_POST['requires_confirmation'] ?? 0)
            ];

            $templateId = CommandTemplate::create($data);

            self::jsonResponse(['success' => true, 'template_id' => $templateId]);
        } catch (Exception $e) {
            self::jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Update an existing template (POST)
     */
    public static function updateTemplate(): void
    {
        Auth::requireAdmin();

        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? null)) {
            self::jsonResponse(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        try {
            $templateId = (int)($_POST['id'] ?? 0);

            if ($templateId <= 0) {
                throw new InvalidArgumentException('Invalid template ID');
            }

            $data = [];
            if (isset($_POST['name'])) $data['name'] = $_POST['name'];
            if (isset($_POST['category'])) $data['category'] = $_POST['category'];
            if (isset($_POST['command_template'])) $data['command_template'] = $_POST['command_template'];
            if (isset($_POST['description'])) $data['description'] = $_POST['description'];
            if (isset($_POST['host_types'])) $data['host_types'] = json_decode($_POST['host_types'], true);
            if (isset($_POST['parameters'])) $data['parameters'] = json_decode($_POST['parameters'], true);
            if (isset($_POST['timeout_seconds'])) $data['timeout_seconds'] = (int)$_POST['timeout_seconds'];
            if (isset($_POST['requires_confirmation'])) $data['requires_confirmation'] = (int)$_POST['requires_confirmation'];

            CommandTemplate::update($templateId, $data);

            self::jsonResponse(['success' => true]);
        } catch (Exception $e) {
            self::jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Delete a template (POST)
     */
    public static function deleteTemplate(): void
    {
        Auth::requireAdmin();

        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? null)) {
            self::jsonResponse(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        try {
            $templateId = (int)($_POST['id'] ?? 0);

            if ($templateId <= 0) {
                throw new InvalidArgumentException('Invalid template ID');
            }

            CommandTemplate::delete($templateId);

            self::jsonResponse(['success' => true]);
        } catch (Exception $e) {
            self::jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Execute a command (POST - AJAX)
     */
    public static function execute(): void
    {
        // Reason: Allow execution if user is admin OR has unlocked the page where the widget is located
        $widgetId = (int)($_POST['widget_id'] ?? 0);
        $hasAccess = Auth::isAdmin();

        // If not admin, check if user has unlocked the page this widget is on
        if (!$hasAccess && $widgetId > 0) {
            require_once APP_PATH . '/models/Widget.php';
            $widget = WidgetModel::find($widgetId);
            if ($widget) {
                $hasAccess = Auth::isPageUnlocked((int)$widget['page_id']);
            }
        }

        if (!$hasAccess) {
            self::jsonResponse(['success' => false, 'error' => 'Authentication required. Please unlock this page or log in as admin.'], 401);
            return;
        }

        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? null)) {
            // Check if CSRF token expired (force logout)
            if (Security::isCSRFExpired()) {
                Security::forceLogout();
                self::jsonResponse([
                    'success' => false,
                    'error' => 'Your session has expired. Please log in again.',
                    'expired' => true,
                    'redirect' => View::url('/login.php')
                ], 401);
                return;
            }

            self::jsonResponse(['success' => false, 'error' => 'Invalid CSRF token. Please refresh the page.'], 403);
            return;
        }

        try {
            $templateId = (int)($_POST['template_id'] ?? 0);
            $hostId = (int)($_POST['host_id'] ?? 0);
            $parameters = json_decode($_POST['parameters'] ?? '{}', true);

            if ($templateId <= 0 || $hostId <= 0) {
                throw new InvalidArgumentException('Invalid template or host ID');
            }

            $result = CommandExecutor::execute($templateId, $hostId, $parameters);

            self::jsonResponse($result);
        } catch (Exception $e) {
            self::jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * View execution details
     */
    public static function showExecution(): void
    {
        Auth::requireAdmin();

        $executionId = (int)($_GET['id'] ?? 0);

        if ($executionId <= 0) {
            View::notFound();
            return;
        }

        $execution = CommandExecution::getById($executionId);

        if (!$execution) {
            View::notFound();
            return;
        }

        View::render('commands/execution', [
            'execution' => $execution,
            'parameters' => CommandExecution::getParameters($execution)
        ]);
    }

    /**
     * Get compatible templates for a host (AJAX)
     */
    public static function getCompatibleTemplates(): void
    {
        Auth::requireAdmin();

        try {
            $hostId = (int)($_GET['host_id'] ?? 0);

            if ($hostId <= 0) {
                throw new InvalidArgumentException('Invalid host ID');
            }

            $templates = CommandTemplate::getCompatibleWithHost($hostId);

            self::jsonResponse(['success' => true, 'templates' => $templates]);
        } catch (Exception $e) {
            self::jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Helper: Send JSON response
     */
    private static function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
