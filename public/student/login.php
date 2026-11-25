<?php
/**
 * Student Login Page
 * 
 * Implements FR-02: Authentication with email and password.
 * Secure password verification, session initialization, and redirect.
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Redirect if already logged in
redirectIfStudentLoggedIn();
redirectIfAdminLoggedIn();

$error = '';
$email = '';

// Handle login POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security token invalid or expired. Please submit the form again.';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $error = 'Please provide both your registered email and password.';
        } else {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("
                SELECT id, name, student_id, email, department, password 
                FROM students 
                WHERE email = :email 
                LIMIT 1
            ");
            $stmt->execute([':email' => $email]);
            $student = $stmt->fetch();

            if ($student && password_verify($password, $student['password'])) {
                // Successful student authentication
                session_regenerate_id(true); // Prevent session fixation

                $_SESSION['user_role']      = 'student';
                $_SESSION['student_id']     = (int)$student['id'];
                $_SESSION['student_name']   = $student['name'];
                $_SESSION['student_code']   = $student['student_id'];
                $_SESSION['student_email']  = $student['email'];
                $_SESSION['student_dept']   = $student['department'];

                setFlash('success', 'Welcome, ' . $student['name'] . '. You are now logged in.');
                header('Location: /student/dashboard.php');
                exit;
            } else {
                $error = 'Invalid email address or password. Please verify and try again.';
            }
        }
    }
}

$pageTitle = 'Student Sign In';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card card">
        <div class="auth-header">
            <h1 class="auth-title">Student Sign In</h1>
            <p class="auth-subtitle">Access your complaint dashboard and tracking history.</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error">
                <div class="alert-content">
                    <strong>Authentication failed:</strong> <?= e($error) ?>
                </div>
            </div>
        <?php endif; ?>

        <form action="/student/login.php" method="POST" class="form-stack">
            <?= csrfField() ?>

            <div class="form-group">
                <label for="email" class="form-label">Email Address <span class="required-mark">*</span></label>
                <input type="email" id="email" name="email" class="form-control" 
                       value="<?= e($email) ?>" placeholder="student@college.edu" required autocomplete="email" autofocus>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password <span class="required-mark">*</span></label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Enter your password" required autocomplete="current-password">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </div>
        </form>

        <div class="auth-footer">
            <span class="auth-note">Need an account?</span>
            <a href="/student/register.php" class="auth-link">Register here</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
