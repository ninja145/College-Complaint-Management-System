<?php
/**
 * Administrator Login Page
 * 
 * Implements FR-08: Separate secure login for college administrative staff.
 * Default credentials seeded in database: admin / admin123
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Redirect if already logged in
redirectIfAdminLoggedIn();
redirectIfStudentLoggedIn();

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security token invalid or expired. Please submit the form again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Please enter both administrator username and password.';
        } else {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("SELECT id, username, password FROM admins WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                session_regenerate_id(true);

                $_SESSION['user_role']      = 'admin';
                $_SESSION['admin_id']       = (int)$admin['id'];
                $_SESSION['admin_username'] = $admin['username'];

                setFlash('success', 'Logged in successfully as Administrator.');
                header('Location: /admin/dashboard.php');
                exit;
            } else {
                $error = 'Invalid administrator credentials. Access denied.';
            }
        }
    }
}

$pageTitle = 'Administrator Access';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card card auth-card-admin">
        <div class="auth-header">
            <span class="auth-role-badge">Administrative Portal</span>
            <h1 class="auth-title">Administrative Sign In</h1>
            <p class="auth-subtitle">Restricted to authorized college personnel.</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error">
                <div class="alert-content">
                    <strong>Authentication failed:</strong> <?= e($error) ?>
                </div>
            </div>
        <?php endif; ?>

        <form action="/admin/login.php" method="POST" class="form-stack">
            <?= csrfField() ?>

            <div class="form-group">
                <label for="username" class="form-label">Administrator Username <span class="required-mark">*</span></label>
                <input type="text" id="username" name="username" class="form-control" 
                       value="<?= e($username) ?>" placeholder="e.g. admin" required autocomplete="username" autofocus>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password <span class="required-mark">*</span></label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Enter administrative password" required autocomplete="current-password">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-block">Sign In to Dashboard</button>
            </div>
        </form>

        <div class="auth-footer">
            <span class="auth-note">Default demo credentials:</span>
            <span class="mono-code">admin</span> / <span class="mono-code">admin123</span>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
