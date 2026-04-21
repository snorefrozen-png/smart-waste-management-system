<?php
/**
 * Assign Task Handler
 * Creates a task record linking a report to a collector.
 * Updates the report status to 'assigned'.
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireRole('admin');
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /smart waste system/admin/reports.php");
    exit();
}

$reportId    = intval($_POST['report_id'] ?? 0);
$collectorId = intval($_POST['collector_id'] ?? 0);

if ($reportId <= 0 || $collectorId <= 0) {
    header("Location: /smart waste system/admin/reports.php?error=invalid");
    exit();
}

try {
    $pdo = getDBConnection();
    
    // Check if task already exists for this report
    $stmt = $pdo->prepare("SELECT id FROM tasks WHERE report_id = :rid LIMIT 1");
    $stmt->execute([':rid' => $reportId]);
    
    if ($stmt->fetch()) {
        // Update existing task
        $stmt = $pdo->prepare("UPDATE tasks SET collector_id = :cid, status = 'assigned', date_assigned = NOW() WHERE report_id = :rid");
        $stmt->execute([':cid' => $collectorId, ':rid' => $reportId]);
    } else {
        // Create new task
        $stmt = $pdo->prepare("INSERT INTO tasks (report_id, collector_id, status) VALUES (:rid, :cid, 'assigned')");
        $stmt->execute([':rid' => $reportId, ':cid' => $collectorId]);
    }
    
    // Update report status to assigned
    $stmt = $pdo->prepare("UPDATE reports SET status = 'assigned' WHERE id = :id");
    $stmt->execute([':id' => $reportId]);
    
    header("Location: /smart waste system/admin/reports.php?assigned=1");
    exit();
} catch (PDOException $e) {
    header("Location: /smart waste system/admin/reports.php?error=failed");
    exit();
}
