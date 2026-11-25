<?php
/**
 * My Complaints Page
 * 
 * Implements FR-06: Student views list of all their previously submitted complaints.
 * Shows Tracking ID, Title, Category, Date, Status badge, and View action.
 * Strict access control: only shows records belonging to the authenticated student.
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireStudentLogin();
$student = getLoggedInStudent();
$pdo = getDbConnection();

// Optional filter by status
$statusFilter = trim($_GET['status'] ?? '');
$allowedStatuses = ['Pending', 'In Progress', 'Resolved'];

$sql = "
    SELECT id, tracking_id, title, category, status, created_at, attachment
    FROM complaints
    WHERE student_id = :student_id
";
$params = [':student_id' => $student['id']];

if (in_array($statusFilter, $allowedStatuses, true)) {
    $sql .= " AND status = :status";
    $params[':status'] = $statusFilter;
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$complaints = $stmt->fetchAll();

// Total count for student
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE student_id = :student_id");
$countStmt->execute([':student_id' => $student['id']]);
$totalOwned = (int)$countStmt->fetchColumn();

$pageTitle = 'My Complaints';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-info">
        <h1 class="page-title">My Complaints</h1>
        <p class="page-meta">Complete record of your submitted grievances and their administrative status.</p>
    </div>
    <div class="page-header-actions">
        <a href="/student/submit-complaint.php" class="btn btn-primary">Submit New Complaint</a>
    </div>
</div>

<!-- Filter controls -->
<div class="card card-filter">
    <form action="/student/my-complaints.php" method="GET" class="filter-form">
        <div class="filter-group">
            <label for="status" class="filter-label">Filter by Status:</label>
            <select name="status" id="status" class="form-control filter-select">
                <option value="">All Statuses (<?= $totalOwned ?>)</option>
                <option value="Pending" <?= $statusFilter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="In Progress" <?= $statusFilter === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="Resolved" <?= $statusFilter === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
            </select>
        </div>

        <button type="submit" class="btn btn-secondary btn-sm">Filter</button>

        <?php if ($statusFilter !== ''): ?>
            <a href="/student/my-complaints.php" class="text-link filter-clear">Clear filter</a>
        <?php endif; ?>
    </form>
</div>

<!-- Complaints Ledger Table -->
<div class="card">
    <div class="table-responsive">
        <?php if (empty($complaints)): ?>
            <div class="empty-state">
                <div class="empty-state-body">
                    <p class="empty-state-title">
                        <?= $statusFilter !== '' ? 'No complaints found with status "' . e($statusFilter) . '"' : 'No complaints recorded yet' ?>
                    </p>
                    <p class="empty-state-text">
                        <?= $statusFilter !== '' ? 'Try selecting "All Statuses" to view your other submissions.' : 'You have not submitted any complaints yet. When you submit a complaint, it will appear here with full tracking.' ?>
                    </p>
                    <a href="/student/submit-complaint.php" class="btn btn-primary">Submit a Complaint</a>
                </div>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 170px;">Tracking ID</th>
                        <th>Complaint Title</th>
                        <th style="width: 180px;">Category</th>
                        <th style="width: 170px;">Date Lodged</th>
                        <th style="width: 140px;">Status</th>
                        <th style="width: 110px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($complaints as $c): ?>
                        <tr>
                            <td>
                                <code class="mono-code"><?= e($c['tracking_id']) ?></code>
                            </td>
                            <td>
                                <a href="/student/complaint-details.php?id=<?= urlencode($c['tracking_id']) ?>" class="table-item-title">
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
                                <a href="/student/complaint-details.php?id=<?= urlencode($c['tracking_id']) ?>" class="btn-action">
                                    View
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
