<?php
/**
 * Submit Complaint Page
 * 
 * Implements FR-03 & FR-04: Form to submit a complaint with category selection,
 * description, optional supporting image upload (max 2MB, JPG/PNG),
 * and automatic sequential Tracking ID generation.
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireStudentLogin();
$student = getLoggedInStudent();

$errors = [];
$title = '';
$category = '';
$description = '';
$categories = getComplaintCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token invalid or expired. Please submit the form again.';
    } else {
        // 2. Collect and validate text inputs
        $title       = trim($_POST['title'] ?? '');
        $category    = trim($_POST['category'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($title === '') {
            $errors[] = 'Complaint Title is required.';
        } elseif (strlen($title) < 5 || strlen($title) > 200) {
            $errors[] = 'Complaint Title must be between 5 and 200 characters.';
        }

        if ($category === '') {
            $errors[] = 'Please select a valid Complaint Category.';
        } elseif (!in_array($category, $categories, true)) {
            $errors[] = 'Selected category is not in the recognized categories list.';
        }

        if ($description === '') {
            $errors[] = 'Complaint Description is required.';
        } elseif (strlen($description) < 15) {
            $errors[] = 'Please provide a detailed description (at least 15 characters).';
        }

        // 3. Handle image upload if provided (Optional)
        $attachmentPath = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = handleImageUpload($_FILES['attachment']);
            if (!$uploadResult['success']) {
                $errors[] = $uploadResult['error'];
            } else {
                $attachmentPath = $uploadResult['filename'];
            }
        }

        // 4. Save to database if validation passes
        if (empty($errors)) {
            $pdo = getDbConnection();

            try {
                // Generate unique sequential tracking ID (e.g. CMP-2026-0001)
                $trackingId = generateTrackingId($pdo);

                $insertStmt = $pdo->prepare("
                    INSERT INTO complaints 
                        (student_id, tracking_id, title, category, description, attachment, status)
                    VALUES 
                        (:student_id, :tracking_id, :title, :category, :description, :attachment, 'Pending')
                ");

                $insertStmt->execute([
                    ':student_id'  => $student['id'],
                    ':tracking_id' => $trackingId,
                    ':title'       => $title,
                    ':category'    => $category,
                    ':description' => $description,
                    ':attachment'  => $attachmentPath
                ]);

                setFlash('success', "Complaint submitted successfully! Your tracking reference is {$trackingId}.");
                header('Location: /student/my-complaints.php');
                exit;

            } catch (PDOException $e) {
                error_log('Complaint Submission Error: ' . $e->getMessage());
                $errors[] = 'A database error occurred while registering your complaint. Please try again.';
            }
        }
    }
}

$pageTitle = 'Submit Complaint';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-info">
        <h1 class="page-title">Submit New Complaint</h1>
        <p class="page-meta">Complete the form below to lodge an official academic or institutional grievance.</p>
    </div>
    <div class="page-header-actions">
        <a href="/student/my-complaints.php" class="btn btn-secondary">View My Complaints</a>
    </div>
</div>

<div class="form-container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Grievance Submission Form</h2>
            <p class="card-subtitle">All mandatory fields are marked with an asterisk (*)</p>
        </div>

        <div class="card-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <div class="alert-content">
                        <strong>Please resolve the following issues:</strong>
                        <ul class="error-list">
                            <?php foreach ($errors as $err): ?>
                                <li><?= e($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <form action="/student/submit-complaint.php" method="POST" enctype="multipart/form-data" class="form-stack">
                <?= csrfField() ?>

                <div class="form-group">
                    <label for="title" class="form-label">Complaint Title <span class="required-mark">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" 
                           value="<?= e($title) ?>" placeholder="Brief summary of the issue (e.g. Projector malfunctioning in Room 304)" 
                           required maxlength="200" autofocus>
                </div>

                <div class="form-group">
                    <label for="category" class="form-label">Complaint Category <span class="required-mark">*</span></label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="">-- Select Relevant Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= ($category === $cat ? 'selected' : '') ?>>
                                <?= e($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Detailed Description <span class="required-mark">*</span></label>
                    <textarea id="description" name="description" class="form-control" rows="6" 
                              placeholder="Please provide full facts including date, location, equipment or department involved, and any steps already taken." 
                              required><?= e($description) ?></textarea>
                    <span class="form-help">Please be clear, specific, and objective to assist administrative evaluation.</span>
                </div>

                <div class="form-group">
                    <label for="attachment" class="form-label">Supporting Evidence / Image <span class="optional-mark">(Optional)</span></label>
                    <input type="file" id="attachment" name="attachment" class="form-control file-input" 
                           accept=".jpg,.jpeg,.png">
                    <span class="form-help">Accepted formats: JPG, JPEG, PNG. Maximum file size: 2 MB.</span>
                </div>

                <div class="form-actions-split">
                    <a href="/student/dashboard.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit Complaint</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
