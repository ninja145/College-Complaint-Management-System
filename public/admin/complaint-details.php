<?php
/**
 * Administrator Complaint Details & Status Management Page
 * 
 * Implements FR-10:
 * - View student information and complaint description
 * - View uploaded supporting image
 * - Update complaint status (Pending, In Progress, Resolved)
 * - Save status changes to MySQL
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdminLogin();

$idParam = trim($_GET['id'] ?? '');

if ($idParam === '') {
    setFlash('error', 'No complaint tracking reference provided.');
    header('Location: /admin/complaints.php');
    exit;
}

$pdo = getDbConnection();

// Handle Status Update POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Security token expired. Please try again.');
    } else {
        $newStatus = trim($_POST['status'] ?? '');
        $allowedStatuses = ['Pending', 'In Progress', 'Resolved'];

        if (!in_array($newStatus, $allowedStatuses, true)) {
            setFlash('error', 'Invalid status selected.');
        } else {
            $updateStmt = $pdo->prepare("
                UPDATE complaints 
                SET status = :status 
                WHERE tracking_id = :tracking_id OR id = :id_num
            ");
            $updateStmt->execute([
                ':status'      => $newStatus,
                ':tracking_id' => $idParam,
                ':id_num'      => is_numeric($idParam) ? (int)$idParam : 0
            ]);

            setFlash('success', "Complaint status successfully updated to '{$newStatus}'.");
            header('Location: /admin/complaint-details.php?id=' . urlencode($idParam));
            exit;
        }
    }
}

// Fetch complete complaint details with student record
$stmt = $pdo->prepare("
    SELECT c.*, s.name AS student_name, s.student_id AS student_code, s.email, s.department
    FROM complaints c
    JOIN students s ON c.student_id = s.id
    WHERE c.tracking_id = :tracking_id OR c.id = :id_num
    LIMIT 1
");

$stmt->execute([
    ':tracking_id' => $idParam,
    ':id_num'      => is_numeric($idParam) ? (int)$idParam : 0
]);

$complaint = $stmt->fetch();

if (!$complaint) {
    setFlash('error', 'Complaint record not found in database.');
    header('Location: /admin/complaints.php');
    exit;
}

$pageTitle = 'Manage ' . $complaint['tracking_id'];
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="breadcrumb-bar">
    <a href="/admin/complaints.php" class="breadcrumb-link">&larr; Back to Complaints List</a>
</div>

<div class="page-header">
    <div class="page-header-info">
        <div class="tracking-meta-row">
            <span class="tracking-tag"><?= e($complaint['tracking_id']) ?></span>
            <span class="category-tag"><?= e($complaint['category']) ?></span>
            <?= renderStatusBadge($complaint['status']) ?>
        </div>
        <h1 class="page-title"><?= e($complaint['title']) ?></h1>
        <p class="page-meta">Submitted by <?= e($complaint['student_name']) ?> on <?= formatDate($complaint['created_at']) ?></p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/complaints.php" class="btn btn-secondary">Back to List</a>
    </div>
</div>

<div class="details-layout">
    <!-- Main Complaint Details -->
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

        <!-- Attached Image Evidence -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Supporting Evidence / Attachment</h2>
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
                                Open Full Size Image &nearr;
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No supporting image was attached by the student for this grievance.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Administrative Action & Info Sidebar -->
    <div class="details-sidebar">
        <!-- Status Management Action Card -->
        <div class="card card-action">
            <div class="card-header">
                <h3 class="card-title">Update Complaint Status</h3>
                <p class="card-subtitle">Select new status and save changes.</p>
            </div>
            <div class="card-body">
                <form action="/admin/complaint-details.php?id=<?= urlencode($complaint['tracking_id']) ?>" method="POST" class="form-stack">
                    <?= csrfField() ?>

                    <div class="form-group">
                        <label for="status" class="form-label">Grievance Status</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="Pending" <?= $complaint['status'] === 'Pending' ? 'selected' : '' ?>>Pending (Awaiting action)</option>
                            <option value="In Progress" <?= $complaint['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress (Under active inquiry)</option>
                            <option value="Resolved" <?= $complaint['status'] === 'Resolved' ? 'selected' : '' ?>>Resolved (Issue addressed & closed)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Save Status Change</button>
                </form>
            </div>
        </div>

        <!-- Student Information Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Student Information</h3>
            </div>
            <div class="card-body">
                <div class="meta-list">
                    <div class="meta-item">
                        <span class="meta-key">Student Name</span>
                        <span class="meta-value"><?= e($complaint['student_name']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-key">Student ID</span>
                        <span class="meta-value mono-code"><?= e($complaint['student_code']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-key">Email Address</span>
                        <span class="meta-value"><?= e($complaint['email']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-key">Department</span>
                        <span class="meta-value"><?= e($complaint['department']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-key">Submission Date</span>
                        <span class="meta-value"><?= formatDate($complaint['created_at']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
