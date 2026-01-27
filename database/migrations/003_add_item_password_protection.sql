-- Migration: Add password protection to items
-- Date: 2026-01-26
-- Description: Adds is_private and password_hash columns to items table for per-item password protection

-- Add is_private column to items table (0 = public, 1 = password protected)
ALTER TABLE items ADD COLUMN is_private INTEGER NOT NULL DEFAULT 0;

-- Add password_hash column to items table (stores hashed password when is_private = 1)
ALTER TABLE items ADD COLUMN password_hash TEXT;

-- Create index for faster lookup of private items
CREATE INDEX IF NOT EXISTS idx_items_is_private ON items(is_private);

-- Ensure all existing items are public by default (backward compatibility)
UPDATE items SET is_private = 0 WHERE is_private IS NULL;
