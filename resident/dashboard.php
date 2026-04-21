<?php
/**
 * Resident Dashboard
 * Shows stats overview and recent reports for the logged-in resident.
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireRole('resident');
require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();
$userId = getCurrentUserId();

// Get report statistics for this resident
$stmt = $pdo->prepare("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'assigned' OR status = 'in-progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'completed' OR status = 'archived' THEN 1 ELSE 0 END) as completed
    FROM reports WHERE user_id = :uid");
$stmt->execute([':uid' => $userId]);
$stats = $stmt->fetch();

// Get recent reports (last 5)
$stmt = $pdo->prepare("SELECT * FROM reports WHERE user_id = :uid ORDER BY date_submitted DESC LIMIT 5");
$stmt->execute([':uid' => $userId]);
$recentReports = $stmt->fetchAll();

$pageTitle = 'Resident Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h2><i class="bi bi-speedometer2 me-2 text-green"></i>Dashboard</h2>
                <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong>! Here's your waste reporting overview.</p>
            </div>
            <div class="col-auto">
                <a href="/smart waste system/resident/submit_report.php" class="btn btn-green">
                    <i class="bi bi-plus-circle me-2"></i>New Report
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon icon-blue"><i class="bi bi-file-earmark-text"></i></div>
                </div>
                <div class="stat-value"><?php echo $stats['total'] ?? 0; ?></div>
                <div class="stat-label">Total Reports</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon icon-orange"><i class="bi bi-clock"></i></div>
                </div>
                <div class="stat-value"><?php echo $stats['pending'] ?? 0; ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon icon-purple"><i class="bi bi-arrow-repeat"></i></div>
                </div>
                <div class="stat-value"><?php echo $stats['in_progress'] ?? 0; ?></div>
                <div class="stat-label">In Progress</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon icon-green"><i class="bi bi-check-circle"></i></div>
                </div>
                <div class="stat-value"><?php echo $stats['completed'] ?? 0; ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
    </div>

    <!-- Recent Reports Table -->
    <div class="dashboard-card">
        <div class="card-header-custom">
            <h5><i class="bi bi-clock-history me-2"></i>Recent Reports</h5>
            <a href="/smart waste system/resident/my_reports.php" class="btn btn-sm btn-outline-secondary">View All</a>
        </div>

        <?php if (count($recentReports) > 0): ?>
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentReports as $report): ?>
                            <tr>
                                <td><strong><?php echo $report['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars(substr($report['description'], 0, 50)) . (strlen($report['description']) > 50 ? '...' : ''); ?></td>
                                <td><?php echo htmlspecialchars($report['location']); ?></td>
                                <td>
                                    <?php
                                    $badgeClass = 'badge-' . $report['status'];
                                    echo '<span class="badge ' . $badgeClass . '">' . ucfirst(str_replace('-', ' ', $report['status'])) . '</span>';
                                    ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($report['date_submitted'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>You haven't submitted any reports yet.</p>
                <a href="/smart waste system/resident/submit_report.php" class="btn btn-green btn-sm">Submit Your First Report</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
