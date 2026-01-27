<?php

declare(strict_types=1);

/**
 * Widget Model - Database CRUD operations for widgets
 */
class WidgetModel
{
    /**
     * Get all widgets for a specific page
     *
     * @param int $pageId The page ID
     * @return array Array of widget records
     */
    public static function getByPage(int $pageId): array
    {
        return Database::fetchAll(
            'SELECT * FROM widgets WHERE page_id = ? AND is_enabled = 1 ORDER BY display_order ASC, id ASC',
            [$pageId]
        );
    }

    /**
     * Get all widgets for a page (including disabled)
     *
     * @param int $pageId The page ID
     * @return array Array of widget records
     */
    public static function getAllByPage(int $pageId): array
    {
        return Database::fetchAll(
            'SELECT * FROM widgets WHERE page_id = ? ORDER BY display_order ASC, id ASC',
            [$pageId]
        );
    }

    /**
     * Find a widget by ID
     *
     * @param int $id The widget ID
     * @return array|null The widget record or null if not found
     */
    public static function find(int $id): ?array
    {
        return Database::fetchOne(
            'SELECT * FROM widgets WHERE id = ?',
            [$id]
        );
    }

    /**
     * Create a new widget
     *
     * @param array $data The widget data
     * @return int The ID of the created widget
     */
    public static function create(array $data): int
    {
        // Get next display order for this page
        $maxOrder = Database::fetchOne(
            'SELECT MAX(display_order) as max_order FROM widgets WHERE page_id = ?',
            [$data['page_id']]
        );
        $displayOrder = ((int) ($maxOrder['max_order'] ?? 0)) + 1;

        // Reason: Encode settings as JSON for storage
        $settingsJson = !empty($data['settings'])
            ? json_encode($data['settings'])
            : null;

        return Database::insert(
            'INSERT INTO widgets (page_id, widget_type, title, icon, icon_type, display_order, settings, refresh_interval, is_enabled, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, datetime("now"), datetime("now"))',
            [
                $data['page_id'],
                $data['widget_type'],
                $data['title'],
                $data['icon'] ?? 'box',
                $data['icon_type'] ?? 'lucide',
                $displayOrder,
                $settingsJson,
                $data['refresh_interval'] ?? 300,
                $data['is_enabled'] ?? 1
            ]
        );
    }

    /**
     * Update an existing widget
     *
     * @param int $id The widget ID
     * @param array $data The updated widget data
     * @return int The number of affected rows
     */
    public static function update(int $id, array $data): int
    {
        $widget = self::find($id);
        if ($widget === null) {
            return 0;
        }

        // Reason: Encode settings as JSON for storage
        $settingsJson = isset($data['settings'])
            ? json_encode($data['settings'])
            : $widget['settings'];

        return Database::update(
            'UPDATE widgets
             SET title = ?, icon = ?, icon_type = ?, display_order = ?, settings = ?, refresh_interval = ?, is_enabled = ?, updated_at = datetime("now")
             WHERE id = ?',
            [
                $data['title'] ?? $widget['title'],
                $data['icon'] ?? $widget['icon'],
                $data['icon_type'] ?? $widget['icon_type'],
                $data['display_order'] ?? $widget['display_order'],
                $settingsJson,
                $data['refresh_interval'] ?? $widget['refresh_interval'],
                isset($data['is_enabled']) ? (int) $data['is_enabled'] : $widget['is_enabled'],
                $id
            ]
        );
    }

    /**
     * Delete a widget
     *
     * @param int $id The widget ID
     * @return int The number of affected rows
     */
    public static function delete(int $id): int
    {
        // Reason: Delete widget cache entries first (CASCADE will handle this, but explicit is safer)
        Database::delete('DELETE FROM widget_cache WHERE widget_id = ?', [$id]);

        return Database::delete('DELETE FROM widgets WHERE id = ?', [$id]);
    }

    /**
     * Clean expired cache entries
     *
     * @return int The number of deleted entries
     */
    public static function cleanExpiredCache(): int
    {
        return Database::delete(
            'DELETE FROM widget_cache WHERE expires_at < ?',
            [time()]
        );
    }

    /**
     * Get all widget types that are currently in use
     *
     * @return array Array of widget type strings
     */
    public static function getUsedTypes(): array
    {
        $results = Database::fetchAll(
            'SELECT DISTINCT widget_type FROM widgets ORDER BY widget_type ASC'
        );

        return array_column($results, 'widget_type');
    }

    /**
     * Count widgets by page
     *
     * @param int $pageId The page ID
     * @return int Number of widgets
     */
    public static function countByPage(int $pageId): int
    {
        $result = Database::fetchOne(
            'SELECT COUNT(*) as count FROM widgets WHERE page_id = ?',
            [$pageId]
        );

        return (int) ($result['count'] ?? 0);
    }

    /**
     * Reorder widgets within a page
     *
     * @param array $order Array of widget IDs in desired order
     */
    public static function reorder(array $order): void
    {
        Database::beginTransaction();

        try {
            foreach ($order as $position => $widgetId) {
                Database::update(
                    'UPDATE widgets SET display_order = ?, updated_at = datetime("now") WHERE id = ?',
                    [$position, $widgetId]
                );
            }

            Database::commit();
        } catch (Exception $e) {
            Database::rollback();
            throw $e;
        }
    }
}
