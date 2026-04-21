<?php
/**
 * Feedback Submission
 * Allows residents to submit feedback on completed tasks.
 * Includes a rating (1-5 stars) and a comment.
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireRole('resident');
require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();
$userId = getCurrentUserId();
$taskId = intval($_GET['task_id'] ?? 0);

$error = '';
$success = '';

// Verify task exists and belongs to a report by this user
$stmt = $pdo->prepare("SELECT t.*, r.description, r.location 
                       FROM tasks t 
                       JOIN reports r ON t.report_id = r.id 
                       WHERE t.id = :tid AND r.user_id = :uid AND t.status = 'completed'");
$stmt->execute([':tid' => $taskId, ':uid' => $userId]);
$task = $stmt->fetch();

if (!$task) {
    header("Location: /smart waste system/resident/my_reports.php");
    exit();
}

// Check if feedback already exists
$stmt = $pdo->prepare("SELECT id FROM feedback WHERE user_id = :uid AND task_id = :tid LIMIT 1");
$stmt->execute([':uid' => $userId, ':tid' => $taskId]);
$existingFeedback = $stmt->fetch();

// Process feedback form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$existingFeedback) {
    $rating  = intval($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $error = 'Please select a rating between 1 and 5.';
    } elseif (empty($comment) || strlen($comment) < 5) {
        $error = 'Comment must be at least 5 characters.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO feedback (user_id, task_id, comment, rating) VALUES (:uid, :tid, :comment, :rating)");
            $stmt->execute([
                ':uid'     => $userId,
                ':tid'     => $taskId,
                ':comment' => $comment,
                ':rating'  => $rating,
            ]);
            $success = 'Thank you for your feedback!';
            $existingFeedback = true;
        } catch (PDOException $e) {
            $error = 'Failed to submit feedback. Please try again.';
        }
    }
}

$pageTitle = 'Submit Feedback';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="page-header">
        <h2><i class="bi bi-star me-2 text-green"></i>Submit Feedback</h2>
        <p>Rate and review the waste collection service for your report.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <!-- Task Info -->
            <div class="dashboard-card mb-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>Report Details</h6>
                <div class="row">
                    <div class="col-sm-6 mb-2">
                        <span class="text-muted small">Description:</span>
                        <p class="mb-0"><?php echo htmlspecialchars($task['description']); ?></p>
                    </div>
                    <div class="col-sm-3 mb-2">
                        <span class="text-muted small">Location:</span>
                        <p class="mb-0"><?php echo htmlspecialchars($task['location']); ?></p>
                    </div>
                    <div class="col-sm-3 mb-2">
                        <span class="text-muted small">Completed:</span>
                        <p class="mb-0"><?php echo $task['date_completed'] ? date('M d, Y', strtotime($task['date_completed'])) : 'N/A'; ?></p>
                    </div>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success fade-in">
                    <i class="bi bi-check-circle me-2"></i><?php echo $success; ?>
                </div>
                <a href="/smart waste system/resident/my_reports.php" class="btn btn-green">
                    <i class="bi bi-arrow-left me-2"></i>Back to My Reports
                </a>
            <?php elseif ($existingFeedback): ?>
                <div class="alert alert-info fade-in">
                    <i class="bi bi-info-circle me-2"></i>You have already submitted feedback for this task.
                </div>
                <a href="/smart waste system/resident/my_reports.php" class="btn btn-green">
                    <i class="bi bi-arrow-left me-2"></i>Back to My Reports
                </a>
            <?php else: ?>
                <div class="form-card">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade-in">
                            <i class="bi bi-x-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <!-- Star Rating -->
                        <div class="mb-3">
                            <label class="form-label"><i class="bi bi-star me-1"></i>Rating <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" name="rating" id="star<?php echo $i; ?>" 
                                               value="<?php echo $i; ?>" required>
                                        <label class="form-check-label" for="star<?php echo $i; ?>">
                                            <?php echo $i; ?> <i class="bi bi-star-fill text-warning"></i>
                                        </label>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <!-- Comment -->
                        <div class="mb-4">
                            <label for="comment" class="form-label">
                                <i class="bi bi-chat-dots me-1"></i>Comment <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="comment" name="comment" rows="3" 
                                      placeholder="Share your experience with the waste collection service..." 
                                      required minlength="5"></textarea>
                        </div>

                        <button type="submit" class="btn btn-green">
                            <i class="bi bi-send me-2"></i>Submit Feedback
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
