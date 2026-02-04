-- Migration: Add File Sharing System
-- Date: 2026-02-04
-- Purpose: PicoShare-like file sharing with expiration, password protection, and access logging

-- Table: shared_files
-- Stores metadata for shared files
CREATE TABLE IF NOT EXISTS shared_files (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    token TEXT NOT NULL UNIQUE,                    -- 64-char unique share token
    stored_filename TEXT NOT NULL UNIQUE,          -- UUID-based filename on disk
    original_filename TEXT NOT NULL,               -- User's original filename
    file_size INTEGER NOT NULL,                    -- File size in bytes
    mime_type TEXT NOT NULL,                       -- MIME type (e.g., application/pdf)
    description TEXT,                              -- Optional description
    expires_at TEXT,                               -- Expiration date (NULL = unlimited)
    password_hash TEXT,                            -- Argon2ID hash (NULL = no password)
    download_count INTEGER NOT NULL DEFAULT 0,     -- Number of downloads
    uploaded_by TEXT NOT NULL DEFAULT 'admin',     -- Uploader identifier
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    accessed_at TEXT                               -- Last access timestamp
);

-- Table: shared_file_access_log
-- Audit trail for file access attempts
CREATE TABLE IF NOT EXISTS shared_file_access_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    file_id INTEGER NOT NULL,
    access_type TEXT NOT NULL,                     -- 'view', 'download', 'failed_password'
    accessor_ip TEXT,                              -- IP address of accessor
    user_agent TEXT,                               -- Browser user agent
    accessed_at TEXT NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (file_id) REFERENCES shared_files(id) ON DELETE CASCADE
);

-- Indexes for performance
CREATE INDEX IF NOT EXISTS idx_shared_files_token ON shared_files(token);
CREATE INDEX IF NOT EXISTS idx_shared_files_expires_at ON shared_files(expires_at);
CREATE INDEX IF NOT EXISTS idx_shared_files_created_at ON shared_files(created_at);
CREATE INDEX IF NOT EXISTS idx_shared_file_access_log_file_id ON shared_file_access_log(file_id);
CREATE INDEX IF NOT EXISTS idx_shared_file_access_log_accessed_at ON shared_file_access_log(accessed_at);
CREATE INDEX IF NOT EXISTS idx_shared_file_access_log_access_type ON shared_file_access_log(access_type);

-- Settings for file sharing configuration
INSERT OR IGNORE INTO settings (key, value) VALUES ('fileshare_enabled', '1');
INSERT OR IGNORE INTO settings (key, value) VALUES ('fileshare_max_file_size', '104857600');      -- 100MB
INSERT OR IGNORE INTO settings (key, value) VALUES ('fileshare_max_total_size', '10737418240');   -- 10GB
