<?php
/**
 * Database Configuration
 * Nairobi City Council Smart Waste Management System
 * 
 * Uses PDO for secure database access with prepared statements.
 * Compatible with XAMPP / WAMP default settings.
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'waste_management');
define('DB_USER', 'root');
define('DB_PASS', '');  // Default XAMPP/WAMP has no password

/**
 * Get PDO database connection
 * @return PDO
 */
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}
