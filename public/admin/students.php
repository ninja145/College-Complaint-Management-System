<?php
/**
 * Administrator View Students Page
 * 
 * Implements FR-11: Read-only listing of all registered students:
 * - Student Name
 * - Student ID
 * - Email Address
 * - Department
 * - Registration Date
 * - Total complaints submitted
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdminLogin();
$pdo = getDbConnection();

// Search handling
$search = trim($_GET['search'] ?? '');

$sql = "
    SELECT 
        s.id, s.name, s.student_id, s.email, s.department, s.created_at,
        COUNT(c.id) AS total_complaints
    FROM students s
    LEFT JOIN complaints c ON s.id = c.student_id
    WHERE 1=1
";
$params = [];

if ($search !== '') {
    $sql .= " AND (s.name LIKE :search OR s.student_id LIKE :search OR s.email LIKE :search OR s.department LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

$sql .= " GROUP BY s.id ORDER BY s.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

$totalStudents = count($students);

$pageTitle = 'Registered Students';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-info">
        <h1 class="page-title">Registered Students</h1>
        <p class="page-meta">Read-only institutional directory of student accounts.</p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/dashboard.php" class="btn btn-secondary">&larr; Back to Dashboard</a>
    </div>
</div>

<!-- Search Bar -->
<div class="card card-filter">
    <form action="/admin/students.php" method="GET" class="filter-form">
        <div class="filter-group" style="flex: 1;">
            <label for="search" class="filter-label">Search Students:</label>
            <input type="text" id="search" name="search" class="form-control" 
                   value="<?= e($search) ?>" placeholder="Search by name, student ID, department, email...">
        </div>
        <button type="submit" class="btn btn-primary">Search</button>
        <?php if ($search !== ''): ?>
            <a href="/admin/students.php" class="btn btn-secondary">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="results-bar">
    <span class="results-count">Showing <strong><?= $totalStudents ?></strong> registered student<?= $totalStudents === 1 ? '' : 's' ?></span>
</div>

<!-- Students Directory Table -->
<div class="card">
    <div class="table-responsive">
        <?php if (empty($students)): ?>
            <div class="empty-state">
                <div class="empty-state-body">
                    <p class="empty-state-title">No students found</p>
                    <p class="empty-state-text">
                        <?= $search !== '' ? 'No student records matched your search query.' : 'No students have registered in the system yet.' ?>
                    </p>
                </div>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">#</th>
                        <th>Student Name</th>
                        <th style="width: 160px;">Student ID</th>
                        <th>Email Address</th>
                        <th style="width: 220px;">Department</th>
                        <th style="width: 160px;">Date Joined</th>
                        <th style="width: 140px; text-align: center;">Complaints Filed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $index => $s): ?>
                        <tr>
                            <td class="tabular-num text-muted"><?= $index + 1 ?></td>
                            <td>
                                <strong class="student-cell-name"><?= e($s['name']) ?></strong>
                            </td>
                            <td>
                                <code class="mono-code"><?= e($s['student_id']) ?></code>
                            </td>
                            <td>
                                <span class="email-text"><?= e($s['email']) ?></span>
                            </td>
                            <td>
                                <span class="department-tag"><?= e($s['department']) ?></span>
                            </td>
                            <td class="tabular-date">
                                <?= formatDate($s['created_at']) ?>
                            </td>
                            <td style="text-align: center;">
                                <a href="/admin/complaints.php?search=<?= urlencode($s['student_id']) ?>" class="count-pill">
                                    <?= (int)$s['total_complaints'] ?>
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
