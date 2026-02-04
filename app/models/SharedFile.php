<?php

declare(strict_types=1);

/**
 * SharedFile Model
 *
 * Represents a shared file with expiration and password protection
 */
class SharedFile
{
    /**
     * Create a new shared file
     *
     * @param array $data File data
     * @return int The new file ID
     * @throws InvalidArgumentException If required fields are missing
     */
    public static function create(array $data): int
    {
        // Validate required fields
        $required = ['token', 'stored_filename', 'original_filename', 'file_size', 'mime_type'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new InvalidArgumentException("Field '{$field}' is required");
            }
        }

        return Database::insert('
            INSERT INTO shared_files (
                token, stored_filename, original_filename, file_size,
                mime_type, description, expires_at, password_hash, uploaded_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ', [
            $data['token'],
            $data['stored_filename'],
            $data['original_filename'],
            $data['file_size'],
            $data['mime_type'],
            $data['description'] ?? null,
            $data['expires_at'] ?? null,
            $data['password_hash'] ?? null,
            $data['uploaded_by'] ?? 'admin'
        ]);
    }

    /**
     * Find a file by ID
     *
     * @param int $id File ID
     * @return array|null File record or null if not found
     */
    public static function find(int $id): ?array
    {
        return Database::fetchOne('SELECT * FROM shared_files WHERE id = ?', [$id]);
    }

    /**
     * Find a file by share token
     *
     * @param string $token Share token
     * @return array|null File record or null if not found
     */
    public static function findByToken(string $token): ?array
    {
        return Database::fetchOne('SELECT * FROM shared_files WHERE token = ?', [$token]);
    }

    /**
     * Get all shared files
     *
     * @return array Array of file records
     */
    public static function getAll(): array
    {
        return Database::fetchAll('
            SELECT * FROM shared_files
            ORDER BY created_at DESC
        ');
    }

    /**
     * Update a shared file
     *
     * @param int $id File ID
     * @param array $data Fields to update
     * @return bool True if updated successfully
     */
    public static function update(int $id, array $data): bool
    {
        $allowedFields = ['description', 'expires_at', 'password_hash'];
        $updates = [];
        $params = [];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updates[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($updates)) {
            return false;
        }

        $params[] = $id;
        $sql = 'UPDATE shared_files SET ' . implode(', ', $updates) . ' WHERE id = ?';

        return Database::update($sql, $params) > 0;
    }

    /**
     * Delete a shared file
     *
     * @param int $id File ID
     * @return bool True if deleted successfully
     */
    public static function delete(int $id): bool
    {
        return Database::delete('DELETE FROM shared_files WHERE id = ?', [$id]) > 0;
    }

    /**
     * Check if a file is expired
     *
     * @param array $file File record
     * @return bool True if expired
     */
    public static function isExpired(array $file): bool
    {
        // Null expires_at means unlimited
        if ($file['expires_at'] === null) {
            return false;
        }

        return strtotime($file['expires_at']) < time();
    }

    /**
     * Verify password for a file
     *
     * @param array $file File record
     * @param string $password Password to verify
     * @return bool True if password matches
     */
    public static function verifyPassword(array $file, string $password): bool
    {
        // No password set
        if ($file['password_hash'] === null) {
            return true;
        }

        return password_verify($password, $file['password_hash']);
    }

    /**
     * Increment download count
     *
     * @param int $id File ID
     * @return bool True if updated successfully
     */
    public static function incrementDownloadCount(int $id): bool
    {
        return Database::update('
            UPDATE shared_files
            SET download_count = download_count + 1,
                accessed_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ', [$id]) > 0;
    }

    /**
     * Update last accessed timestamp
     *
     * @param int $id File ID
     * @return bool True if updated successfully
     */
    public static function updateAccessTime(int $id): bool
    {
        return Database::update('
            UPDATE shared_files
            SET accessed_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ', [$id]) > 0;
    }

    /**
     * Clean up expired files
     *
     * @return array{deleted_files: int, freed_space: int}
     */
    public static function cleanupExpired(): array
    {
        // Get expired files
        $expiredFiles = Database::fetchAll('
            SELECT id, stored_filename, file_size
            FROM shared_files
            WHERE expires_at IS NOT NULL
            AND expires_at < datetime("now")
        ');

        $deletedCount = 0;
        $freedSpace = 0;

        foreach ($expiredFiles as $file) {
            // Delete from filesystem
            if (FileHelper::deleteFile($file['stored_filename'])) {
                $freedSpace += $file['file_size'];
            }

            // Delete from database (CASCADE will remove access logs)
            if (self::delete($file['id'])) {
                $deletedCount++;
            }
        }

        return [
            'deleted_files' => $deletedCount,
            'freed_space' => $freedSpace
        ];
    }

    /**
     * Get total storage used
     *
     * @return int Total bytes used
     */
    public static function getTotalStorageUsed(): int
    {
        $result = Database::fetchOne('SELECT SUM(file_size) as total FROM shared_files');
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Get file count
     *
     * @return int Total number of files
     */
    public static function getFileCount(): int
    {
        $result = Database::fetchOne('SELECT COUNT(*) as count FROM shared_files');
        return (int) ($result['count'] ?? 0);
    }

    /**
     * Get total download count
     *
     * @return int Total downloads across all files
     */
    public static function getTotalDownloads(): int
    {
        $result = Database::fetchOne('SELECT SUM(download_count) as total FROM shared_files');
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Check if file is password protected
     *
     * @param array $file File record
     * @return bool True if password protected
     */
    public static function isPasswordProtected(array $file): bool
    {
        return $file['password_hash'] !== null;
    }

    /**
     * Check if file is unlocked in current session
     *
     * @param int $fileId File ID
     * @return bool True if unlocked
     */
    public static function isUnlocked(int $fileId): bool
    {
        return isset($_SESSION['unlocked_files']) && in_array($fileId, $_SESSION['unlocked_files'], true);
    }

    /**
     * Unlock a file in current session
     *
     * @param int $fileId File ID
     * @return void
     */
    public static function unlock(int $fileId): void
    {
        if (!isset($_SESSION['unlocked_files'])) {
            $_SESSION['unlocked_files'] = [];
        }

        if (!in_array($fileId, $_SESSION['unlocked_files'], true)) {
            $_SESSION['unlocked_files'][] = $fileId;
        }
    }

    /**
     * Generate share URL for a file
     *
     * @param string $token Share token
     * @return string Full share URL
     */
    public static function getShareUrl(string $token): string
    {
        return View::url('/s/' . $token);
    }

    /**
     * Get statistics for dashboard
     *
     * @return array Statistics
     */
    public static function getStatistics(): array
    {
        return [
            'total_files' => self::getFileCount(),
            'total_storage' => self::getTotalStorageUsed(),
            'total_downloads' => self::getTotalDownloads(),
            'files_with_password' => Database::fetchOne('SELECT COUNT(*) as count FROM shared_files WHERE password_hash IS NOT NULL')['count'] ?? 0,
            'expired_files' => Database::fetchOne('SELECT COUNT(*) as count FROM shared_files WHERE expires_at IS NOT NULL AND expires_at < datetime("now")')['count'] ?? 0,
        ];
    }
}
