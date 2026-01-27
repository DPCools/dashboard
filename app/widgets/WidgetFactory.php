<?php

declare(strict_types=1);

/**
 * Widget Factory - Creates widget instances by type
 *
 * Uses registry pattern to map widget types to their class implementations
 */
class WidgetFactory
{
    /**
     * Registry of widget types to class names
     *
     * @var array<string, string>
     */
    private static array $registry = [];

    /**
     * Register a widget type
     *
     * @param string $type Widget type identifier (e.g., 'external_ip')
     * @param string $className Fully qualified class name
     */
    public static function register(string $type, string $className): void
    {
        self::$registry[$type] = $className;
    }

    /**
     * Create a widget instance from database record
     *
     * @param array $widgetData Widget data from database
     * @return Widget|null Widget instance or null if type not registered
     */
    public static function create(array $widgetData): ?Widget
    {
        $type = $widgetData['widget_type'] ?? null;

        if ($type === null || !isset(self::$registry[$type])) {
            return null;
        }

        $className = self::$registry[$type];

        // Reason: Verify class exists and extends Widget
        if (!class_exists($className) || !is_subclass_of($className, Widget::class)) {
            return null;
        }

        try {
            return new $className($widgetData);
        } catch (Exception $e) {
            error_log('Widget creation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all available widget types
     *
     * @return array Array of widget type identifiers
     */
    public static function getAvailableTypes(): array
    {
        return array_keys(self::$registry);
    }

    /**
     * Get metadata for a widget type
     *
     * @param string $type Widget type identifier
     * @return array|null Metadata array or null if type not registered
     */
    public static function getTypeMetadata(string $type): ?array
    {
        if (!isset(self::$registry[$type])) {
            return null;
        }

        $className = self::$registry[$type];

        if (!class_exists($className)) {
            return null;
        }

        // Reason: Create temporary instance to get metadata
        try {
            $tempData = [
                'id' => 0,
                'page_id' => 0,
                'widget_type' => $type,
                'title' => 'Temp',
                'settings' => '{}',
                'refresh_interval' => 300,
                'is_enabled' => 1
            ];

            $instance = new $className($tempData);

            return [
                'type' => $type,
                'class' => $className,
                'default_settings' => $instance->getDefaultSettings(),
                'display_name' => self::getDisplayName($type)
            ];
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get human-readable display name for a widget type
     *
     * @param string $type Widget type identifier
     * @return string Display name
     */
    private static function getDisplayName(string $type): string
    {
        $displayNames = [
            'external_ip' => 'External IP Address',
            'weather' => 'Weather',
            'system_stats' => 'System Statistics',
            'clock' => 'Clock',
            'notes' => 'Notes',
            'rss' => 'RSS Feed',
            'proxmox' => 'Proxmox Cluster'
        ];

        return $displayNames[$type] ?? ucwords(str_replace('_', ' ', $type));
    }

    /**
     * Check if a widget type is registered
     *
     * @param string $type Widget type identifier
     * @return bool True if registered, false otherwise
     */
    public static function isRegistered(string $type): bool
    {
        return isset(self::$registry[$type]);
    }
}

// Reason: Auto-register all widget types when factory is loaded
WidgetFactory::register('external_ip', 'ExternalIpWidget');
WidgetFactory::register('weather', 'WeatherWidget');
WidgetFactory::register('system_stats', 'SystemStatsWidget');
WidgetFactory::register('clock', 'ClockWidget');
WidgetFactory::register('notes', 'NotesWidget');
WidgetFactory::register('rss', 'RssWidget');
WidgetFactory::register('proxmox', 'ProxmoxWidget');
