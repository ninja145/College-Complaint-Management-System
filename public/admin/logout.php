<?php
/**
 * Administrator Logout Script
 * 
 * Clears administrator session variables and redirects to admin login.
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

unset(
    $_SESSION['user_role'],
    $_SESSION['admin_id'],
    $_SESSION['admin_username']
);

setFlash('info', 'Administrator session ended.');
header('Location: /admin/login.php');
exit;
