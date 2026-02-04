<?php

declare(strict_types=1);

/**
 * SharedFileAccessLog Model
 *
 * Audit trail for file access attempts
 */
class SharedFileAccessLog
{
    /**
     * Log a file access event
     *
     * @param int $fileId File ID
     * @param string $accessType Type of access ('view', 'download', 'failed_password')
     * @param string|null $accessorIp IP address of accessor
     * @param string|null $userAgent Browser user agent
     * @return int The new log entry ID
     */
    public static function create(int $fileId, string $accessType, ?string $accessorIp = null, ?string $userAgent = null): int
    {
        // Get IP and user agent from current request if not provided
        if ($accessorIp === null) {
            $accessorIp = $_SERVER['REMOTE_ADDR'] ?? null;
        }

        if ($userAgent === null) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        }

        return Database::insert('
            INSERT INTO shared_file_access_log (
                file_id, access_type, accessor_ip, user_agent
            ) VALUES (?, ?, ?, ?)
        ', [
            $fileId,
            $accessType,
            $accessorIp,
            $userAgent
        ]);
    }

    /**
     * Get access log for a specific file
     *
     * @param int $fileId File ID
     * @param int|null $limit Maximum number of entries to return
     * @return array Array of log entries
     */
    public static function getByFileId(int $fileId, ?int $limit = null): array
    {
        $sql = '
            SELECT * FROM shared_file_access_log
            WHERE file_id = ?
            ORDER BY accessed_at DESC
        ';

        if ($limit !== null) {
            $sql .= ' LIMIT ' . $limit;
        }

        return Database::fetchAll($sql, [$fileId]);
    }

    /**
     * Get all access logs
     *
     * @param int|null $limit Maximum number of entries to return
     * @return array Array of log entries
     */
    public static function getAll(?int $limit = null): array
    {
        $sql = '
            SELECT sal.*, sf.original_filename, sf.token
            FROM shared_file_access_log sal
            INNER JOIN shared_files sf ON sal.file_id = sf.id
            ORDER BY sal.accessed_at DESC
        ';

        if ($limit !== null) {
            $sql .= ' LIMIT ' . $limit;
        }

        return Database::fetchAll($sql);
    }

    /**
     * Get access count by type for a file
     *
     * @param int $fileId File ID
     * @return array{view: int, download: int, failed_password: int}
     */
    public static function getAccessCountsByType(int $fileId): array
    {
        $result = Database::fetchAll('
            SELECT access_type, COUNT(*) as count
            FROM shared_file_access_log
            WHERE file_id = ?
            GROUP BY access_type
        ', [$fileId]);

        $counts = [
            'view' => 0,
            'download' => 0,
            'failed_password' => 0
        ];

        foreach ($result as $row) {
            $counts[$row['access_type']] = (int) $row['count'];
        }

        return $counts;
    }

    /**
     * Get recent access activity
     *
     * @param int $hours Number of hours to look back
     * @return array Array of log entries
     */
    public static function getRecentActivity(int $hours = 24): array
    {
        return Database::fetchAll('
            SELECT sal.*, sf.original_filename, sf.token
            FROM shared_file_access_log sal
            INNER JOIN shared_files sf ON sal.file_id = sf.id
            WHERE sal.accessed_at >= datetime("now", "-" || ? || " hours")
            ORDER BY sal.accessed_at DESC
        ', [$hours]);
    }

    /**
     * Delete old access logs
     *
     * @param int $days Delete logs older than this many days
     * @return int Number of deleted entries
     */
    public static function deleteOldLogs(int $days = 90): int
    {
        return Database::delete('
            DELETE FROM shared_file_access_log
            WHERE accessed_at < datetime("now", "-" || ? || " days")
        ', [$days]);
    }

    /**
     * Get total access count
     *
     * @return int Total number of access events
     */
    public static function getTotalAccessCount(): int
    {
        $result = Database::fetchOne('SELECT COUNT(*) as count FROM shared_file_access_log');
        return (int) ($result['count'] ?? 0);
    }

    /**
     * Get access statistics
     *
     * @return array Statistics
     */
    public static function getStatistics(): array
    {
        $totalAccess = self::getTotalAccessCount();

        $byType = Database::fetchAll('
            SELECT access_type, COUNT(*) as count
            FROM shared_file_access_log
            GROUP BY access_type
        ');

        $stats = [
            'total_access' => $totalAccess,
            'by_type' => []
        ];

        foreach ($byType as $row) {
            $stats['by_type'][$row['access_type']] = (int) $row['count'];
        }

        return $stats;
    }
}
