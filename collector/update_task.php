<?php
/**
 * Update Task Status Handler
 * Collectors use this to update task status:
 * assigned → in-progress → completed
 * Also updates the parent report status accordingly.
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireRole('collector');
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /smart waste system/collector/dashboard.php");
    exit();
}

$taskId   = intval($_POST['task_id'] ?? 0);
$newStatus = $_POST['new_status'] ?? '';
$collectorId = getCurrentUserId();

// Validate
$allowedStatuses = ['in-progress', 'completed'];
if ($taskId <= 0 || !in_array($newStatus, $allowedStatuses)) {
    header("Location: /smart waste system/collector/dashboard.php");
    exit();
}

try {
    $pdo = getDBConnection();
    
    // Verify this task belongs to the collector
    $stmt = $pdo->prepare("SELECT t.*, r.id as rid FROM tasks t JOIN reports r ON t.report_id = r.id 
                           WHERE t.id = :tid AND t.collector_id = :cid LIMIT 1");
    $stmt->execute([':tid' => $taskId, ':cid' => $collectorId]);
    $task = $stmt->fetch();
    
    if (!$task) {
        header("Location: /smart waste system/collector/dashboard.php");
        exit();
    }
    
    // Update task status
    if ($newStatus === 'completed') {
        $stmt = $pdo->prepare("UPDATE tasks SET status = :status, date_completed = NOW() WHERE id = :tid");
    } else {
        $stmt = $pdo->prepare("UPDATE tasks SET status = :status WHERE id = :tid");
    }
    $stmt->execute([':status' => $newStatus, ':tid' => $taskId]);
    
    // Update parent report status
    $stmt = $pdo->prepare("UPDATE reports SET status = :status WHERE id = :rid");
    $stmt->execute([':status' => $newStatus, ':rid' => $task['rid']]);
    
    header("Location: /smart waste system/collector/dashboard.php?updated=1");
    exit();
} catch (PDOException $e) {
    header("Location: /smart waste system/collector/dashboard.php");
    exit();
}
