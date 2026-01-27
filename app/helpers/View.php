<?php

declare(strict_types=1);

class View
{
    /**
     * Render a view with optional data
     *
     * @param string $view The view file path (relative to app/views/)
     * @param array $data Data to pass to the view
     * @param bool $useLayout Whether to wrap the view in the layout
     */
    public static function render(string $view, array $data = [], bool $useLayout = true): void
    {
        // Reason: Extract data array to make variables available in view
        extract($data);

        // Start output buffering
        ob_start();

        // Load the view file
        $viewPath = APP_PATH . '/views/' . $view . '.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            die('View not found: ' . $view);
        }

        // Get the view content
        $content = ob_get_clean();

        // Wrap in layout if requested
        if ($useLayout) {
            require APP_PATH . '/views/layout.php';
        } else {
            echo $content;
        }
    }

    /**
     * Render a partial view without layout
     *
     * @param string $partial The partial file path (relative to app/views/)
     * @param array $data Data to pass to the partial
     */
    public static function partial(string $partial, array $data = []): void
    {
        self::render($partial, $data, false);
    }

    /**
     * Redirect to a URL
     *
     * @param string $url The URL to redirect to
     */
    public static function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Redirect back to the previous page or a default URL
     *
     * @param string $default The default URL if no referer is available
     */
    public static function redirectBack(string $default = '/'): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? $default;
        self::redirect($referer);
    }

    /**
     * Return a JSON response
     *
     * @param mixed $data The data to encode as JSON
     * @param int $statusCode The HTTP status code
     */
    public static function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Return a 404 error page
     */
    public static function notFound(): void
    {
        http_response_code(404);
        self::render('errors/404', [], true);
        exit;
    }

    /**
     * Get a setting value
     *
     * @param string $key The setting key
     * @param string|null $default The default value
     * @return string|null The setting value
     */
    public static function setting(string $key, ?string $default = null): ?string
    {
        $result = Database::fetchOne('SELECT value FROM settings WHERE key = ?', [$key]);
        return $result['value'] ?? $default;
    }

    /**
     * Generate a URL for a route
     *
     * @param string $path The path (starting with /)
     * @return string The full URL
     */
    public static function url(string $path = ''): string
    {
        return BASE_URL . $path;
    }

    /**
     * Generate an asset URL
     *
     * @param string $path The asset path
     * @return string The full asset URL
     */
    public static function asset(string $path): string
    {
        return BASE_URL . '/assets/' . ltrim($path, '/');
    }
}
