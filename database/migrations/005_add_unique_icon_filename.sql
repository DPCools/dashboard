-- Add unique constraint to icons filename
-- This prevents duplicate icon uploads at the database level

-- Drop existing index
DROP INDEX IF EXISTS idx_icons_filename;

-- Create unique index on filename
CREATE UNIQUE INDEX idx_icons_filename_unique ON icons(filename);
