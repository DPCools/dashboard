<?php

declare(strict_types=1);

/**
 * Application Version Configuration
 *
 * This file defines the current application version.
 * Increment this when adding new migrations or features.
 */

// Current application version (format: MAJOR.MINOR.PATCH)
define('APP_VERSION', '1.1.0');

// Database schema version (increment for each migration)
define('DB_VERSION', 5);

/**
 * Version History:
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
