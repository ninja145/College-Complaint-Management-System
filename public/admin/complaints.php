<?php
/**
 * Administrator All Complaints Page
 * 
 * Implements FR-10:
 * - View all submitted complaints
 * - Search by Tracking ID or Student Name or Student ID
 * - Filter by status and/or category
 * - Access complaint details and status update
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdminLogin();
$pdo = getDbConnection();

// Filter & search parameters
$search   = trim($_GET['search'] ?? '');
$status   = trim($_GET['status'] ?? '');
$category = trim($_GET['category'] ?? '');

$allowedStatuses = ['Pending', 'In Progress', 'Resolved'];
$categories = getComplaintCategories();

// Base query joining complaints with students table
$sql = "
    SELECT 
        c.id, c.tracking_id, c.title, c.category, c.status, c.created_at, c.attachment,
        s.name AS student_name, s.student_id AS student_code, s.email, s.department
    FROM complaints c
    JOIN students s ON c.student_id = s.id
    WHERE 1=1
";
$params = [];

// Apply Search
if ($search !== '') {
    $sql .= " AND (c.tracking_id LIKE :search OR c.title LIKE :search OR s.name LIKE :search OR s.student_id LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

// Apply Status Filter
if (in_array($status, $allowedStatuses, true)) {
    $sql .= " AND c.status = :status";
    $params[':status'] = $status;
}

// Apply Category Filter
if (in_array($category, $categories, true)) {
    $sql .= " AND c.category = :category";
    $params[':category'] = $category;
}

$sql .= " ORDER BY c.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$complaints = $stmt->fetchAll();

// Total count
$totalCount = count($complaints);

$pageTitle = 'All Complaints';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-info">
        <h1 class="page-title">Complaint Management</h1>
        <p class="page-meta">Search, filter, and inspect all student grievance records.</p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/dashboard.php" class="btn btn-secondary">&larr; Back to Dashboard</a>
    </div>
</div>

<!-- Search and Filter Form -->
<div class="card card-filter">
    <form action="/admin/complaints.php" method="GET" class="filter-form-grid">
        <div class="filter-col">
            <label for="search" class="filter-label">Search Keyword / ID</label>
            <input type="text" id="search" name="search" class="form-control" 
                   value="<?= e($search) ?>" placeholder="Tracking ID, student name, title...">
        </div>

        <div class="filter-col">
            <label for="status" class="filter-label">Status</label>
            <select name="status" id="status" class="form-control">
                <option value="">All Statuses</option>
                <option value="Pending" <?= $status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="In Progress" <?= $status === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="Resolved" <?= $status === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
            </select>
        </div>

        <div class="filter-col">
            <label for="category" class="filter-label">Category</label>
            <select name="category" id="category" class="form-control">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                        <?= e($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-actions-col">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($search !== '' || $status !== '' || $category !== ''): ?>
                <a href="/admin/complaints.php" class="btn btn-secondary">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Results Count -->
<div class="results-bar">
    <span class="results-count">Showing <strong><?= $totalCount ?></strong> complaint record<?= $totalCount === 1 ? '' : 's' ?></span>
</div>

<!-- Complaints Table -->
<div class="card">
    <div class="table-responsive">
        <?php if (empty($complaints)): ?>
            <div class="empty-state">
                <div class="empty-state-body">
                    <p class="empty-state-title">No complaints match your criteria</p>
                    <p class="empty-state-text">No records were found matching the selected search query or filters.</p>
                    <a href="/admin/complaints.php" class="btn btn-secondary">Clear Filters</a>
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
                        <th style="width: 160px;">Lodged On</th>
                        <th style="width: 130px;">Status</th>
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
                                    Manage
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
