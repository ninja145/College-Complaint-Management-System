<?php
/**
 * Student Complaint Details Page
 * 
 * Implements FR-05, FR-06, FR-07: Detailed view of a single complaint.
 * Shows Tracking ID, Category, Title, Description, Image Attachment, Status.
 * Security: Strict student ID ownership validation!
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireStudentLogin();
$student = getLoggedInStudent();

$idParam = trim($_GET['id'] ?? '');

if ($idParam === '') {
    setFlash('error', 'No complaint specified.');
    header('Location: /student/my-complaints.php');
    exit;
}

$pdo = getDbConnection();

// Fetch complaint verifying ownership: student_id must match logged in student
$stmt = $pdo->prepare("
    SELECT c.*, s.name AS student_name, s.student_id AS student_code, s.email, s.department
    FROM complaints c
    JOIN students s ON c.student_id = s.id
    WHERE (c.tracking_id = :tracking_id OR c.id = :id_num) 
      AND c.student_id = :student_id
    LIMIT 1
");

$stmt->execute([
    ':tracking_id' => $idParam,
    ':id_num'      => is_numeric($idParam) ? (int)$idParam : 0,
    ':student_id'  => $student['id']
]);

$complaint = $stmt->fetch();

// Security check: if not found or unauthorized
if (!$complaint) {
    setFlash('error', 'Complaint not found or you do not have permission to view this record.');
    header('Location: /student/my-complaints.php');
    exit;
}

$pageTitle = 'Complaint ' . $complaint['tracking_id'];
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="breadcrumb-bar">
    <a href="/student/my-complaints.php" class="breadcrumb-link">&larr; Back to My Complaints</a>
</div>

<div class="page-header">
    <div class="page-header-info">
        <div class="tracking-meta-row">
            <span class="tracking-tag"><?= e($complaint['tracking_id']) ?></span>
            <span class="category-tag"><?= e($complaint['category']) ?></span>
            <?= renderStatusBadge($complaint['status']) ?>
        </div>
        <h1 class="page-title"><?= e($complaint['title']) ?></h1>
        <p class="page-meta">Submitted on <?= formatDate($complaint['created_at']) ?></p>
    </div>
    <div class="page-header-actions">
        <a href="/student/submit-complaint.php" class="btn btn-secondary">Submit Another Complaint</a>
    </div>
</div>

<div class="details-layout">
    <!-- Main Complaint Details Content -->
    <div class="details-main">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Complaint Description</h2>
            </div>
            <div class="card-body">
                <div class="complaint-text-body">
                    <?= nl2br(e($complaint['description'])) ?>
                </div>
            </div>
        </div>

        <!-- Supporting Evidence / Attachment -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Supporting Evidence</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($complaint['attachment'])): ?>
                    <div class="attachment-preview-box">
                        <div class="attachment-image-wrap">
                            <img src="/<?= e($complaint['attachment']) ?>" 
                                 alt="Supporting evidence for complaint <?= e($complaint['tracking_id']) ?>" 
                                 class="evidence-image">
                        </div>
                        <div class="attachment-meta">
                            <span class="attachment-filename"><?= e(basename($complaint['attachment'])) ?></span>
                            <a href="/<?= e($complaint['attachment']) ?>" target="_blank" class="btn btn-secondary btn-sm">
                                View Full Image &nearr;
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No supporting image or photograph was attached to this complaint.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Status & Tracking Sidebar -->
    <div class="details-sidebar">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Current Resolution Status</h3>
            </div>
            <div class="card-body">
                <div class="status-summary-box">
                    <?= renderStatusBadge($complaint['status']) ?>
                    
                    <div class="status-explanation">
                        <?php if ($complaint['status'] === 'Pending'): ?>
                            <strong>Pending Review:</strong>
                            <p>Your complaint has been logged and assigned to the administration queue. An official will examine the issue shortly.</p>
                        <?php elseif ($complaint['status'] === 'In Progress'): ?>
                            <strong>Active Inquiry:</strong>
                            <p>The college administration is currently investigating or coordinating corrective action for this complaint.</p>
                        <?php elseif ($complaint['status'] === 'Resolved'): ?>
                            <strong>Resolved & Concluded:</strong>
                            <p>Administrative action has been completed regarding this matter. If you experience further issues, please file a new report.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <hr class="divider">

                <div class="meta-list">
                    <div class="meta-item">
                        <span class="meta-key">Tracking ID</span>
                        <span class="meta-value mono-code"><?= e($complaint['tracking_id']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-key">Submission Date</span>
                        <span class="meta-value"><?= formatDate($complaint['created_at']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-key">Category</span>
                        <span class="meta-value"><?= e($complaint['category']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-key">Student Name</span>
                        <span class="meta-value"><?= e($complaint['student_name']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-key">Student ID</span>
                        <span class="meta-value"><?= e($complaint['student_code']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-key">Department</span>
                        <span class="meta-value"><?= e($complaint['department']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
