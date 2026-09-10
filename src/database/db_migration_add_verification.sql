-- Run this ONCE in phpMyAdmin's SQL tab if you already imported schema.sql
-- before today. It upgrades your existing `users` table in place without
-- losing any data. Anyone importing the updated schema.sql fresh does NOT
-- need this file.

USE chrontex;

ALTER TABLE users
    MODIFY COLUMN status ENUM('unverified','active','blocked') NOT NULL DEFAULT 'unverified',
    ADD COLUMN verification_token VARCHAR(64) NULL AFTER status,
    ADD COLUMN verification_expires TIMESTAMP NULL AFTER verification_token;
