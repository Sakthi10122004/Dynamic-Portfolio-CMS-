-- ============================================================
-- Portfolio Database Upgrade v3 — Blog Images + Messages Fix
-- Run this migration to add image support to blog posts
-- ============================================================

-- Add image column to notes table for blog cover images
ALTER TABLE notes ADD COLUMN IF NOT EXISTS image VARCHAR(255) DEFAULT NULL AFTER content;

-- Ensure contact_messages table has read_status column
ALTER TABLE contact_messages ADD COLUMN IF NOT EXISTS read_status TINYINT(1) DEFAULT 0 AFTER ip_address;

-- Ensure contact_messages table has ip_address column
ALTER TABLE contact_messages ADD COLUMN IF NOT EXISTS ip_address VARCHAR(45) DEFAULT NULL AFTER message;
