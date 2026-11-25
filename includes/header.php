<?php
/**
 * Shared Header Template
 * 
 * Consistent institutional navigation, metadata, and flash messages.
 * Pure HTML5 & CSS3 - strictly NO JavaScript.
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$pageTitle = isset($pageTitle) ? $pageTitle . ' — College Grievance Portal' : 'College Grievance Management System';
$student = getLoggedInStudent();
$admin = getLoggedInAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="header-inner container">
            <div class="brand-block">
                <a href="/" class="brand-link">
                    <span class="brand-mark">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18"/>
                            <path d="M4 18h16"/>
                            <path d="M6 18v-7"/>
                            <path d="M10 18v-7"/>
                            <path d="M14 18v-7"/>
                            <path d="M18 18v-7"/>
                            <path d="M12 2l10 5H2l10-5z"/>
                        </svg>
                    </span>
                    <span class="brand-text">
                        <span class="brand-title">College Grievance Portal</span>
                        <span class="brand-subtitle">Complaint Management System</span>
                    </span>
                </a>
            </div>

            <nav class="site-nav" aria-label="Main Navigation">
                <?php if ($student): ?>
                    <!-- Student Navigation -->
                    <a href="/student/dashboard.php" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'dashboard') ? 'active' : '') ?>">Dashboard</a>
                    <a href="/student/submit-complaint.php" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'submit-complaint') ? 'active' : '') ?>">Submit Complaint</a>
                    <a href="/student/my-complaints.php" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'my-complaints') || str_contains($_SERVER['REQUEST_URI'], 'complaint-details') ? 'active' : '') ?>">My Complaints</a>
                    
                    <div class="nav-user-meta">
                        <span class="user-role-label">Student:</span>
                        <span class="user-name-label"><?= e($student['name']) ?></span>
                        <a href="/student/logout.php" class="nav-btn-outline">Log Out</a>
                    </div>

                <?php elseif ($admin): ?>
                    <!-- Admin Navigation -->
                    <a href="/admin/dashboard.php" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'dashboard') ? 'active' : '') ?>">Dashboard</a>
                    <a href="/admin/complaints.php" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'complaints') || str_contains($_SERVER['REQUEST_URI'], 'complaint-details') ? 'active' : '') ?>">Complaints</a>
                    <a href="/admin/students.php" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'students') ? 'active' : '') ?>">Students</a>
                    
                    <div class="nav-user-meta">
                        <span class="user-role-label admin-badge-tag">Admin:</span>
                        <span class="user-name-label"><?= e($admin['username']) ?></span>
                        <a href="/admin/logout.php" class="nav-btn-outline">Log Out</a>
                    </div>

                <?php else: ?>
                    <!-- Public Guest Navigation -->
                    <a href="/" class="nav-link <?= ($_SERVER['REQUEST_URI'] === '/' || str_ends_with($_SERVER['REQUEST_URI'], 'index.php') ? 'active' : '') ?>">Home</a>
                    <a href="/student/login.php" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], '/student/login') ? 'active' : '') ?>">Student Login</a>
                    <a href="/student/register.php" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], '/student/register') ? 'active' : '') ?>">Register</a>
                    <a href="/admin/login.php" class="nav-link nav-link-admin <?= (str_contains($_SERVER['REQUEST_URI'], '/admin/login') ? 'active' : '') ?>">Admin Portal</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="site-main">
        <div class="container main-content-container">
            <?= renderFlash() ?>
