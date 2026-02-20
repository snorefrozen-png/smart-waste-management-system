<?php
/**
 * Archive Management
 * View and manage archived (completed) reports.
 * Admin can archive completed reports or restore them.
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireRole('admin');
require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();
$message = '';

// Handle archive/restore actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportId = intval($_POST['report_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    
    if ($reportId > 0) {
        if ($action === 'archive') {
            $stmt = $pdo->prepare("UPDATE reports SET status = 'archived' WHERE id = :id AND status = 'completed'");
            $stmt->execute([':id' => $reportId]);
            $message = 'Report archived successfully.';
        } elseif ($action === 'restore') {
            $stmt = $pdo->prepare("UPDATE reports SET status = 'completed' WHERE id = :id AND status = 'archived'");
            $stmt->execute([':id' => $reportId]);
            $message = 'Report restored successfully.';
        }
    }
}

// Show which view
$view = $_GET['view'] ?? 'completed';

if ($view === 'archived') {
    $reports = $pdo->query("SELECT r.*, u.name as user_name FROM reports r JOIN users u ON r.user_id = u.id 
                            WHERE r.status = 'archived' ORDER BY r.date_submitted DESC")->fetchAll();
} else {
    $reports = $pdo->query("SELECT r.*, u.name as user_name FROM reports r JOIN users u ON r.user_id = u.id 
                            WHERE r.status = 'completed' ORDER BY r.date_submitted DESC")->fetchAll();
}

$pageTitle = 'Archive';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="page-header">
        <h2><i class="bi bi-archive me-2 text-green"></i>Report Archive</h2>
        <p>Manage completed and archived reports.</p>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade-in">
            <i class="bi bi-check-circle me-2"></i><?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Toggle View -->
    <div class="mb-4">
        <div class="btn-group">
            <a href="?view=completed" class="btn btn-sm <?php echo $view !== 'archived' ? 'btn-green' : 'btn-outline-secondary'; ?>">
                <i class="bi bi-check-circle me-1"></i>Completed (<?php 
                    echo $pdo->query("SELECT COUNT(*) FROM reports WHERE status='completed'")->fetchColumn(); ?>)
            </a>
            <a href="?view=archived" class="btn btn-sm <?php echo $view === 'archived' ? 'btn-green' : 'btn-outline-secondary'; ?>">
                <i class="bi bi-archive me-1"></i>Archived (<?php 
                    echo $pdo->query("SELECT COUNT(*) FROM reports WHERE status='archived'")->fetchColumn(); ?>)
            </a>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Reported By</th>
                        <th>Description</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($reports) > 0): ?>
                        <?php foreach ($reports as $report): ?>
                            <tr>
                                <td><strong><?php echo $report['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($report['user_name']); ?></td>
                                <td><?php echo htmlspecialchars(substr($report['description'], 0, 60)) . '...'; ?></td>
                                <td><?php echo htmlspecialchars($report['location']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $report['status']; ?>">
                                        <?php echo ucfirst($report['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($report['date_submitted'])); ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                                        <?php if ($report['status'] === 'completed'): ?>
                                            <input type="hidden" name="action" value="archive">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Archive">
                                                <i class="bi bi-archive me-1"></i>Archive
                                            </button>
                                        <?php else: ?>
                                            <input type="hidden" name="action" value="restore">
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Restore">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i>Restore
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No <?php echo $view === 'archived' ? 'archived' : 'completed'; ?> reports found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
