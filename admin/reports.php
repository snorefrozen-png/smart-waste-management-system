<?php
/**
 * Admin Reports Management
 * View all reports, assign to collectors, update status.
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireRole('admin');
require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $reportId = intval($_POST['report_id']);
        $newStatus = $_POST['status'];
        $allowed = ['pending', 'assigned', 'in-progress', 'completed', 'archived'];
        
        if (in_array($newStatus, $allowed)) {
            $stmt = $pdo->prepare("UPDATE reports SET status = :status WHERE id = :id");
            $stmt->execute([':status' => $newStatus, ':id' => $reportId]);
            
            // If archiving, also update associated task
            if ($newStatus === 'archived') {
                $stmt = $pdo->prepare("UPDATE tasks SET status = 'completed' WHERE report_id = :rid AND status != 'completed'");
                $stmt->execute([':rid' => $reportId]);
            }
        }
        header("Location: /GABU/admin/reports.php?updated=1");
        exit();
    }
}

// Fetch all reports with user info
$filter = $_GET['status'] ?? 'all';
$sql = "SELECT r.*, u.name as user_name, 
        (SELECT u2.name FROM tasks t JOIN users u2 ON t.collector_id = u2.id WHERE t.report_id = r.id LIMIT 1) as collector_name
        FROM reports r JOIN users u ON r.user_id = u.id";
if ($filter !== 'all') {
    $sql .= " WHERE r.status = :status";
}
$sql .= " ORDER BY r.date_submitted DESC";

$stmt = $pdo->prepare($sql);
if ($filter !== 'all') {
    $stmt->execute([':status' => $filter]);
} else {
    $stmt->execute();
}
$reports = $stmt->fetchAll();

// Fetch collectors for assignment
$collectors = $pdo->query("SELECT id, name FROM users WHERE role = 'collector' ORDER BY name")->fetchAll();

$pageTitle = 'Manage Reports';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h2><i class="bi bi-file-earmark-text me-2 text-green"></i>Manage Reports</h2>
                <p>View, assign, and update waste report statuses.</p>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success alert-dismissible fade-in">
            <i class="bi bi-check-circle me-2"></i>Report updated successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['assigned'])): ?>
        <div class="alert alert-success alert-dismissible fade-in">
            <i class="bi bi-check-circle me-2"></i>Task assigned successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filter Tabs -->
    <div class="mb-4">
        <div class="btn-group" role="group">
            <a href="?status=all" class="btn btn-sm <?php echo $filter === 'all' ? 'btn-green' : 'btn-outline-secondary'; ?>">All</a>
            <a href="?status=pending" class="btn btn-sm <?php echo $filter === 'pending' ? 'btn-green' : 'btn-outline-secondary'; ?>">Pending</a>
            <a href="?status=assigned" class="btn btn-sm <?php echo $filter === 'assigned' ? 'btn-green' : 'btn-outline-secondary'; ?>">Assigned</a>
            <a href="?status=in-progress" class="btn btn-sm <?php echo $filter === 'in-progress' ? 'btn-green' : 'btn-outline-secondary'; ?>">In Progress</a>
            <a href="?status=completed" class="btn btn-sm <?php echo $filter === 'completed' ? 'btn-green' : 'btn-outline-secondary'; ?>">Completed</a>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="dashboard-card">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Reported By</th>
                        <th>Description</th>
                        <th>Location</th>
                        <th>Image</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($reports) > 0): ?>
                        <?php foreach ($reports as $report): ?>
                            <tr>
                                <td><strong><?php echo $report['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($report['user_name']); ?></td>
                                <td><?php echo htmlspecialchars(substr($report['description'], 0, 50)) . '...'; ?></td>
                                <td><?php echo htmlspecialchars($report['location']); ?></td>
                                <td>
                                    <?php if ($report['image']): ?>
                                        <a href="/GABU/uploads/<?php echo htmlspecialchars($report['image']); ?>" target="_blank" class="text-green">
                                            <i class="bi bi-image"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $report['status']; ?>">
                                        <?php echo ucfirst(str_replace('-', ' ', $report['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $report['collector_name'] ? htmlspecialchars($report['collector_name']) : '<span class="text-muted">—</span>'; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($report['date_submitted'])); ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <!-- Assign to Collector -->
                                        <?php if ($report['status'] === 'pending'): ?>
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" 
                                                    data-bs-target="#assignModal<?php echo $report['id']; ?>">
                                                <i class="bi bi-person-plus"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <!-- Update Status -->
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                                            <select name="status" onchange="this.form.submit()" class="form-select form-select-sm" style="width:auto;min-width:120px;">
                                                <?php 
                                                $statuses = ['pending', 'assigned', 'in-progress', 'completed', 'archived'];
                                                foreach ($statuses as $s): ?>
                                                    <option value="<?php echo $s; ?>" <?php echo $report['status'] === $s ? 'selected' : ''; ?>>
                                                        <?php echo ucfirst(str_replace('-', ' ', $s)); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    </div>

                                    <!-- Assign Modal -->
                                    <?php if ($report['status'] === 'pending'): ?>
                                    <div class="modal fade" id="assignModal<?php echo $report['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Assign Report #<?php echo $report['id']; ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="/GABU/admin/assign_task.php" method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Select Collector</label>
                                                            <select name="collector_id" class="form-select" required>
                                                                <option value="">-- Choose Collector --</option>
                                                                <?php foreach ($collectors as $c): ?>
                                                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-green">Assign</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>No reports found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
