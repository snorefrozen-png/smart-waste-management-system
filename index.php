<?php
/**
 * Landing Page / Login
 * Nairobi City Council Smart Waste Management System
 * 
 * Displays a hero section with login form.
 * Redirects authenticated users to their dashboard.
 */
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $redirects = [
        'admin'     => '/GABU/admin/dashboard.php',
        'resident'  => '/GABU/resident/dashboard.php',
        'collector' => '/GABU/collector/dashboard.php',
    ];
    header("Location: " . ($redirects[$_SESSION['role']] ?? '/GABU/index.php'));
    exit();
}

$error = $_GET['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Nairobi City Council Smart Waste Management and Reporting System - Login">
    <title>NCC Smart Waste Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/GABU/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Hero Section with Login -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <!-- Left: Hero Text -->
                <div class="col-lg-6 text-white">
                    <span class="hero-badge">🏛️ Nairobi City Council</span>
                    <h1 class="hero-title mb-3">Smart Waste<br>Management &<br>Reporting System</h1>
                    <p class="hero-subtitle mb-4">
                        A modern platform to report, track, and manage waste collection 
                        across Nairobi. Together, let's keep our city clean, green, and sustainable.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-white bg-opacity-25 p-2 me-2">
                                <i class="bi bi-check-lg text-white"></i>
                            </div>
                            <span class="small">Report waste issues</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-white bg-opacity-25 p-2 me-2">
                                <i class="bi bi-check-lg text-white"></i>
                            </div>
                            <span class="small">Track resolution</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-white bg-opacity-25 p-2 me-2">
                                <i class="bi bi-check-lg text-white"></i>
                            </div>
                            <span class="small">Real-time analytics</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Login Form -->
                <div class="col-lg-5 offset-lg-1">
                    <div class="login-card">
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center bg-green-soft rounded-circle mb-3" style="width:64px;height:64px;">
                                <i class="bi bi-recycle text-green fs-2"></i>
                            </div>
                            <h4 class="fw-bold">Welcome Back</h4>
                            <p class="text-muted small">Sign in to your account</p>
                        </div>

                        <?php if ($error === 'login_required'): ?>
                            <div class="alert alert-warning alert-dismissible fade-in" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>Please login to continue.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php elseif ($error === 'invalid'): ?>
                            <div class="alert alert-danger alert-dismissible fade-in" role="alert">
                                <i class="bi bi-x-circle me-2"></i>Invalid email or password.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['registered'])): ?>
                            <div class="alert alert-success alert-dismissible fade-in" role="alert">
                                <i class="bi bi-check-circle me-2"></i>Registration successful! Please login.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form id="loginForm" action="/GABU/auth/login.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control border-start-0" id="email" name="email" 
                                           placeholder="your@email.com" required>
                                </div>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control border-start-0" id="password" name="password" 
                                           placeholder="Enter your password" required minlength="6">
                                </div>
                                <div class="invalid-feedback">Password must be at least 6 characters.</div>
                            </div>
                            <button type="submit" class="btn btn-green w-100 py-2 mb-3">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                            </button>
                        </form>
                        <p class="text-center text-muted small mb-0">
                            Don't have an account? 
                            <a href="/GABU/auth/register.php" class="text-decoration-none fw-semibold text-green">Register here</a>
                        </p>

                        <!-- Demo Credentials -->
                        <hr class="my-3">
                        <div class="text-center">
                            <p class="small text-muted mb-1"><i class="bi bi-info-circle me-1"></i>Demo Credentials:</p>
                            <p class="small text-muted mb-0">
                                Admin: <code>admin@ncc.go.ke</code> | 
                                Collector: <code>collector@ncc.go.ke</code>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/GABU/assets/js/main.js"></script>
</body>
</html>
