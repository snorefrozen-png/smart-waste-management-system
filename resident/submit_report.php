<?php
/**
 * Submit Waste Report
 * Residents use this form to submit a new waste report with
 * description, location, and optional image upload.
 */
require_once __DIR__ . '/../includes/auth_check.php';
requireRole('resident');
require_once __DIR__ . '/../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');
    $location    = trim($_POST['location'] ?? '');
    $imageName   = null;

    // Validate input
    if (empty($description) || strlen($description) < 10) {
        $error = 'Description must be at least 10 characters.';
    } elseif (empty($location)) {
        $error = 'Location is required.';
    } else {
        // Handle image upload (optional)
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = $_FILES['image']['type'];
            $fileSize = $_FILES['image']['size'];

            if (!in_array($fileType, $allowedTypes)) {
                $error = 'Invalid image type. Allowed: JPG, PNG, GIF, WEBP.';
            } elseif ($fileSize > 5 * 1024 * 1024) { // 5MB limit
                $error = 'Image size must not exceed 5MB.';
            } else {
                // Generate unique filename
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $imageName = 'report_' . time() . '_' . uniqid() . '.' . $ext;
                $uploadDir = __DIR__ . '/../uploads/';
                
                // Create uploads directory if it doesn't exist
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName)) {
                    $error = 'Failed to upload image.';
                    $imageName = null;
                }
            }
        }

        // Insert report if no errors
        if (empty($error)) {
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("INSERT INTO reports (user_id, description, location, image, status) 
                                       VALUES (:uid, :desc, :loc, :img, 'pending')");
                $stmt->execute([
                    ':uid'  => getCurrentUserId(),
                    ':desc' => $description,
                    ':loc'  => $location,
                    ':img'  => $imageName,
                ]);
                $success = 'Report submitted successfully! It is now pending review.';
            } catch (PDOException $e) {
                $error = 'Failed to submit report. Please try again.';
            }
        }
    }
}

$pageTitle = 'Submit Report';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="page-header">
        <h2><i class="bi bi-plus-circle me-2 text-green"></i>Submit Waste Report</h2>
        <p>Report waste issues in your area for collection and cleanup.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="form-card">
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade-in" role="alert">
                        <i class="bi bi-check-circle me-2"></i><?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade-in" role="alert">
                        <i class="bi bi-x-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form id="reportForm" action="" method="POST" enctype="multipart/form-data">
                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">
                            <i class="bi bi-pencil me-1"></i>Description <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="description" name="description" rows="4" 
                                  placeholder="Describe the waste issue in detail (e.g., overflowing bin, illegal dumping, uncollected garbage...)" 
                                  required minlength="10"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                        <div class="invalid-feedback">Please provide a detailed description (at least 10 characters).</div>
                        <div class="form-text">Minimum 10 characters. Be specific about the waste issue.</div>
                    </div>

                    <!-- Location -->
                    <div class="mb-3">
                        <label for="location" class="form-label">
                            <i class="bi bi-geo-alt me-1"></i>Location <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="location" name="location" required>
                            <option value="">-- Select Location --</option>
                            <option value="CBD" <?php echo ($location ?? '') === 'CBD' ? 'selected' : ''; ?>>CBD (Central Business District)</option>
                            <option value="Westlands" <?php echo ($location ?? '') === 'Westlands' ? 'selected' : ''; ?>>Westlands</option>
                            <option value="Eastleigh" <?php echo ($location ?? '') === 'Eastleigh' ? 'selected' : ''; ?>>Eastleigh</option>
                            <option value="Kibera" <?php echo ($location ?? '') === 'Kibera' ? 'selected' : ''; ?>>Kibera</option>
                            <option value="Langata" <?php echo ($location ?? '') === 'Langata' ? 'selected' : ''; ?>>Langata</option>
                            <option value="Karen" <?php echo ($location ?? '') === 'Karen' ? 'selected' : ''; ?>>Karen</option>
                            <option value="Kasarani" <?php echo ($location ?? '') === 'Kasarani' ? 'selected' : ''; ?>>Kasarani</option>
                            <option value="Embakasi" <?php echo ($location ?? '') === 'Embakasi' ? 'selected' : ''; ?>>Embakasi</option>
                            <option value="Dagoretti" <?php echo ($location ?? '') === 'Dagoretti' ? 'selected' : ''; ?>>Dagoretti</option>
                            <option value="Starehe" <?php echo ($location ?? '') === 'Starehe' ? 'selected' : ''; ?>>Starehe</option>
                        </select>
                        <div class="invalid-feedback">Please select a location.</div>
                    </div>

                    <!-- Image Upload (Optional) -->
                    <div class="mb-4">
                        <label for="image" class="form-label">
                            <i class="bi bi-camera me-1"></i>Photo Evidence <span class="text-muted">(optional)</span>
                        </label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <div class="invalid-feedback">Please upload a valid image (JPG, PNG, GIF, WEBP).</div>
                        <div class="form-text">Max file size: 5MB. Accepted formats: JPG, PNG, GIF, WEBP.</div>
                        <!-- Image Preview -->
                        <img id="imagePreview" class="mt-2 rounded" style="display:none; max-height:200px;" alt="Preview">
                    </div>

                    <!-- Submit -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-green">
                            <i class="bi bi-send me-2"></i>Submit Report
                        </button>
                        <a href="/GABU/resident/dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
