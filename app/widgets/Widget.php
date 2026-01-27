<?php

declare(strict_types=1);

/**
 * Abstract base class for all widgets
 *
 * Provides common functionality for widget rendering, caching, and data management
 */
abstract class Widget
{
    protected int $id;
    protected int $pageId;
    protected string $widgetType;
    protected string $title;
    protected string $icon;
    protected string $iconType;
    protected int $displayOrder;
    protected array $settings;
    protected int $refreshInterval;
    protected bool $isEnabled;

    /**
     * Constructor to initialize widget from database record
     *
     * @param array $data Widget data from database
     */
    public function __construct(array $data)
    {
        $this->id = (int) $data['id'];
        $this->pageId = (int) $data['page_id'];
        $this->widgetType = $data['widget_type'];
        $this->title = $data['title'];
        $this->icon = $data['icon'] ?? 'box';
        $this->iconType = $data['icon_type'] ?? 'lucide';
        $this->displayOrder = (int) ($data['display_order'] ?? 0);

        // Reason: Decode JSON settings, fallback to empty array if invalid
        $this->settings = !empty($data['settings'])
            ? json_decode($data['settings'], true) ?? []
            : [];

        $this->refreshInterval = (int) ($data['refresh_interval'] ?? 300);
        $this->isEnabled = (bool) ($data['is_enabled'] ?? true);
    }

    /**
     * Abstract method to fetch widget data
     * Must be implemented by each widget type
     *
     * @return array Widget-specific data to be displayed
     */
    abstract public function getData(): array;

    /**
     * Abstract method to validate widget settings
     * Must be implemented by each widget type
     *
     * @param array $settings Settings to validate
     * @return array Array with 'valid' (bool) and 'errors' (array) keys
     */
    abstract public function validateSettings(array $settings): array;

    /**
     * Abstract method to get default settings for this widget type
     * Must be implemented by each widget type
     *
     * @return array Default settings
     */
    abstract public function getDefaultSettings(): array;

    /**
     * Render the widget HTML
     *
     * @return string Rendered HTML
     */
    public function render(): string
    {
        try {
            $data = $this->getData();

            // Reason: If getData returns an error, render error message
            if (isset($data['error'])) {
                return $this->renderError($data['error']);
            }

            // Reason: Load widget-specific view template
            $viewPath = APP_PATH . '/views/widgets/' . $this->widgetType . '.php';

            if (!file_exists($viewPath)) {
                return $this->renderError('Widget view template not found');
            }

            // Reason: Extract variables for view template
            $widget = $this;
            $settings = $this->settings;

            ob_start();
            require $viewPath;
            return ob_get_clean();

        } catch (Exception $e) {
            return $this->renderError('Widget error: ' . $e->getMessage());
        }
    }

    /**
     * Render an error message
     *
     * @param string $message Error message
     * @return string Rendered error HTML
     */
    protected function renderError(string $message): string
    {
        return '<p class="text-sm text-red-600 dark:text-red-400">'
            . Security::escape($message)
            . '</p>';
    }

    /**
     * Get cached data for this widget
     *
     * @param string $cacheKey Cache key
     * @return mixed|null Cached data or null if not found/expired
     */
    protected function getCachedData(string $cacheKey)
    {
        $result = Database::fetchOne(
            'SELECT cache_value, expires_at FROM widget_cache WHERE widget_id = ? AND cache_key = ?',
            [$this->id, $cacheKey]
        );

        if ($result === null) {
            return null;
        }

        // Reason: Check if cache is expired
        if ($result['expires_at'] < time()) {
            $this->deleteCachedData($cacheKey);
            return null;
        }

        // Reason: Decode JSON cached data
        return json_decode($result['cache_value'], true);
    }

    /**
     * Store data in cache
     *
     * @param string $cacheKey Cache key
     * @param mixed $data Data to cache
     * @param int $ttl Time to live in seconds
     */
    protected function setCachedData(string $cacheKey, $data, int $ttl = 300): void
    {
        $expiresAt = time() + $ttl;
        $cacheValue = json_encode($data);

        // Reason: Use INSERT OR REPLACE to update existing cache or create new
        Database::query(
            'INSERT OR REPLACE INTO widget_cache (widget_id, cache_key, cache_value, expires_at, created_at)
             VALUES (?, ?, ?, ?, datetime("now"))',
            [$this->id, $cacheKey, $cacheValue, $expiresAt]
        );
    }

    /**
     * Delete cached data
     *
     * @param string $cacheKey Cache key
     */
    protected function deleteCachedData(string $cacheKey): void
    {
        Database::delete(
            'DELETE FROM widget_cache WHERE widget_id = ? AND cache_key = ?',
            [$this->id, $cacheKey]
        );
    }

    /**
     * Make an HTTP request with timeout
     *
     * @param string $url URL to fetch
     * @param int $timeout Timeout in seconds
     * @return string|null Response body or null on failure
     */
    protected function httpGet(string $url, int $timeout = 10): ?string
    {
        // Reason: Use cURL for better control over timeout and error handling
        $ch = curl_init($url);
        if ($ch === false) {
            return null;
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'HomeDash/1.0');

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Reason: Only return response if HTTP request was successful
        if ($response === false || $httpCode < 200 || $httpCode >= 300) {
            return null;
        }

        return $response;
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getPageId(): int { return $this->pageId; }
    public function getWidgetType(): string { return $this->widgetType; }
    public function getTitle(): string { return $this->title; }
    public function getIcon(): string { return $this->icon; }
    public function getIconType(): string { return $this->iconType; }
    public function getDisplayOrder(): int { return $this->displayOrder; }
    public function getSettings(): array { return $this->settings; }
    public function getRefreshInterval(): int { return $this->refreshInterval; }
    public function isEnabled(): bool { return $this->isEnabled; }

    /**
     * Get a specific setting value
     *
     * @param string $key Setting key
     * @param mixed $default Default value if not found
     * @return mixed Setting value
     */
    protected function getSetting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }
}
