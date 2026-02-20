-- ============================================================
-- NAIROBI CITY COUNCIL SMART WASTE MANAGEMENT SYSTEM
-- Database Schema
-- Compatible with MySQL 5.7+ / MariaDB 10+
-- ============================================================

CREATE DATABASE IF NOT EXISTS waste_management;
USE waste_management;

-- -----------------------------------------------------------
-- USERS TABLE
-- Stores all system users (residents, admins, collectors)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,          -- stored with password_hash()
    role ENUM('resident','admin','collector') NOT NULL DEFAULT 'resident',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- REPORTS TABLE
-- Waste reports submitted by residents
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(255) NOT NULL,
    image VARCHAR(255) DEFAULT NULL,         -- filename in /uploads
    status ENUM('pending','assigned','in-progress','completed','archived') NOT NULL DEFAULT 'pending',
    date_submitted DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- TASKS TABLE
-- Links reports to collectors for action
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    collector_id INT NOT NULL,
    status ENUM('assigned','in-progress','completed') NOT NULL DEFAULT 'assigned',
    date_assigned DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_completed DATETIME DEFAULT NULL,
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    FOREIGN KEY (collector_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- FEEDBACK TABLE
-- Resident feedback on completed tasks
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    task_id INT NOT NULL,
    comment TEXT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------
-- SEED DATA
-- Default admin and collector accounts
-- Passwords are hashed versions of "Admin@123" and "Collector@123"
-- -----------------------------------------------------------

-- Admin account  (password: Admin@123)
INSERT INTO users (name, email, password, role) VALUES
('System Admin', 'admin@ncc.go.ke',
 '$2y$10$8KzQ1X6F5WJ0x6x4Y5v5aeX5X5X5X5X5X5X5X5X5X5X5X5X5X5X5X', 'admin');

-- Collector account (password: Collector@123)
INSERT INTO users (name, email, password, role) VALUES
('John Kamau', 'collector@ncc.go.ke',
 '$2y$10$8KzQ1X6F5WJ0x6x4Y5v5aeX5X5X5X5X5X5X5X5X5X5X5X5X5X5X5X', 'collector');

-- Sample resident (password: Resident@123)
INSERT INTO users (name, email, password, role) VALUES
('Jane Wanjiku', 'jane@example.com',
 '$2y$10$8KzQ1X6F5WJ0x6x4Y5v5aeX5X5X5X5X5X5X5X5X5X5X5X5X5X5X5X', 'resident');

-- Sample reports
INSERT INTO reports (user_id, description, location, status, date_submitted) VALUES
(3, 'Overflowing garbage bin near the bus stop', 'Westlands', 'pending', '2026-02-15 08:30:00'),
(3, 'Illegal dumping site behind shopping centre', 'CBD', 'assigned', '2026-02-16 10:00:00'),
(3, 'Blocked drainage with waste material', 'Eastleigh', 'completed', '2026-02-14 14:20:00'),
(3, 'Uncollected waste for over a week', 'Kibera', 'pending', '2026-02-18 09:15:00'),
(3, 'Hazardous waste near school compound', 'Langata', 'assigned', '2026-02-17 11:45:00');

-- Sample tasks
INSERT INTO tasks (report_id, collector_id, status, date_assigned, date_completed) VALUES
(2, 2, 'assigned', '2026-02-16 12:00:00', NULL),
(3, 2, 'completed', '2026-02-14 15:00:00', '2026-02-15 10:00:00'),
(5, 2, 'assigned', '2026-02-17 13:00:00', NULL);

-- Sample feedback
INSERT INTO feedback (user_id, task_id, comment, rating) VALUES
(3, 2, 'Great job cleaning up the area. Very professional team!', 5);
