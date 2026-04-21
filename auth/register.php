<?php
/**
 * Registration Page
 * Allows new residents to create an account.
 * Uses password_hash() for secure password storage.
 */
session_start();
require_once __DIR__ . '/../config/database.php';

$error = '';
$success = '';

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $role     = $_POST['role'] ?? 'resident'; // 👈 ADD THIS

    // Only allow resident or collector (security)
    if (!in_array($role, ['resident', 'collector'])) {
        $role = 'resident';
    }

    // Server-side validation
    if (empty($name) || strlen($name) < 2) {
        $error = 'Name must be at least 2 characters.';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (empty($password) || strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $pdo = getDBConnection();

            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            
            if ($stmt->fetch()) {
                $error = 'An account with this email already exists.';
            } else {

                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO users (name, email, password, role)
                    VALUES (:name, :email, :password, :role)
                ");

                $stmt->execute([
                    ':name'     => $name,
                    ':email'    => $email,
                    ':password' => $hashedPassword,
                    ':role'     => $role
                ]);

                header("Location: /smart waste system/index.php?registered=1");
                exit();
            }
        } catch (PDOException $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
$pageTitle = 'Register';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | NCC Smart Waste Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/smart waste system/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-7">
                    <div class="login-card">
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center bg-green-soft rounded-circle mb-3" style="width:64px;height:64px;">
                                <i class="bi bi-person-plus text-green fs-2"></i>
                            </div>
                            <h4 class="fw-bold">Create Account</h4>
                            <p class="text-muted small">Join us in keeping Nairobi clean</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade-in" role="alert">
                                <i class="bi bi-x-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form id="registerForm" action="" method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control border-start-0" id="name" name="name" 
                                           placeholder="John Doe" value="<?php echo htmlspecialchars($name ?? ''); ?>" required minlength="2">
                                </div>
                                <div class="invalid-feedback">Name must be at least 2 characters.</div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control border-start-0" id="email" name="email" 
                                           placeholder="your@email.com" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                                </div>
                                <div class="invalid-feedback">Please enter a valid email.</div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control border-start-0" id="password" name="password" 
                                           placeholder="At least 6 characters" required minlength="6">
                                </div>
                                <div class="invalid-feedback">Password must be at least 6 characters.</div>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" class="form-control border-start-0" id="confirm_password" name="confirm_password" 
                                           placeholder="Repeat your password" required>
                                </div>
                                <div class="invalid-feedback">Passwords do not match.</div>
                            </div>
                            <div class="mb-4">
    <label for="role" class="form-label">Select Role</label>
    <div class="input-group">
        <span class="input-group-text bg-light border-end-0">
            <i class="bi bi-person-badge"></i>
        </span>
        <select class="form-select border-start-0" id="role" name="role" required>
            <option value="">Choose your role</option>
            <option value="resident" <?php if(($role ?? '') == 'resident') echo 'selected'; ?>>
                Resident
            </option>
            <option value="collector" <?php if(($role ?? '') == 'collector') echo 'selected'; ?>>
                Collector
            </option>
        </select>
    </div>
    <div class="invalid-feedback">Please select a role.</div>
</div>
                            <button type="submit" class="btn btn-green w-100 py-2 mb-3">
                                <i class="bi bi-person-plus me-2"></i>Create Account
                            </button>
                        </form>
                        <p class="text-center text-muted small mb-0">
                            Already have an account? 
                            <a href="/smart waste system/index.php" class="text-decoration-none fw-semibold text-green">Sign in</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/smart waste system/assets/js/main.js"></script>
</body>
</html>
