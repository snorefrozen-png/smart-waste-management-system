<?php
/**
 * Header Include
 * Contains the HTML head, Bootstrap 5 CDN, and responsive navigation bar.
 * Included at the top of every page.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentRole = $_SESSION['role'] ?? null;
$currentName = $_SESSION['name'] ?? 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Nairobi City Council Smart Waste Management and Reporting System">
    <title><?php echo $pageTitle ?? 'Smart Waste Management'; ?> | NCC</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/smart waste system/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark-green sticky-top shadow">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="/smart waste system/index.php">
                <i class="bi bi-recycle me-2 fs-4"></i>
                <span class="fw-bold">NCC Waste Management</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if ($currentRole === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="/smart waste system/admin/dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="/smart waste system/admin/reports.php"><i class="bi bi-file-earmark-text me-1"></i>Reports</a></li>
                        <li class="nav-item"><a class="nav-link" href="/smart waste system/admin/manage_users.php"><i class="bi bi-people me-1"></i>Users</a></li>
                        <li class="nav-item"><a class="nav-link" href="/smart waste system/admin/archive.php"><i class="bi bi-archive me-1"></i>Archive</a></li>
                    <?php elseif ($currentRole === 'resident'): ?>
                        <li class="nav-item"><a class="nav-link" href="/smart waste systen/resident/dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="/smart waste system/resident/submit_report.php"><i class="bi bi-plus-circle me-1"></i>New Report</a></li>
                        <li class="nav-item"><a class="nav-link" href="/smart waste system/resident/my_reports.php"><i class="bi bi-list-check me-1"></i>My Reports</a></li>
                    <?php elseif ($currentRole === 'collector'): ?>
                        <li class="nav-item"><a class="nav-link" href="/smart waste system/collector/dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($currentName); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><span class="dropdown-item-text text-muted small">Role: <?php echo ucfirst($currentRole); ?></span></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="/smart waste system/auth/logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="/smart waste system/index.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link btn btn-outline-light btn-sm ms-2 px-3" href="/smart waste system/auth/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Wrapper -->
    <main class="main-content">
