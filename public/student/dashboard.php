<?php
/**
 * Student Dashboard Page
 * 
 * Implements FR-05: Overview of complaint metrics, quick actions,
 * and recent complaint history for the authenticated student.
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireStudentLogin();
$student = getLoggedInStudent();
$pdo = getDbConnection();

// Fetch complaint metrics for current student
$metricsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) AS total_count,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending_count,
        SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) AS progress_count,
        SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) AS resolved_count
    FROM complaints
    WHERE student_id = :student_id
");
$metricsStmt->execute([':student_id' => $student['id']]);
$metrics = $metricsStmt->fetch();

$totalCount    = (int)($metrics['total_count'] ?? 0);
$pendingCount  = (int)($metrics['pending_count'] ?? 0);
$progressCount = (int)($metrics['progress_count'] ?? 0);
$resolvedCount = (int)($metrics['resolved_count'] ?? 0);

// Fetch the 5 most recent complaints for this student
$recentStmt = $pdo->prepare("
    SELECT id, tracking_id, title, category, status, created_at
    FROM complaints
    WHERE student_id = :student_id
    ORDER BY id DESC
    LIMIT 5
");
$recentStmt->execute([':student_id' => $student['id']]);
$recentComplaints = $recentStmt->fetchAll();

$pageTitle = 'Student Dashboard';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-info">
        <h1 class="page-title">Student Dashboard</h1>
        <p class="page-meta">
            <?= e($student['name']) ?> &middot; Student ID: <span class="mono-code"><?= e($student['student_id']) ?></span> &middot; <?= e($student['department']) ?>
        </p>
    </div>
    <div class="page-header-actions">
        <a href="/student/submit-complaint.php" class="btn btn-primary">Submit Complaint</a>
        <a href="/student/my-complaints.php" class="btn btn-secondary">My Complaints</a>
    </div>
</div>

<!-- Complaint Metrics Grid (4 compact stat cards) -->
<div class="grid-4 stat-grid">
    <div class="card stat-card">
        <span class="stat-label">Total Complaints</span>
        <span class="stat-figure"><?= $totalCount ?></span>
        <span class="stat-sub">Lifetime registered</span>
    </div>

    <div class="card stat-card">
        <span class="stat-label">Pending</span>
        <span class="stat-figure stat-figure-pending"><?= $pendingCount ?></span>
        <span class="stat-sub">Awaiting review</span>
    </div>

    <div class="card stat-card">
        <span class="stat-label">In Progress</span>
        <span class="stat-figure stat-figure-progress"><?= $progressCount ?></span>
        <span class="stat-sub">Under active inquiry</span>
    </div>

    <div class="card stat-card">
        <span class="stat-label">Resolved</span>
        <span class="stat-figure stat-figure-resolved"><?= $resolvedCount ?></span>
        <span class="stat-sub">Action completed</span>
    </div>
</div>

<!-- Recent Complaints Section -->
<div class="card">
    <div class="card-header card-header-split">
        <div>
            <h2 class="card-title">Recent Complaints</h2>
            <p class="card-subtitle">Last 5 submissions by your account</p>
        </div>
        <?php if ($totalCount > 5): ?>
            <a href="/student/my-complaints.php" class="text-link">View All (<?= $totalCount ?>) &rarr;</a>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <?php if (empty($recentComplaints)): ?>
            <div class="empty-state">
                <div class="empty-state-body">
                    <p class="empty-state-title">No complaints submitted yet</p>
                    <p class="empty-state-text">You have not submitted any grievance records. If you are experiencing issues on campus, you can submit an official complaint.</p>
                    <a href="/student/submit-complaint.php" class="btn btn-primary">Submit First Complaint</a>
                </div>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 170px;">Tracking ID</th>
                        <th>Complaint Title</th>
                        <th style="width: 180px;">Category</th>
                        <th style="width: 170px;">Submitted On</th>
                        <th style="width: 130px;">Status</th>
                        <th style="width: 100px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentComplaints as $complaint): ?>
                        <tr>
                            <td>
                                <code class="mono-code"><?= e($complaint['tracking_id']) ?></code>
                            </td>
                            <td>
                                <a href="/student/complaint-details.php?id=<?= urlencode($complaint['tracking_id']) ?>" class="table-item-title">
                                    <?= e($complaint['title']) ?>
                                </a>
                            </td>
                            <td>
                                <span class="category-tag"><?= e($complaint['category']) ?></span>
                            </td>
                            <td class="tabular-date">
                                <?= formatDate($complaint['created_at']) ?>
                            </td>
                            <td>
                                <?= renderStatusBadge($complaint['status']) ?>
                            </td>
                            <td style="text-align: right;">
                                <a href="/student/complaint-details.php?id=<?= urlencode($complaint['tracking_id']) ?>" class="btn-action">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
