<?php
/**
 * Student Logout Script
 * 
 * Clears student session variables and redirects cleanly.
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Unset student session values
unset(
    $_SESSION['user_role'],
    $_SESSION['student_id'],
    $_SESSION['student_name'],
    $_SESSION['student_code'],
    $_SESSION['student_email'],
    $_SESSION['student_dept']
);

setFlash('info', 'You have been logged out successfully.');
header('Location: /student/login.php');
exit;
