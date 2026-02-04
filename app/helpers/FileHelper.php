<?php

declare(strict_types=1);

/**
 * FileHelper - Utility functions for file sharing system
 */
class FileHelper
{
    private const MAX_FILE_SIZE = 5033164800; // 4.8GB default
    private const STORAGE_PATH = BASE_PATH . '/data/files/';

    // Disallowed MIME types (executables, scripts)
    private const BLOCKED_MIME_TYPES = [
        'application/x-executable',
        'application/x-dosexec',
        'application/x-msdos-program',
        'application/x-msdownload',
        'application/x-sh',
        'application/x-shellscript',
        'text/x-php',
        'text/x-python',
        'text/x-perl',
        'text/x-ruby',
        'application/x-httpd-php',
    ];

    // Disallowed file extensions
    private const BLOCKED_EXTENSIONS = [
        'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js',
        'jar', 'app', 'deb', 'rpm', 'sh', 'bash', 'csh', 'ksh',
        'php', 'phtml', 'php3', 'php4', 'php5', 'phps', 'phar',
        'py', 'pyc', 'pyo', 'rb', 'pl', 'cgi', 'asp', 'aspx',
    ];

    /**
     * Validate an uploaded file
     *
     * @param array{name: string, type: string, tmp_name: string, error: int, size: int} $file The uploaded file array from $_FILES
     * @return array{success: bool, error?: string}
     */
    public static function validateFile(array $file): array
    {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'error' => self::getUploadErrorMessage($file['error'])
            ];
        }

        // Check if file exists
        if (!file_exists($file['tmp_name'])) {
            return [
                'success' => false,
                'error' => 'Uploaded file not found'
            ];
        }

        // Check file size
        $maxSize = (int) Database::fetchOne('SELECT value FROM settings WHERE key = ?', ['fileshare_max_file_size'])['value'] ?? self::MAX_FILE_SIZE;

        if ($file['size'] > $maxSize) {
            return [
                'success' => false,
                'error' => 'File size exceeds maximum allowed size of ' . self::formatFileSize($maxSize)
            ];
        }

        // Validate extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($extension, self::BLOCKED_EXTENSIONS, true)) {
            return [
                'success' => false,
                'error' => 'File type not allowed for security reasons'
            ];
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return [
                'success' => false,
                'error' => 'Could not determine file type'
            ];
        }

        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (in_array($mimeType, self::BLOCKED_MIME_TYPES, true)) {
            return [
                'success' => false,
                'error' => 'File type not allowed for security reasons'
            ];
        }

        // Additional check: detect executable signatures
        $handle = fopen($file['tmp_name'], 'rb');
        if ($handle !== false) {
            $header = fread($handle, 4);
            fclose($handle);

            // Reason: Check for executable file signatures (MZ for Windows, ELF for Linux)
            if ($header !== false && (substr($header, 0, 2) === 'MZ' || substr($header, 0, 4) === "\x7fELF")) {
                return [
                    'success' => false,
                    'error' => 'Executable files are not allowed'
                ];
            }
        }

        return ['success' => true];
    }

    /**
     * Generate a secure random token for share URL
     *
     * @return string 64-character hexadecimal token
     */
    public static function generateToken(): string
    {
        // Reason: bin2hex() of 32 random bytes = 64 hex characters
        return bin2hex(random_bytes(32));
    }

    /**
     * Generate a UUID-based stored filename
     *
     * @param string $originalFilename Original filename to extract extension
     * @return string UUID-based filename with original extension
     */
    public static function generateStoredFilename(string $originalFilename): string
    {
        $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));

        // Generate UUID v4
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Set version to 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Set variant to RFC 4122

        $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));

        return $extension ? $uuid . '.' . $extension : $uuid;
    }

    /**
     * Save an uploaded file to storage directory
     *
     * @param array{name: string, type: string, tmp_name: string, error: int, size: int} $file The uploaded file array
     * @param string $storedFilename The UUID-based filename to save as
     * @return array{success: bool, path?: string, error?: string}
     */
    public static function saveFile(array $file, string $storedFilename): array
    {
        // Ensure storage directory exists
        if (!is_dir(self::STORAGE_PATH)) {
            mkdir(self::STORAGE_PATH, 0775, true);
        }

        $targetPath = self::STORAGE_PATH . $storedFilename;

        // Check if file already exists (shouldn't happen with UUID, but check anyway)
        if (file_exists($targetPath)) {
            return [
                'success' => false,
                'error' => 'Storage filename collision (please try again)'
            ];
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return [
                'success' => false,
                'error' => 'Failed to save file to storage'
            ];
        }

        // Set proper permissions
        chmod($targetPath, 0644);

        return [
            'success' => true,
            'path' => $targetPath
        ];
    }

    /**
     * Delete a file from storage
     *
     * @param string $storedFilename The UUID-based filename to delete
     * @return bool True if deleted successfully
     */
    public static function deleteFile(string $storedFilename): bool
    {
        $filepath = self::STORAGE_PATH . $storedFilename;

        if (!file_exists($filepath)) {
            return false;
        }

        return unlink($filepath);
    }

    /**
     * Format file size in human-readable format
     *
     * @param int $bytes File size in bytes
     * @param int $decimals Number of decimal places
     * @return string Formatted file size (e.g., "1.5 MB")
     */
    public static function formatFileSize(int $bytes, int $decimals = 2): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen((string) $bytes) - 1) / 3);

        return sprintf("%.{$decimals}f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }

    /**
     * Get total storage used by all files
     *
     * @return int Total bytes used
     */
    public static function getTotalStorageUsed(): int
    {
        $result = Database::fetchOne('SELECT SUM(file_size) as total FROM shared_files');
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Get storage path for a file
     *
     * @param string $storedFilename The UUID-based filename
     * @return string Full filesystem path
     */
    public static function getStoragePath(string $storedFilename): string
    {
        return self::STORAGE_PATH . $storedFilename;
    }

    /**
     * Check if storage limit would be exceeded
     *
     * @param int $newFileSize Size of file to be added
     * @return array{within_limit: bool, current_usage: int, max_storage: int, available: int}
     */
    public static function checkStorageLimit(int $newFileSize): array
    {
        $currentUsage = self::getTotalStorageUsed();
        $maxStorage = (int) Database::fetchOne('SELECT value FROM settings WHERE key = ?', ['fileshare_max_total_size'])['value'] ?? 10737418240; // 10GB default

        $available = $maxStorage - $currentUsage;

        return [
            'within_limit' => ($currentUsage + $newFileSize) <= $maxStorage,
            'current_usage' => $currentUsage,
            'max_storage' => $maxStorage,
            'available' => $available
        ];
    }

    /**
     * Get human-readable error message for upload error code
     *
     * @param int $errorCode PHP upload error code
     * @return string Error message
     */
    private static function getUploadErrorMessage(int $errorCode): string
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive in HTML form',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary upload folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by PHP extension',
        ];

        return $errors[$errorCode] ?? 'Unknown upload error (code: ' . $errorCode . ')';
    }

    /**
     * Calculate expiration timestamp from preset
     *
     * @param string $preset Expiration preset ('30d', '2m', '4m', '6m', 'unlimited')
     * @return string|null ISO 8601 datetime string or null for unlimited
     */
    public static function calculateExpiration(string $preset): ?string
    {
        $expirations = [
            '30d' => 2592000,       // 30 days
            '2m' => 5184000,        // 2 months (60 days)
            '4m' => 10368000,       // 4 months (120 days)
            '6m' => 15552000,       // 6 months (180 days)
            'unlimited' => null,
        ];

        if (!isset($expirations[$preset])) {
            $preset = '30d'; // Default to 30 days
        }

        if ($expirations[$preset] === null) {
            return null; // Unlimited
        }

        // Reason: SQLite datetime format for expiration
        return date('Y-m-d H:i:s', time() + $expirations[$preset]);
    }

    /**
     * Get MIME type icon for display
     *
     * @param string $mimeType MIME type string
     * @return string Lucide icon name
     */
    public static function getMimeTypeIcon(string $mimeType): string
    {
        // Reason: Map common MIME types to appropriate Lucide icons
        $iconMap = [
            'application/pdf' => 'file-text',
            'application/zip' => 'archive',
            'application/x-rar-compressed' => 'archive',
            'application/x-7z-compressed' => 'archive',
            'image/jpeg' => 'image',
            'image/png' => 'image',
            'image/gif' => 'image',
            'image/svg+xml' => 'image',
            'video/mp4' => 'video',
            'video/x-matroska' => 'video',
            'audio/mpeg' => 'music',
            'audio/wav' => 'music',
            'text/plain' => 'file-text',
            'text/csv' => 'file-text',
            'application/msword' => 'file-text',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'file-text',
            'application/vnd.ms-excel' => 'file-text',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'file-text',
        ];

        // Check for exact match
        if (isset($iconMap[$mimeType])) {
            return $iconMap[$mimeType];
        }

        // Check for category match (e.g., "image/*")
        $category = explode('/', $mimeType)[0];
        $categoryMap = [
            'image' => 'image',
            'video' => 'video',
            'audio' => 'music',
            'text' => 'file-text',
        ];

        return $categoryMap[$category] ?? 'file';
    }
}
