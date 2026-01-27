<?php

declare(strict_types=1);

/**
 * Icon Helper - Utility functions for custom icon management
 */
class IconHelper
{
    private const MAX_FILE_SIZE = 512000; // 500KB
    private const ALLOWED_MIME_TYPES = ['image/svg+xml', 'text/xml', 'application/xml'];

    /**
     * Scan the custom icons directory and return list of files
     *
     * @return array<string, array{filename: string, path: string, size: int}>
     */
    public static function scanCustomIcons(): array
    {
        $iconDir = BASE_PATH . '/public/icons/';
        $icons = [];

        if (!is_dir($iconDir)) {
            return $icons;
        }

        $files = glob($iconDir . '*.svg');
        if ($files === false) {
            return $icons;
        }

        foreach ($files as $filepath) {
            $filename = basename($filepath);

            // Skip .gitkeep and other non-icon files
            if ($filename === '.gitkeep') {
                continue;
            }

            $icons[$filename] = [
                'filename' => $filename,
                'path' => $filepath,
                'size' => filesize($filepath) ?: 0
            ];
        }

        return $icons;
    }

    /**
     * Validate an SVG file for security and format
     *
     * @param string $filepath Path to the file to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateSVG(string $filepath): bool
    {
        // Check if file exists
        if (!file_exists($filepath)) {
            return false;
        }

        // Check file size
        $filesize = filesize($filepath);
        if ($filesize === false || $filesize > self::MAX_FILE_SIZE) {
            return false;
        }

        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return false;
        }

        $mime = finfo_file($finfo, $filepath);
        finfo_close($finfo);

        if (!in_array($mime, self::ALLOWED_MIME_TYPES, true)) {
            return false;
        }

        // Read file content
        $content = file_get_contents($filepath);
        if ($content === false) {
            return false;
        }

        // Reason: Security check - reject SVGs with embedded scripts
        if (stripos($content, '<script') !== false) {
            return false;
        }
        if (stripos($content, 'javascript:') !== false) {
            return false;
        }
        if (stripos($content, 'onerror=') !== false) {
            return false;
        }
        if (stripos($content, 'onload=') !== false) {
            return false;
        }

        // Try to parse as XML to verify structure
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);
        libxml_clear_errors();

        return $xml !== false;
    }

    /**
     * Save an uploaded icon file
     *
     * @param array{name: string, type: string, tmp_name: string, error: int, size: int} $file The uploaded file array from $_FILES
     * @return array{success: bool, filename?: string, error?: string}
     */
    public static function saveIcon(array $file): array
    {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'error' => 'File upload failed with error code: ' . $file['error']
            ];
        }

        // Validate file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension !== 'svg') {
            return [
                'success' => false,
                'error' => 'Only SVG files are allowed'
            ];
        }

        // Validate SVG content
        if (!self::validateSVG($file['tmp_name'])) {
            return [
                'success' => false,
                'error' => 'Invalid SVG file or contains unsafe content'
            ];
        }

        // Sanitize filename
        $filename = self::sanitizeFilename($file['name']);

        // Check if file already exists
        $targetPath = BASE_PATH . '/public/icons/' . $filename;
        if (file_exists($targetPath)) {
            return [
                'success' => false,
                'error' => 'An icon with this name already exists'
            ];
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return [
                'success' => false,
                'error' => 'Failed to save icon file'
            ];
        }

        // Set proper permissions
        chmod($targetPath, 0644);

        return [
            'success' => true,
            'filename' => $filename
        ];
    }

    /**
     * Delete an icon file from the filesystem
     *
     * @param string $filename The filename to delete
     * @return bool True if deleted, false otherwise
     */
    public static function deleteIcon(string $filename): bool
    {
        $filepath = self::getIconPath($filename);

        if (!file_exists($filepath)) {
            return false;
        }

        return unlink($filepath);
    }

    /**
     * Get the full URL for an icon
     *
     * @param string $name Icon name/filename
     * @param string $type Icon type ('lucide' or 'custom')
     * @return string The URL or icon identifier
     */
    public static function getIconUrl(string $name, string $type): string
    {
        if ($type === 'custom') {
            return View::url('/public/icons/' . $name);
        }

        return $name; // Return name for Lucide icons
    }

    /**
     * Get the filesystem path for an icon
     *
     * @param string $filename The icon filename
     * @return string The full filesystem path
     */
    public static function getIconPath(string $filename): string
    {
        return BASE_PATH . '/public/icons/' . $filename;
    }

    /**
     * Get all available icons (both Lucide and custom)
     *
     * @return array{lucide: array<string>, custom: array<int, array{filename: string, display_name: string}>}
     */
    public static function getAllIcons(): array
    {
        // Load custom icons from database
        require_once BASE_PATH . '/app/models/Icon.php';
        $customIcons = Icon::all();

        // Reason: Return both icon types for the icon picker
        return [
            'lucide' => self::getLucideIcons(),
            'custom' => $customIcons
        ];
    }

    /**
     * Get list of popular Lucide icons
     *
     * @return array<string> Array of Lucide icon names
     */
    public static function getLucideIcons(): array
    {
        // Reason: Curated list of commonly used Lucide icons for the picker
        return [
            'home', 'server', 'monitor', 'cpu', 'hard-drive', 'database',
            'cloud', 'wifi', 'globe', 'link', 'link-2', 'external-link',
            'video', 'film', 'music', 'image', 'camera', 'play',
            'settings', 'tool', 'wrench', 'sliders', 'toggle-left', 'power',
            'download', 'upload', 'folder', 'file', 'file-text', 'archive',
            'lock', 'unlock', 'shield', 'key', 'eye', 'eye-off',
            'user', 'users', 'mail', 'message-square', 'bell', 'calendar',
            'search', 'filter', 'list', 'grid', 'layers', 'box',
            'package', 'shopping-cart', 'credit-card', 'dollar-sign',
            'chart-bar', 'activity', 'trending-up', 'pie-chart',
            'bookmark', 'star', 'heart', 'flag', 'tag', 'paperclip',
            'printer', 'scan', 'smartphone', 'tablet', 'laptop', 'watch',
            'thermometer', 'lightbulb', 'zap', 'battery', 'plug',
            'git-branch', 'github', 'gitlab', 'code', 'terminal', 'command'
        ];
    }

    /**
     * Sanitize a filename to be safe for filesystem
     *
     * @param string $filename The original filename
     * @return string The sanitized filename
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Remove extension
        $name = pathinfo($filename, PATHINFO_FILENAME);

        // Convert to lowercase
        $name = strtolower($name);

        // Replace spaces and underscores with hyphens
        $name = str_replace([' ', '_'], '-', $name);

        // Remove non-alphanumeric except hyphens
        $name = preg_replace('/[^a-z0-9-]/', '', $name);

        // Remove consecutive hyphens
        $name = preg_replace('/-+/', '-', $name);

        // Trim hyphens from ends
        $name = trim($name, '-');

        // Add back extension
        return $name . '.svg';
    }

    /**
     * Check if an icon can be safely deleted
     *
     * @param string $filename The icon filename
     * @return array{can_delete: bool, used_by_items: array, used_by_pages: array}
     */
    public static function canDeleteIcon(string $filename): array
    {
        $items = Database::fetchAll(
            "SELECT id, title FROM items WHERE icon = ? AND icon_type = 'custom'",
            [$filename]
        );

        $pages = Database::fetchAll(
            "SELECT id, name FROM pages WHERE icon = ? AND icon_type = 'custom'",
            [$filename]
        );

        return [
            'can_delete' => empty($items) && empty($pages),
            'used_by_items' => $items,
            'used_by_pages' => $pages
        ];
    }

    /**
     * Render an icon (either Lucide or custom)
     *
     * @param string $icon Icon name/filename
     * @param string $type Icon type ('lucide' or 'custom')
     * @param array<string> $classes CSS classes to apply
     * @return string HTML for the icon
     */
    public static function render(string $icon, string $type, array $classes = []): string
    {
        $classString = !empty($classes) ? ' class="' . implode(' ', array_map('htmlspecialchars', $classes)) . '"' : '';

        if ($type === 'custom') {
            $url = self::getIconUrl($icon, $type);
            return '<img src="' . Security::escape($url) . '"' . $classString . ' alt="Icon">';
        }

        // Lucide icon
        return '<i data-lucide="' . Security::escape($icon) . '"' . $classString . '></i>';
    }

    /**
     * Sync database with filesystem (register manually uploaded icons)
     *
     * @return array{added: int, skipped: int}
     */
    public static function syncWithFilesystem(): array
    {
        require_once BASE_PATH . '/app/models/Icon.php';

        $filesystemIcons = self::scanCustomIcons();
        $added = 0;
        $skipped = 0;

        foreach ($filesystemIcons as $filename => $data) {
            // Check if already in database
            $existing = Icon::findByFilename($filename);
            if ($existing !== null) {
                $skipped++;
                continue;
            }

            // Validate SVG
            if (!self::validateSVG($data['path'])) {
                $skipped++;
                continue;
            }

            // Add to database
            $displayName = ucwords(str_replace(['-', '_'], ' ', pathinfo($filename, PATHINFO_FILENAME)));
            Icon::create([
                'filename' => $filename,
                'display_name' => $displayName,
                'file_size' => $data['size'],
                'uploaded_by' => 'manual'
            ]);

            $added++;
        }

        return [
            'added' => $added,
            'skipped' => $skipped
        ];
    }
}
