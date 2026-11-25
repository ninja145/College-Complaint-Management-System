<?php
/**
 * Helper and Utility Functions
 * 
 * Provides CSRF protection, input sanitation, file uploads,
 * tracking ID generation, and flash messages.
 * Designed with clean, beginner-friendly procedural code.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';

/**
 * List of standard complaint categories as specified in PRD FR-03
 */
function getComplaintCategories(): array {
    return [
        'Academic Issues',
        'Infrastructure Problems',
        'Library Issues',
        'Laboratory Issues',
        'Hostel Issues',
        'Examination Issues',
        'Other Issues'
    ];
}

/**
 * Escape output for safe HTML display (XSS prevention)
 */
function e(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token if one does not exist
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render a hidden HTML CSRF input field
 */
function csrfField(): string {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

/**
 * Validate CSRF token against session
 */
function validateCsrfToken(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Set a user notification flash message
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type'    => in_array($type, ['success', 'error', 'warning', 'info']) ? $type : 'info',
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Render flash message banner HTML if exists
 */
function renderFlash(): string {
    $flash = getFlash();
    if (!$flash) {
        return '';
    }

    $typeClass = 'alert-' . e($flash['type']);
    $title = match ($flash['type']) {
        'success' => 'Success',
        'error'   => 'Notice',
        'warning' => 'Warning',
        default   => 'Information'
    };

    return '<div class="alert ' . $typeClass . '" role="alert">' .
           '<div class="alert-content">' .
           '<strong>' . e($title) . ':</strong> ' . e($flash['message']) .
           '</div>' .
           '</div>';
}

/**
 * Generate a unique sequential tracking ID for a complaint.
 * Example format: CMP-2026-0001
 * 
 * @param PDO $pdo
 * @return string
 */
function generateTrackingId(PDO $pdo): string {
    $year = date('Y');
    $prefix = "CMP-{$year}-";

    // Find highest sequence for the current year
    $stmt = $pdo->prepare("
        SELECT tracking_id FROM complaints 
        WHERE tracking_id LIKE :pattern 
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->execute([':pattern' => $prefix . '%']);
    $lastId = $stmt->fetchColumn();

    if ($lastId) {
        // Extract sequence number from e.g. CMP-2026-0012
        $parts = explode('-', $lastId);
        $nextNum = (int)end($parts) + 1;
    } else {
        $nextNum = 1;
    }

    return sprintf('%s%04d', $prefix, $nextNum);
}

/**
 * Handle optional supporting image upload
 * 
 * Requirements from PRD FR-04:
 * - Allowed types: JPG, JPEG, PNG
 * - Maximum size: 2 MB
 * - Generate unique filenames
 * - Reject executable files & prevent path traversal
 * 
 * @param array $file The $_FILES['attachment'] array
 * @return array ['success' => bool, 'filename' => ?string, 'error' => ?string]
 */
function handleImageUpload(array $file): array {
    // If no file was uploaded, that's valid since upload is optional
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => true, 'filename' => null, 'error' => null];
    }

    // Check for upload error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'filename' => null, 'error' => 'File upload encountered an error (Code: ' . $file['error'] . ').'];
    }

    // 1. Validate file size (Maximum 2 MB = 2 * 1024 * 1024 bytes)
    $maxSize = 2 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'filename' => null, 'error' => 'The uploaded image exceeds the 2 MB maximum limit.'];
    }

    // 2. Validate file extension
    $originalName = basename($file['name']);
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png'];

    if (!in_array($ext, $allowedExtensions, true)) {
        return ['success' => false, 'filename' => null, 'error' => 'Invalid file extension. Only JPG, JPEG, and PNG images are allowed.'];
    }

    // 3. Validate actual MIME type using finfo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    $allowedMimes = ['image/jpeg', 'image/png'];

    if (!in_array($mimeType, $allowedMimes, true)) {
        return ['success' => false, 'filename' => null, 'error' => 'Invalid file format. The uploaded file is not a valid JPEG or PNG image.'];
    }

    // 4. Verify actual image dimensions
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return ['success' => false, 'filename' => null, 'error' => 'The uploaded file could not be verified as an image.'];
    }

    // 5. Generate secure, unique filename to avoid collisions and directory traversal
    $newFilename = 'comp_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
    
    // Directory on server: public/uploads/
    $uploadDir = dirname(__DIR__) . '/public/uploads';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0775, true);
    }

    $destination = $uploadDir . '/' . $newFilename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'filename' => null, 'error' => 'Failed to save the uploaded image to the server directory.'];
    }

    // Ensure permissions
    @chmod($destination, 0644);

    // Return the relative path stored in the database
    return [
        'success'  => true,
        'filename' => 'uploads/' . $newFilename,
        'error'    => null
    ];
}

/**
 * Render a standardized status badge following unslop design rules
 */
function renderStatusBadge(string $status): string {
    $normalized = strtolower(trim($status));
    $class = match ($normalized) {
        'pending'     => 'badge-pending',
        'in progress' => 'badge-progress',
        'resolved'    => 'badge-resolved',
        default       => 'badge-neutral'
    };

    return '<span class="status-badge ' . $class . '">' .
           '<span class="status-dot"></span>' .
           e($status) .
           '</span>';
}

/**
 * Format datetime neatly for institutional records
 */
function formatDate(?string $timestamp): string {
    if (!$timestamp) {
        return '—';
    }
    $time = strtotime($timestamp);
    if ($time === false) {
        return e($timestamp);
    }
    return date('d M Y, h:i A', $time);
}
