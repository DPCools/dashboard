<?php

declare(strict_types=1);

/**
 * Application Version Configuration
 *
 * This file defines the current application version.
 * Increment this when adding new migrations or features.
 */

// Current application version (format: MAJOR.MINOR.PATCH)
define('APP_VERSION', '1.2.0');

// Database schema version (increment for each migration)
define('DB_VERSION', 6);

/**
 * Version History:
 *
 * 1.2.0 (DB v6) - 2026-02-04
 *   - File sharing system (PicoShare-like)
 *   - Upload files with shareable URLs
 *   - Expiration options (30 days, 2/4/6 months, unlimited)
 *   - Optional password protection per file
 *   - Automatic cleanup of expired files
 *   - Download tracking and access logging
 *
 * 1.1.1 (DB v5) - 2026-01-28
 *   - Reverse proxy URL detection fix
 *   - Support for Nginx Proxy Manager and other reverse proxies
 *   - Environment variable configuration (.env support)
 *   - Manual base path override option
 *   - All redirects now use View::url() for consistency
 *
 * 1.1.0 (DB v5) - 2026-01-26
 *   - Proxmox widget
 *   - Widget drag-and-drop reordering
 *   - Masonry grid layout
 *   - Multiple icon upload with progress
 *   - Unique icon filename constraint
 *   - External IP widget location accuracy improvements
 *
 * 1.0.0 (DB v4) - 2026-01-26
 *   - Initial release
 *   - Widget system (6 widget types)
 *   - Item password protection
 *   - Icon system
 *   - Multi-page dashboard
 */
