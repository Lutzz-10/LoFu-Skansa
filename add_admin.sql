-- SQL script to add an admin account to the lofu_skansa database
-- This script inserts a new admin user into the 'users' table.
-- Password is pre-hashed using PASSWORD_DEFAULT for 'password123'.
-- Run this script in phpMyAdmin or via command line to add the admin.

USE `lofu_skansa`;

INSERT INTO `users` (`nisn_nip`, `nama_lengkap`, `password`, `level`) VALUES
('admin', 'Administrator', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 99);
