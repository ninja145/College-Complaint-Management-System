<?php
/**
 * Public Homepage
 * 
 * Overview of the College Complaint Management System.
 * Clean, academic presentation adhering to unslop design standards.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Home';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="hero-section">
    <div class="hero-content">
        <div class="hero-badge">Academic Grievance Redressal Portal</div>
        <h1 class="hero-title">Transparent College Complaint Management</h1>
        <p class="hero-description">
            A centralized digital platform for students to lodge complaints, submit supporting documentation, and track administrative resolutions in real time.
        </p>

        <div class="hero-actions">
            <?php if (isStudentLoggedIn()): ?>
                <a href="/student/dashboard.php" class="btn btn-primary">Go to Student Dashboard</a>
                <a href="/student/submit-complaint.php" class="btn btn-secondary">Submit New Complaint</a>
            <?php elseif (isAdminLoggedIn()): ?>
                <a href="/admin/dashboard.php" class="btn btn-primary">Go to Admin Dashboard</a>
                <a href="/admin/complaints.php" class="btn btn-secondary">Review Complaints</a>
            <?php else: ?>
                <a href="/student/login.php" class="btn btn-primary">Student Sign In</a>
                <a href="/student/register.php" class="btn btn-secondary">Register as Student</a>
                <a href="/admin/login.php" class="btn btn-quiet">Administrator Portal &rarr;</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section-block">
    <div class="section-header">
        <h2 class="section-title">System Capabilities</h2>
        <p class="section-subtitle">Structured grievance redressal designed for accountability and simplicity.</p>
    </div>

    <div class="grid-3">
        <div class="card feature-card">
            <div class="feature-header">
                <span class="feature-num">01</span>
                <h3 class="card-title">Digital Submission</h3>
            </div>
            <p class="card-text">
                Lodge grievances across seven core institutional categories including Academic, Infrastructure, Library, and Examination issues with optional image evidence.
            </p>
        </div>

        <div class="card feature-card">
            <div class="feature-header">
                <span class="feature-num">02</span>
                <h3 class="card-title">Sequential Tracking</h3>
            </div>
            <p class="card-text">
                Each complaint is issued an immutable sequential tracking ID (<code class="mono-code">CMP-2026-0001</code>) preventing lost paperwork and enabling instant record retrieval.
            </p>
        </div>

        <div class="card feature-card">
            <div class="feature-header">
                <span class="feature-num">03</span>
                <h3 class="card-title">Transparent Redressal</h3>
            </div>
            <p class="card-text">
                Complaints progress clearly through Pending, In Progress, and Resolved stages, keeping students updated directly from their personal dashboard.
            </p>
        </div>
    </div>
</section>

<section class="section-block">
    <div class="card ledger-card">
        <div class="card-header">
            <h3 class="card-title">How The Redressal Workflow Operates</h3>
        </div>
        <div class="card-body">
            <div class="workflow-steps">
                <div class="workflow-step">
                    <span class="step-index">1</span>
                    <div class="step-info">
                        <strong>Register Account</strong>
                        <p>Student registers using their official Student ID, Department, and institutional Email.</p>
                    </div>
                </div>
                <div class="workflow-step">
                    <span class="step-index">2</span>
                    <div class="step-info">
                        <strong>Submit Grievance</strong>
                        <p>Fill out the complaint form, select the category, and optionally attach a supporting photograph.</p>
                    </div>
                </div>
                <div class="workflow-step">
                    <span class="step-index">3</span>
                    <div class="step-info">
                        <strong>Administrative Review</strong>
                        <p>The college administrator reviews details, examines evidence, and initiates corrective actions.</p>
                    </div>
                </div>
                <div class="workflow-step">
                    <span class="step-index">4</span>
                    <div class="step-info">
                        <strong>Resolution & Closure</strong>
                        <p>The issue status is updated to Resolved, completing the institutional audit trail.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
