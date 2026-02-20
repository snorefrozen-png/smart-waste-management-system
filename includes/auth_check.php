<?php

/**
 * Authentication & Role-Based Access Guard
 * 
 * Include this file at the top of any protected page.
 * Call requireRole() with the allowed role to restrict access.
 */

session_start();

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/**
 * Require a specific role to access the page.
 * Redirects to login if not authenticated or to appropriate dashboard if wrong role.
 * 
 * @param string $role  The required role ('admin', 'resident', 'collector')
 */
function requireRole($role)
{
    if (!isLoggedIn()) {
        header("Location: /GABU/index.php?error=login_required");
        exit();
    }
    if ($_SESSION['role'] !== $role) {
        // Redirect to their own dashboard
        $redirects = [
            'admin'     => '/GABU/admin/dashboard.php',
            'resident'  => '/GABU/resident/dashboard.php',
            'collector' => '/GABU/collector/dashboard.php',
        ];
        $dest = $redirects[$_SESSION['role']] ?? '/GABU/index.php';
        header("Location: $dest");
        exit();
    }
}

/**
 * Get current user's ID from session
 * @return int|null
 */
function getCurrentUserId()
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user's role from session
 * @return string|null
 */
function getCurrentUserRole()
{
    return $_SESSION['role'] ?? null;
}
