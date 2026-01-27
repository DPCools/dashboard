-- Migration: Add per-item SSL certificate verification control
-- Date: 2026-01-26
-- Description: Adds ssl_verify column to items table for granular SSL verification control

-- Add ssl_verify column to items table
-- Default 0 for backward compatibility (existing items continue to work)
ALTER TABLE items ADD COLUMN ssl_verify INTEGER NOT NULL DEFAULT 0;

-- Add index for query performance
CREATE INDEX IF NOT EXISTS idx_items_ssl_verify ON items(ssl_verify);
