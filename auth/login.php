<?php
/**
 * Login Handler
 * Validates credentials, creates session, and redirects by role.
 */
session_start();
require_once __DIR__ . '/../config/database.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /smart waste system/index.php");
    exit();
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validate input
if (empty($email) || empty($password)) {
    header("Location: /smart waste system/index.php?error=invalid");
    exit();
}

try {
    $pdo = getDBConnection();
    
    // Fetch user by email using prepared statement
    $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    // Verify password
    if ($user && password_verify($password, $user['password'])) {
        // Create session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['role']    = $user['role'];

        // Redirect based on role
        $redirects = [
            'admin'     => '/smart waste system/admin/dashboard.php',
            'resident'  => '/smart waste system/resident/dashboard.php',
            'collector' => '/smart waste system/collector/dashboard.php',
        ];
        header("Location: " . ($redirects[$user['role']] ?? '/smart waste system/index.php'));
        exit();
    } else {
        header("Location: /smart waste system/index.php?error=invalid");
        exit();
    }
} catch (PDOException $e) {
    header("Location: /smart waste system/index.php?error=invalid");
    exit();
}
