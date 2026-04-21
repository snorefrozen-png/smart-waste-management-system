<?php
/**
 * My Reports
 * Displays all reports submitted by the logged-in resident.
 * Shows status tracking and report details.
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireRole('resident');
require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();
$userId = getCurrentUserId();

// Fetch all reports for this resident
$stmt = $pdo->prepare("SELECT r.*, 
    (SELECT t.id FROM tasks t WHERE t.report_id = r.id LIMIT 1) as task_id,
    (SELECT t.status FROM tasks t WHERE t.report_id = r.id LIMIT 1) as task_status
    FROM reports r WHERE r.user_id = :uid ORDER BY r.date_submitted DESC");
$stmt->execute([':uid' => $userId]);
$reports = $stmt->fetchAll();

$pageTitle = 'My Reports';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h2><i class="bi bi-list-check me-2 text-green"></i>My Reports</h2>
                <p>Track the status of all your waste reports.</p>
            </div>
            <div class="col-auto">
                <a href="/smart waste system/resident/submit_report.php" class="btn btn-green">
                    <i class="bi bi-plus-circle me-2"></i>New Report
                </a>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <?php if (count($reports) > 0): ?>
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th>Location</th>
                            <th>Image</th>
                            <th>Status</th>
                            <th>Date Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report): ?>
                            <tr>
                                <td><strong><?php echo $report['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars(substr($report['description'], 0, 60)) . (strlen($report['description']) > 60 ? '...' : ''); ?></td>
                                <td><?php echo htmlspecialchars($report['location']); ?></td>
                                <td>
                                    <?php if ($report['image']): ?>
                                        <a href="/smart waste system/uploads/<?php echo htmlspecialchars($report['image']); ?>" target="_blank" class="text-green">
                                            <i class="bi bi-image"></i> View
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $badgeClass = 'badge-' . $report['status'];
                                    echo '<span class="badge ' . $badgeClass . '">' . ucfirst(str_replace('-', ' ', $report['status'])) . '</span>';
                                    ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($report['date_submitted'])); ?></td>
                                <td>
                                    <?php if ($report['status'] === 'completed' && $report['task_id']): ?>
                                        <a href="/smart waste system/resident/feedback.php?task_id=<?php echo $report['task_id']; ?>" 
                                           class="btn btn-sm btn-gold">
                                            <i class="bi bi-star me-1"></i>Feedback
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>No reports found. Submit your first waste report!</p>
                <a href="/smart waste system/resident/submit_report.php" class="btn btn-green btn-sm">Submit Report</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
