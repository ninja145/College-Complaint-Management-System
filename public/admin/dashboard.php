<?php
/**
 * Administrator Dashboard Page
 * 
 * Implements FR-09: Displays system metrics:
 * - Total Registered Students
 * - Total Complaints
 * - Pending Complaints
 * - In Progress Complaints
 * - Resolved Complaints
 * And displays recently submitted complaints with administrative actions.
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdminLogin();
$admin = getLoggedInAdmin();
$pdo = getDbConnection();

// 1. Fetch total registered students
$studentCountStmt = $pdo->query("SELECT COUNT(*) FROM students");
$totalStudents = (int)$studentCountStmt->fetchColumn();

// 2. Fetch complaint counts
$complaintMetricsStmt = $pdo->query("
    SELECT 
        COUNT(*) AS total_complaints,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending_complaints,
        SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) AS progress_complaints,
        SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) AS resolved_complaints
    FROM complaints
");
$complaintMetrics = $complaintMetricsStmt->fetch();

$totalComplaints    = (int)($complaintMetrics['total_complaints'] ?? 0);
$pendingComplaints  = (int)($complaintMetrics['pending_complaints'] ?? 0);
$progressComplaints = (int)($complaintMetrics['progress_complaints'] ?? 0);
$resolvedComplaints = (int)($complaintMetrics['resolved_complaints'] ?? 0);

// 3. Fetch 8 most recently submitted complaints with student info
$recentStmt = $pdo->query("
    SELECT 
        c.id, c.tracking_id, c.title, c.category, c.status, c.created_at, c.attachment,
        s.name AS student_name, s.student_id AS student_code, s.department
    FROM complaints c
    JOIN students s ON c.student_id = s.id
    ORDER BY c.id DESC
    LIMIT 8
");
$recentComplaints = $recentStmt->fetchAll();

$pageTitle = 'Administrator Dashboard';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-info">
        <h1 class="page-title">Administrator Dashboard</h1>
        <p class="page-meta">Logged in as <span class="mono-code"><?= e($admin['username']) ?></span> &middot; Academic Year <?= date('Y') ?></p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/complaints.php" class="btn btn-primary">Manage Complaints</a>
        <a href="/admin/students.php" class="btn btn-secondary">View Students</a>
    </div>
</div>

<!-- 5 Stat Cards Metric Grid -->
<div class="grid-5 stat-grid">
    <div class="card stat-card">
        <span class="stat-label">Students</span>
        <span class="stat-figure"><?= $totalStudents ?></span>
        <span class="stat-sub">Registered accounts</span>
    </div>

    <div class="card stat-card">
        <span class="stat-label">Total Grievances</span>
        <span class="stat-figure"><?= $totalComplaints ?></span>
        <span class="stat-sub">All submissions</span>
    </div>

    <div class="card stat-card">
        <span class="stat-label">Pending</span>
        <span class="stat-figure stat-figure-pending"><?= $pendingComplaints ?></span>
        <span class="stat-sub">Needs attention</span>
    </div>

    <div class="card stat-card">
        <span class="stat-label">In Progress</span>
        <span class="stat-figure stat-figure-progress"><?= $progressComplaints ?></span>
        <span class="stat-sub">Under review</span>
    </div>

    <div class="card stat-card">
        <span class="stat-label">Resolved</span>
        <span class="stat-figure stat-figure-resolved"><?= $resolvedComplaints ?></span>
        <span class="stat-sub">Completed</span>
    </div>
</div>

<!-- Recent Submissions Overview -->
<div class="card">
    <div class="card-header card-header-split">
        <div>
            <h2 class="card-title">Recent Grievances</h2>
            <p class="card-subtitle">Latest complaints received across all campus departments</p>
        </div>
        <a href="/admin/complaints.php" class="text-link">View All Complaints (<?= $totalComplaints ?>) &rarr;</a>
    </div>

    <div class="table-responsive">
        <?php if (empty($recentComplaints)): ?>
            <div class="empty-state">
                <div class="empty-state-body">
                    <p class="empty-state-title">No complaints lodged yet</p>
                    <p class="empty-state-text">There are currently no complaints in the system. As students submit grievances, they will appear here for administrative action.</p>
                </div>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 160px;">Tracking ID</th>
                        <th>Student & Department</th>
                        <th>Complaint Title</th>
                        <th style="width: 170px;">Category</th>
                        <th style="width: 160px;">Date Lodged</th>
                        <th style="width: 130px;">Status</th>
                        <th style="width: 110px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentComplaints as $c): ?>
                        <tr>
                            <td>
                                <code class="mono-code"><?= e($c['tracking_id']) ?></code>
                            </td>
                            <td>
                                <div class="student-meta-cell">
                                    <strong class="student-cell-name"><?= e($c['student_name']) ?></strong>
                                    <span class="student-cell-sub"><span class="mono-code"><?= e($c['student_code']) ?></span> &middot; <?= e($c['department']) ?></span>
                                </div>
                            </td>
                            <td>
                                <a href="/admin/complaint-details.php?id=<?= urlencode($c['tracking_id']) ?>" class="table-item-title">
                                    <?= e($c['title']) ?>
                                </a>
                                <?php if (!empty($c['attachment'])): ?>
                                    <span class="attachment-indicator" title="Supporting image attached">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/>
                                        </svg>
                                        Image
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="category-tag"><?= e($c['category']) ?></span>
                            </td>
                            <td class="tabular-date">
                                <?= formatDate($c['created_at']) ?>
                            </td>
                            <td>
                                <?= renderStatusBadge($c['status']) ?>
                            </td>
                            <td style="text-align: right;">
                                <a href="/admin/complaint-details.php?id=<?= urlencode($c['tracking_id']) ?>" class="btn-action">
                                    Review
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
