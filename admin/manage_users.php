<?php
/**
 * Manage Users
 * Admin can view all users, change roles, and delete accounts.
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireRole('admin');
require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();
$message = '';
$msgType = '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $targetUserId = intval($_POST['user_id'] ?? 0);
    
    // Prevent self-modification
    if ($targetUserId === getCurrentUserId()) {
        $message = 'You cannot modify your own account.';
        $msgType = 'warning';
    } else {
        switch ($_POST['action']) {
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND id != :admin_id");
                $stmt->execute([':id' => $targetUserId, ':admin_id' => getCurrentUserId()]);
                $message = 'User deleted successfully.';
                $msgType = 'success';
                break;
                
            case 'change_role':
                $newRole = $_POST['new_role'] ?? '';
                $allowedRoles = ['resident', 'admin', 'collector'];
                if (in_array($newRole, $allowedRoles)) {
                    $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
                    $stmt->execute([':role' => $newRole, ':id' => $targetUserId]);
                    $message = 'User role updated successfully.';
                    $msgType = 'success';
                }
                break;
        }
    }
}

// Fetch all users
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

$pageTitle = 'Manage Users';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="page-header">
        <h2><i class="bi bi-people me-2 text-green"></i>Manage Users</h2>
        <p>View and manage all system users.</p>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade-in">
            <i class="bi bi-<?php echo $msgType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="dashboard-card">
        <div class="card-header-custom">
            <h5><i class="bi bi-person-lines-fill me-2"></i>All Users (<?php echo count($users); ?>)</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><strong><?php echo $user['id']; ?></strong></td>
                            <td>
                                <i class="bi bi-person-circle me-1 text-muted"></i>
                                <?php echo htmlspecialchars($user['name']); ?>
                                <?php if ($user['id'] === getCurrentUserId()): ?>
                                    <span class="badge bg-success ms-1">You</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $user['role'] === 'admin' ? 'danger' : 
                                        ($user['role'] === 'collector' ? 'primary' : 'success'); 
                                ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if ($user['id'] !== getCurrentUserId()): ?>
                                    <div class="d-flex gap-1">
                                        <!-- Change Role -->
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="change_role">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <select name="new_role" onchange="this.form.submit()" class="form-select form-select-sm" style="width:auto;">
                                                <option value="resident" <?php echo $user['role'] === 'resident' ? 'selected' : ''; ?>>Resident</option>
                                                <option value="collector" <?php echo $user['role'] === 'collector' ? 'selected' : ''; ?>>Collector</option>
                                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                        </form>
                                        <!-- Delete -->
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete User">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
