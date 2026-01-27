<?php

declare(strict_types=1);

class Item
{
    /**
     * Get all items for a specific page, grouped by category
     *
     * @param int $pageId The page ID
     * @return array Array of items grouped by category
     */
    public static function getByPage(int $pageId): array
    {
        $items = Database::fetchAll(
            'SELECT * FROM items WHERE page_id = ? ORDER BY category ASC, display_order ASC, title ASC',
            [$pageId]
        );

        // Reason: Group items by category for organized display
        $grouped = [];
        foreach ($items as $item) {
            $category = $item['category'] ?? 'Uncategorized';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $item;
        }

        return $grouped;
    }

    /**
     * Get all items for a specific page (ungrouped)
     *
     * @param int $pageId The page ID
     * @return array Array of items
     */
    public static function getAllByPage(int $pageId): array
    {
        return Database::fetchAll(
            'SELECT * FROM items WHERE page_id = ? ORDER BY display_order ASC, title ASC',
            [$pageId]
        );
    }

    /**
     * Find an item by ID
     *
     * @param int $id The item ID
     * @return array|null The item record or null if not found
     */
    public static function find(int $id): ?array
    {
        return Database::fetchOne(
            'SELECT * FROM items WHERE id = ?',
            [$id]
        );
    }

    /**
     * Create a new item
     *
     * @param array $data The item data
     * @return int The ID of the created item
     */
    public static function create(array $data): int
    {
        // Get next display order for this page
        $maxOrder = Database::fetchOne(
            'SELECT MAX(display_order) as max_order FROM items WHERE page_id = ?',
            [$data['page_id']]
        );
        $displayOrder = ((int) ($maxOrder['max_order'] ?? 0)) + 1;

        // Reason: Hash password if item is private
        $passwordHash = null;
        if (!empty($data['is_private']) && !empty($data['password'])) {
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        return Database::insert(
            'INSERT INTO items (page_id, title, url, icon, icon_type, description, category, display_order, status_check, ssl_verify, is_private, password_hash, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime("now"), datetime("now"))',
            [
                $data['page_id'],
                $data['title'],
                $data['url'],
                $data['icon'] ?? 'link',
                $data['icon_type'] ?? 'lucide',
                $data['description'] ?? null,
                $data['category'] ?? null,
                $displayOrder,
                $data['status_check'] ?? 0,
                $data['ssl_verify'] ?? 1, // Reason: Default to verify SSL for NEW items (secure by default)
                $data['is_private'] ?? 0,
                $passwordHash
            ]
        );
    }

    /**
     * Update an existing item
     *
     * @param int $id The item ID
     * @param array $data The updated item data
     * @return int The number of affected rows
     */
    public static function update(int $id, array $data): int
    {
        $item = self::find($id);
        if ($item === null) {
            return 0;
        }

        // Reason: Handle password updates - only update if new password provided, or clear if becoming public
        $passwordHash = $item['password_hash'];
        $isPrivate = $data['is_private'] ?? $item['is_private'];

        if (!empty($data['password'])) {
            // New password provided
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        } elseif (empty($isPrivate)) {
            // Item becoming public, clear password
            $passwordHash = null;
        }

        return Database::update(
            'UPDATE items
             SET title = ?, url = ?, icon = ?, icon_type = ?, description = ?, category = ?, display_order = ?, status_check = ?, ssl_verify = ?, is_private = ?, password_hash = ?, updated_at = datetime("now")
             WHERE id = ?',
            [
                $data['title'] ?? $item['title'],
                $data['url'] ?? $item['url'],
                $data['icon'] ?? $item['icon'],
                $data['icon_type'] ?? $item['icon_type'],
                $data['description'] ?? $item['description'],
                $data['category'] ?? $item['category'],
                $data['display_order'] ?? $item['display_order'],
                $data['status_check'] ?? $item['status_check'],
                $data['ssl_verify'] ?? $item['ssl_verify'],
                $isPrivate,
                $passwordHash,
                $id
            ]
        );
    }

    /**
     * Delete an item
     *
     * @param int $id The item ID
     * @return int The number of affected rows
     */
    public static function delete(int $id): int
    {
        return Database::delete('DELETE FROM items WHERE id = ?', [$id]);
    }

    /**
     * Get all unique categories
     *
     * @return array Array of category names
     */
    public static function getCategories(): array
    {
        $results = Database::fetchAll(
            'SELECT DISTINCT category FROM items WHERE category IS NOT NULL ORDER BY category ASC'
        );

        return array_column($results, 'category');
    }

    /**
     * Reorder items within a page
     *
     * @param array $order Array of item IDs in desired order
     */
    public static function reorder(array $order): void
    {
        Database::beginTransaction();

        try {
            foreach ($order as $position => $itemId) {
                Database::update(
                    'UPDATE items SET display_order = ? WHERE id = ?',
                    [$position, $itemId]
                );
            }

            Database::commit();
        } catch (Exception $e) {
            Database::rollback();
            throw $e;
        }
    }

    /**
     * Move an item to a different page
     *
     * @param int $id The item ID
     * @param int $newPageId The new page ID
     * @return int The number of affected rows
     */
    public static function moveTo(int $id, int $newPageId): int
    {
        // Get next display order for the new page
        $maxOrder = Database::fetchOne(
            'SELECT MAX(display_order) as max_order FROM items WHERE page_id = ?',
            [$newPageId]
        );
        $displayOrder = ((int) ($maxOrder['max_order'] ?? 0)) + 1;

        return Database::update(
            'UPDATE items SET page_id = ?, display_order = ?, updated_at = datetime("now") WHERE id = ?',
            [$newPageId, $displayOrder, $id]
        );
    }

    /**
     * Search items by title or description
     *
     * @param string $query The search query
     * @return array Array of matching items
     */
    public static function search(string $query): array
    {
        return Database::fetchAll(
            'SELECT * FROM items
             WHERE title LIKE ? OR description LIKE ?
             ORDER BY title ASC',
            ['%' . $query . '%', '%' . $query . '%']
        );
    }

    /**
     * Verify password for a password-protected item
     *
     * @param int $id The item ID
     * @param string $password The password to verify
     * @return bool True if password is correct, false otherwise
     */
    public static function verifyPassword(int $id, string $password): bool
    {
        $item = self::find($id);

        if ($item === null || empty($item['is_private']) || empty($item['password_hash'])) {
            return false;
        }

        return password_verify($password, $item['password_hash']);
    }

    /**
     * Check if an item is unlocked in the current session
     *
     * @param int $id The item ID
     * @return bool True if item is unlocked or public, false if locked
     */
    public static function isUnlocked(int $id): bool
    {
        $item = self::find($id);

        // Reason: Public items are always unlocked
        if ($item === null || empty($item['is_private'])) {
            return true;
        }

        // Check session for unlocked items
        if (!isset($_SESSION['unlocked_items'])) {
            $_SESSION['unlocked_items'] = [];
        }

        return in_array($id, $_SESSION['unlocked_items'], true);
    }

    /**
     * Mark an item as unlocked in the session
     *
     * @param int $id The item ID
     */
    public static function unlock(int $id): void
    {
        if (!isset($_SESSION['unlocked_items'])) {
            $_SESSION['unlocked_items'] = [];
        }

        if (!in_array($id, $_SESSION['unlocked_items'], true)) {
            $_SESSION['unlocked_items'][] = $id;
        }
    }

    /**
     * Lock an item (remove from session)
     *
     * @param int $id The item ID
     */
    public static function lock(int $id): void
    {
        if (!isset($_SESSION['unlocked_items'])) {
            return;
        }

        $_SESSION['unlocked_items'] = array_filter(
            $_SESSION['unlocked_items'],
            fn($itemId) => $itemId !== $id
        );
    }
}
