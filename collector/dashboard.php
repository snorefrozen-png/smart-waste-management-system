<?php
/**
 * Collector Dashboard
 * Shows assigned tasks for the logged-in collector.
 * Allows status updates: assigned → in-progress → completed.
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireRole('collector');
require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();
$collectorId = getCurrentUserId();

// Get task stats
$stmt = $pdo->prepare("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN t.status = 'assigned' THEN 1 ELSE 0 END) as assigned,
    SUM(CASE WHEN t.status = 'in-progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) as completed
    FROM tasks t WHERE t.collector_id = :cid");
$stmt->execute([':cid' => $collectorId]);
$stats = $stmt->fetch();

// Fetch assigned tasks with report details
$stmt = $pdo->prepare("SELECT t.*, r.description, r.location, r.image, r.date_submitted, u.name as resident_name
    FROM tasks t
    JOIN reports r ON t.report_id = r.id
    JOIN users u ON r.user_id = u.id
    WHERE t.collector_id = :cid
    ORDER BY 
        CASE t.status 
            WHEN 'in-progress' THEN 1 
            WHEN 'assigned' THEN 2 
            WHEN 'completed' THEN 3 
        END,
        t.date_assigned DESC");
$stmt->execute([':cid' => $collectorId]);
$tasks = $stmt->fetchAll();

$pageTitle = 'Collector Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <h2><i class="bi bi-speedometer2 me-2 text-green"></i>Collector Dashboard</h2>
        <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong>. Manage your assigned waste collection tasks below.</p>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success alert-dismissible fade-in">
            <i class="bi bi-check-circle me-2"></i>Task status updated successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon icon-blue"><i class="bi bi-clipboard-check"></i></div>
                </div>
                <div class="stat-value"><?php echo $stats['total'] ?? 0; ?></div>
                <div class="stat-label">Total Tasks</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="d-flex align-items-center mb-3">
                    <div class="stat-icon icon-orange"><i class="bi bi-hourglass-split"></i></div>
                </div>
                <div class="stat-value"><?php echo $stats['assigned'] ?? 0; ?></div>
                <div class="stat-label">Assigned</div>
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

    <!-- Tasks Table -->
    <div class="dashboard-card">
        <div class="card-header-custom">
            <h5><i class="bi bi-list-task me-2"></i>My Tasks</h5>
        </div>
        
        <?php if (count($tasks) > 0): ?>
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Task #</th>
                            <th>Report Description</th>
                            <th>Location</th>
                            <th>Reported By</th>
                            <th>Image</th>
                            <th>Status</th>
                            <th>Date Assigned</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td><strong><?php echo $task['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars(substr($task['description'], 0, 50)) . (strlen($task['description']) > 50 ? '...' : ''); ?></td>
                                <td>
                                    <i class="bi bi-geo-alt me-1 text-green"></i>
                                    <?php echo htmlspecialchars($task['location']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($task['resident_name']); ?></td>
                                <td>
                                    <?php if ($task['image']): ?>
                                        <a href="/GABU/uploads/<?php echo htmlspecialchars($task['image']); ?>" target="_blank" class="text-green">
                                            <i class="bi bi-image"></i> View
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $task['status']; ?>">
                                        <?php echo ucfirst(str_replace('-', ' ', $task['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($task['date_assigned'])); ?></td>
                                <td>
                                    <?php if ($task['status'] !== 'completed'): ?>
                                        <form action="/GABU/collector/update_task.php" method="POST" class="d-inline">
                                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                            <?php if ($task['status'] === 'assigned'): ?>
                                                <input type="hidden" name="new_status" value="in-progress">
                                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Start Task">
                                                    <i class="bi bi-play-fill me-1"></i>Start
                                                </button>
                                            <?php elseif ($task['status'] === 'in-progress'): ?>
                                                <input type="hidden" name="new_status" value="completed">
                                                <button type="submit" class="btn btn-sm btn-green" title="Complete Task">
                                                    <i class="bi bi-check-lg me-1"></i>Complete
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-success small"><i class="bi bi-check-circle me-1"></i>Done</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-clipboard-x"></i>
                <p>No tasks assigned yet. Check back later!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
