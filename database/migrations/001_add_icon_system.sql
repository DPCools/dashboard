-- Migration: Add custom icon system support
-- Date: 2026-01-26
-- Description: Adds icon_type column to pages and items tables, creates icons table

-- Add icon_type column to pages table
ALTER TABLE pages ADD COLUMN icon_type TEXT NOT NULL DEFAULT 'lucide';

-- Add icon_type column to items table
ALTER TABLE items ADD COLUMN icon_type TEXT NOT NULL DEFAULT 'lucide';

-- Create icons table for custom icon tracking
CREATE TABLE IF NOT EXISTS icons (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    filename TEXT NOT NULL UNIQUE,
    display_name TEXT NOT NULL,
    file_size INTEGER NOT NULL,
    uploaded_at TEXT NOT NULL DEFAULT (datetime('now')),
    uploaded_by TEXT DEFAULT 'admin'
);

-- Create index on filename for faster lookups
CREATE INDEX IF NOT EXISTS idx_icons_filename ON icons(filename);

-- Update existing records to have icon_type = 'lucide'
-- This ensures backward compatibility with existing data
UPDATE pages SET icon_type = 'lucide' WHERE icon_type IS NULL OR icon_type = '';
UPDATE items SET icon_type = 'lucide' WHERE icon_type IS NULL OR icon_type = '';
