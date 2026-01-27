<?php

declare(strict_types=1);

/**
 * Icon Model - Handles database operations for custom icons
 */
class Icon
{
    /**
     * Get all custom icons from database
     *
     * @return array<int, array{id: int, filename: string, display_name: string, file_size: int, uploaded_at: string, uploaded_by: string}>
     */
    public static function all(): array
    {
        return Database::fetchAll('
            SELECT id, filename, display_name, file_size, uploaded_at, uploaded_by
            FROM icons
            ORDER BY display_name ASC
        ');
    }

    /**
     * Find an icon by ID
     *
     * @param int $id The icon ID
     * @return array{id: int, filename: string, display_name: string, file_size: int, uploaded_at: string, uploaded_by: string}|null
     */
    public static function find(int $id): ?array
    {
        return Database::fetchOne('
            SELECT id, filename, display_name, file_size, uploaded_at, uploaded_by
            FROM icons
            WHERE id = ?
        ', [$id]);
    }

    /**
     * Find an icon by filename
     *
     * @param string $filename The icon filename
     * @return array{id: int, filename: string, display_name: string, file_size: int, uploaded_at: string, uploaded_by: string}|null
     */
    public static function findByFilename(string $filename): ?array
    {
        return Database::fetchOne('
            SELECT id, filename, display_name, file_size, uploaded_at, uploaded_by
            FROM icons
            WHERE filename = ?
        ', [$filename]);
    }

    /**
     * Create a new icon record
     *
     * @param array{filename: string, display_name: string, file_size: int, uploaded_by?: string} $data Icon data
     * @return int The inserted icon ID
     */
    public static function create(array $data): int
    {
        return Database::insert('
            INSERT INTO icons (filename, display_name, file_size, uploaded_by)
            VALUES (?, ?, ?, ?)
        ', [
            $data['filename'],
            $data['display_name'],
            $data['file_size'],
            $data['uploaded_by'] ?? 'admin'
        ]);
    }

    /**
     * Update an icon record
     *
     * @param int $id The icon ID
     * @param array{display_name?: string} $data Data to update
     * @return int Number of affected rows
     */
    public static function update(int $id, array $data): int
    {
        $fields = [];
        $params = [];

        if (isset($data['display_name'])) {
            $fields[] = 'display_name = ?';
            $params[] = $data['display_name'];
        }

        if (empty($fields)) {
            return 0;
        }

        $params[] = $id;

        return Database::update('
            UPDATE icons
            SET ' . implode(', ', $fields) . '
            WHERE id = ?
        ', $params);
    }

    /**
     * Delete an icon record
     *
     * @param int $id The icon ID
     * @return int Number of affected rows
     */
    public static function delete(int $id): int
    {
        return Database::delete('DELETE FROM icons WHERE id = ?', [$id]);
    }

    /**
     * Check if an icon is used by any items or pages
     *
     * @param string $filename The icon filename
     * @return bool True if in use, false otherwise
     */
    public static function isUsed(string $filename): bool
    {
        $itemCount = Database::fetchOne(
            "SELECT COUNT(*) as count FROM items WHERE icon = ? AND icon_type = 'custom'",
            [$filename]
        );

        $pageCount = Database::fetchOne(
            "SELECT COUNT(*) as count FROM pages WHERE icon = ? AND icon_type = 'custom'",
            [$filename]
        );

        return ($itemCount['count'] ?? 0) > 0 || ($pageCount['count'] ?? 0) > 0;
    }

    /**
     * Sync database with filesystem
     * Calls IconHelper to perform the actual sync
     *
     * @return array{added: int, skipped: int}
     */
    public static function syncWithFilesystem(): array
    {
        require_once BASE_PATH . '/app/helpers/IconHelper.php';
        return IconHelper::syncWithFilesystem();
    }

    /**
     * Get count of custom icons
     *
     * @return int Number of custom icons
     */
    public static function count(): int
    {
        $result = Database::fetchOne('SELECT COUNT(*) as count FROM icons');
        return (int) ($result['count'] ?? 0);
    }
}
