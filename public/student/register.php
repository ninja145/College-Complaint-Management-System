<?php
/**
 * Student Registration Page
 * 
 * Implements FR-01: Full Name, Student ID, Email, Department, Password.
 * Validates uniqueness, hashes password, and creates account.
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Redirect if already logged in
redirectIfStudentLoggedIn();
redirectIfAdminLoggedIn();

$errors = [];
$name = '';
$student_id = '';
$email = '';
$department = '';

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token invalid or expired. Please submit the form again.';
    } else {
        // 2. Collect and sanitize form inputs
        $name             = trim($_POST['name'] ?? '');
        $student_id       = trim($_POST['student_id'] ?? '');
        $email            = trim($_POST['email'] ?? '');
        $department       = trim($_POST['department'] ?? '');
        $password         = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // 3. Validation checks
        if ($name === '') {
            $errors[] = 'Full Name is required.';
        } elseif (strlen($name) < 3 || strlen($name) > 100) {
            $errors[] = 'Full Name must be between 3 and 100 characters.';
        }

        if ($student_id === '') {
            $errors[] = 'Student ID is required.';
        } elseif (!preg_match('/^[A-Za-z0-9\-\/]+$/', $student_id)) {
            $errors[] = 'Student ID can only contain letters, numbers, hyphens, and slashes.';
        }

        if ($email === '') {
            $errors[] = 'Email address is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }

        if ($department === '') {
            $errors[] = 'Department is required.';
        }

        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters in length.';
        } elseif ($password !== $confirm_password) {
            $errors[] = 'Password and Confirm Password do not match.';
        }

        // 4. Database uniqueness check & registration insertion
        if (empty($errors)) {
            $pdo = getDbConnection();

            // Check if student_id or email already exists
            $checkStmt = $pdo->prepare("
                SELECT id, student_id, email FROM students 
                WHERE student_id = :student_id OR email = :email 
                LIMIT 1
            ");
            $checkStmt->execute([
                ':student_id' => $student_id,
                ':email'      => $email
            ]);
            $existing = $checkStmt->fetch();

            if ($existing) {
                if (strcasecmp($existing['student_id'], $student_id) === 0) {
                    $errors[] = 'A student with this Student ID is already registered.';
                }
                if (strcasecmp($existing['email'], $email) === 0) {
                    $errors[] = 'A student with this Email address is already registered.';
                }
            } else {
                // Securely hash password using PHP standard bcrypt
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                $insertStmt = $pdo->prepare("
                    INSERT INTO students (name, student_id, email, department, password) 
                    VALUES (:name, :student_id, :email, :department, :password)
                ");

                try {
                    $insertStmt->execute([
                        ':name'       => $name,
                        ':student_id' => $student_id,
                        ':email'      => $email,
                        ':department' => $department,
                        ':password'   => $hashedPassword
                    ]);

                    setFlash('success', 'Registration successful! You may now log in with your credentials.');
                    header('Location: /student/login.php');
                    exit;
                } catch (PDOException $e) {
                    error_log('Student Registration Error: ' . $e->getMessage());
                    $errors[] = 'A database error occurred while registering. Please try again.';
                }
            }
        }
    }
}

$pageTitle = 'Student Registration';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card card">
        <div class="auth-header">
            <h1 class="auth-title">Student Registration</h1>
            <p class="auth-subtitle">Create your academic grievance portal account.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <div class="alert-content">
                    <strong>Please correct the following errors:</strong>
                    <ul class="error-list">
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <form action="/student/register.php" method="POST" class="form-stack">
            <?= csrfField() ?>

            <div class="form-group">
                <label for="name" class="form-label">Full Name <span class="required-mark">*</span></label>
                <input type="text" id="name" name="name" class="form-control" 
                       value="<?= e($name) ?>" placeholder="e.g. Rahul Sharma" required autocomplete="name">
            </div>

            <div class="form-row">
                <div class="form-group col-6">
                    <label for="student_id" class="form-label">Student ID <span class="required-mark">*</span></label>
                    <input type="text" id="student_id" name="student_id" class="form-control" 
                           value="<?= e($student_id) ?>" placeholder="e.g. BCA-2024-042" required>
                </div>

                <div class="form-group col-6">
                    <label for="department" class="form-label">Department <span class="required-mark">*</span></label>
                    <select id="department" name="department" class="form-control" required>
                        <option value="">Select Department</option>
                        <option value="Computer Applications (BCA)" <?= $department === 'Computer Applications (BCA)' ? 'selected' : '' ?>>Computer Applications (BCA)</option>
                        <option value="Computer Science (B.Sc CS)" <?= $department === 'Computer Science (B.Sc CS)' ? 'selected' : '' ?>>Computer Science (B.Sc CS)</option>
                        <option value="Information Technology (IT)" <?= $department === 'Information Technology (IT)' ? 'selected' : '' ?>>Information Technology (IT)</option>
                        <option value="Management Studies (BBA)" <?= $department === 'Management Studies (BBA)' ? 'selected' : '' ?>>Management Studies (BBA)</option>
                        <option value="Commerce (B.Com)" <?= $department === 'Commerce (B.Com)' ? 'selected' : '' ?>>Commerce (B.Com)</option>
                        <option value="Science (B.Sc)" <?= $department === 'Science (B.Sc)' ? 'selected' : '' ?>>Science (B.Sc)</option>
                        <option value="Arts & Humanities (BA)" <?= $department === 'Arts & Humanities (BA)' ? 'selected' : '' ?>>Arts & Humanities (BA)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address <span class="required-mark">*</span></label>
                <input type="email" id="email" name="email" class="form-control" 
                       value="<?= e($email) ?>" placeholder="student@college.edu" required autocomplete="email">
            </div>

            <div class="form-row">
                <div class="form-group col-6">
                    <label for="password" class="form-label">Password <span class="required-mark">*</span></label>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="At least 6 characters" required autocomplete="new-password">
                </div>

                <div class="form-group col-6">
                    <label for="confirm_password" class="form-label">Confirm Password <span class="required-mark">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                           placeholder="Re-enter password" required autocomplete="new-password">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-block">Complete Registration</button>
            </div>
        </form>

        <div class="auth-footer">
            <span class="auth-note">Already have an account?</span>
            <a href="/student/login.php" class="auth-link">Sign In here</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
