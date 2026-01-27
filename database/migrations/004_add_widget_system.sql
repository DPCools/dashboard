-- Widget System Migration
-- Creates tables and indexes for the flexible widget system

-- Create widgets table
CREATE TABLE IF NOT EXISTS widgets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    page_id INTEGER NOT NULL,
    widget_type TEXT NOT NULL,
    title TEXT NOT NULL,
    icon TEXT NOT NULL DEFAULT 'box',
    icon_type TEXT NOT NULL DEFAULT 'lucide',
    display_order INTEGER NOT NULL DEFAULT 0,
    settings TEXT,
    refresh_interval INTEGER NOT NULL DEFAULT 300,
    is_enabled INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at TEXT NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE
);

-- Create widget cache table
CREATE TABLE IF NOT EXISTS widget_cache (
    widget_id INTEGER NOT NULL,
    cache_key TEXT NOT NULL,
    cache_value TEXT NOT NULL,
    expires_at INTEGER NOT NULL,
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    PRIMARY KEY (widget_id, cache_key),
    FOREIGN KEY (widget_id) REFERENCES widgets(id) ON DELETE CASCADE
);

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_widgets_page_id ON widgets(page_id);
CREATE INDEX IF NOT EXISTS idx_widgets_type ON widgets(widget_type);
CREATE INDEX IF NOT EXISTS idx_widgets_display_order ON widgets(page_id, display_order);
CREATE INDEX IF NOT EXISTS idx_widget_cache_expires ON widget_cache(expires_at);

-- Add display_type column to items table (for future unified ordering with widgets)
ALTER TABLE items ADD COLUMN display_type TEXT NOT NULL DEFAULT 'item';
CREATE INDEX IF NOT EXISTS idx_items_display_type ON items(display_type);

-- Add global widget settings
INSERT OR IGNORE INTO settings (key, value) VALUES ('widget_global_refresh', '300');
INSERT OR IGNORE INTO settings (key, value) VALUES ('widget_api_timeout', '10');
