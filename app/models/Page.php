<?php

declare(strict_types=1);

class Page
{
    /**
     * Get all pages ordered by display_order
     *
     * @return array Array of page records
     */
    public static function all(): array
    {
        return Database::fetchAll(
            'SELECT * FROM pages ORDER BY display_order ASC, name ASC'
        );
    }

    /**
     * Find a page by ID
     *
     * @param int $id The page ID
     * @return array|null The page record or null if not found
     */
    public static function find(int $id): ?array
    {
        return Database::fetchOne(
            'SELECT * FROM pages WHERE id = ?',
            [$id]
        );
    }

    /**
     * Find a page by slug
     *
     * @param string $slug The page slug
     * @return array|null The page record or null if not found
     */
    public static function findBySlug(string $slug): ?array
    {
        return Database::fetchOne(
            'SELECT * FROM pages WHERE slug = ?',
            [$slug]
        );
    }

    /**
     * Create a new page
     *
     * @param array $data The page data
     * @return int The ID of the created page
     */
    public static function create(array $data): int
    {
        // Reason: Auto-generate slug from name if not provided
        if (!isset($data['slug']) || $data['slug'] === '') {
            $data['slug'] = Security::slugify($data['name']);
        }

        // Reason: Ensure slug is unique by appending a number if needed
        $originalSlug = $data['slug'];
        $counter = 1;
        while (self::findBySlug($data['slug']) !== null) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Reason: Hash password if provided and page is private
        $passwordHash = null;
        if (isset($data['password']) && $data['password'] !== '' && ($data['is_private'] ?? 0) === 1) {
            $passwordHash = Security::hashPassword($data['password']);
        }

        // Get next display order
        $maxOrder = Database::fetchOne('SELECT MAX(display_order) as max_order FROM pages');
        $displayOrder = ((int) ($maxOrder['max_order'] ?? 0)) + 1;

        return Database::insert(
            'INSERT INTO pages (name, slug, is_private, password_hash, icon, icon_type, display_order, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, datetime("now"), datetime("now"))',
            [
                $data['name'],
                $data['slug'],
                $data['is_private'] ?? 0,
                $passwordHash,
                $data['icon'] ?? 'layout-dashboard',
                $data['icon_type'] ?? 'lucide',
                $displayOrder
            ]
        );
    }

    /**
     * Update an existing page
     *
     * @param int $id The page ID
     * @param array $data The updated page data
     * @return int The number of affected rows
     */
    public static function update(int $id, array $data): int
    {
        $page = self::find($id);
        if ($page === null) {
            return 0;
        }

        // Reason: Update slug if name changed and slug not explicitly set
        if (isset($data['name']) && (!isset($data['slug']) || $data['slug'] === '')) {
            $data['slug'] = Security::slugify($data['name']);
        }

        // Reason: Ensure slug is unique (excluding current page)
        if (isset($data['slug'])) {
            $existingPage = self::findBySlug($data['slug']);
            if ($existingPage !== null && (int) $existingPage['id'] !== $id) {
                $originalSlug = $data['slug'];
                $counter = 1;
                while ($existingPage !== null && (int) $existingPage['id'] !== $id) {
                    $data['slug'] = $originalSlug . '-' . $counter;
                    $existingPage = self::findBySlug($data['slug']);
                    $counter++;
                }
            }
        }

        // Reason: Handle password update - only hash if a new password is provided
        $passwordUpdate = '';
        $params = [];

        if (isset($data['password']) && $data['password'] !== '') {
            $passwordUpdate = ', password_hash = ?';
            $params[] = Security::hashPassword($data['password']);
        } elseif (isset($data['is_private']) && $data['is_private'] === 0) {
            // Reason: Clear password if page is no longer private
            $passwordUpdate = ', password_hash = NULL';
        }

        $params = array_merge($params, [
            $data['name'] ?? $page['name'],
            $data['slug'] ?? $page['slug'],
            $data['is_private'] ?? $page['is_private'],
            $data['icon'] ?? $page['icon'],
            $data['icon_type'] ?? $page['icon_type'],
            $data['display_order'] ?? $page['display_order'],
            $id
        ]);

        return Database::update(
            "UPDATE pages
             SET name = ?, slug = ?, is_private = ?, icon = ?, icon_type = ?, display_order = ?{$passwordUpdate}, updated_at = datetime('now')
             WHERE id = ?",
            $params
        );
    }

    /**
     * Delete a page and all its items
     *
     * @param int $id The page ID
     * @return int The number of affected rows
     */
    public static function delete(int $id): int
    {
        // Reason: Items will be cascade deleted due to foreign key constraint
        return Database::delete('DELETE FROM pages WHERE id = ?', [$id]);
    }

    /**
     * Verify a password for a page
     *
     * @param int $id The page ID
     * @param string $password The password to verify
     * @return bool True if password matches, false otherwise
     */
    public static function verifyPassword(int $id, string $password): bool
    {
        $page = self::find($id);

        if ($page === null || $page['password_hash'] === null) {
            return false;
        }

        return Security::verifyPassword($password, $page['password_hash']);
    }

    /**
     * Get the count of items for a page
     *
     * @param int $id The page ID
     * @return int The count of items
     */
    public static function getItemCount(int $id): int
    {
        $result = Database::fetchOne(
            'SELECT COUNT(*) as count FROM items WHERE page_id = ?',
            [$id]
        );

        return (int) ($result['count'] ?? 0);
    }

    /**
     * Reorder pages
     *
     * @param array $order Array of page IDs in desired order
     */
    public static function reorder(array $order): void
    {
        Database::beginTransaction();

        try {
            foreach ($order as $position => $pageId) {
                Database::update(
                    'UPDATE pages SET display_order = ? WHERE id = ?',
                    [$position, $pageId]
                );
            }

            Database::commit();
        } catch (Exception $e) {
            Database::rollback();
            throw $e;
        }
    }
}
