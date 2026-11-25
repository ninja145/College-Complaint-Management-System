<?php
/**
 * Authentication and Session Management
 * 
 * Handles user login status, role verification (Student / Admin),
 * and access control for protected routes.
 * Beginner-friendly and strictly secure.
 */

// Start session securely if not already active
if (session_status() === PHP_SESSION_NONE) {
    // Configure secure session cookie settings
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

/**
 * Check if a student is currently logged in.
 */
function isStudentLoggedIn(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student' && !empty($_SESSION['student_id']);
}

/**
 * Check if an administrator is currently logged in.
 */
function isAdminLoggedIn(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin' && !empty($_SESSION['admin_id']);
}

/**
 * Require student authentication. Redirects to student login if not authenticated.
 */
function requireStudentLogin(): void {
    if (!isStudentLoggedIn()) {
        $_SESSION['flash'] = [
            'type'    => 'error',
            'message' => 'Please log in to access the student portal.'
        ];
        header('Location: /student/login.php');
        exit;
    }
}

/**
 * Require administrator authentication. Redirects to admin login if not authenticated.
 */
function requireAdminLogin(): void {
    if (!isAdminLoggedIn()) {
        $_SESSION['flash'] = [
            'type'    => 'error',
            'message' => 'Please log in with administrator credentials.'
        ];
        header('Location: /admin/login.php');
        exit;
    }
}

/**
 * Redirect student to dashboard if already logged in (used on login/register pages).
 */
function redirectIfStudentLoggedIn(): void {
    if (isStudentLoggedIn()) {
        header('Location: /student/dashboard.php');
        exit;
    }
}

/**
 * Redirect admin to dashboard if already logged in (used on admin login page).
 */
function redirectIfAdminLoggedIn(): void {
    if (isAdminLoggedIn()) {
        header('Location: /admin/dashboard.php');
        exit;
    }
}

/**
 * Returns current logged-in student details array.
 */
function getLoggedInStudent(): ?array {
    if (!isStudentLoggedIn()) {
        return null;
    }
    return [
        'id'         => $_SESSION['student_id'],
        'name'       => $_SESSION['student_name'] ?? '',
        'student_id' => $_SESSION['student_code'] ?? '',
        'email'      => $_SESSION['student_email'] ?? '',
        'department' => $_SESSION['student_dept'] ?? ''
    ];
}

/**
 * Returns current logged-in admin details array.
 */
function getLoggedInAdmin(): ?array {
    if (!isAdminLoggedIn()) {
        return null;
    }
    return [
        'id'       => $_SESSION['admin_id'],
        'username' => $_SESSION['admin_username'] ?? 'Administrator'
    ];
}
