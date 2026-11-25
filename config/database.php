<?php
/**
 * Database Connection Configuration
 * 
 * Uses PHP PDO (PHP Data Objects) for secure, prepared SQL interactions.
 * Reads credentials from environment variables (Docker or .env file).
 * Beginner-friendly, robust, and clean.
 */

// Helper function to read from environment or fallback
function getEnvValue(string $key, string $default = ''): string {
    // 1. Check getenv
    $val = getenv($key);
    if ($val !== false && $val !== '') {
        return $val;
    }

    // 2. Check $_ENV and $_SERVER
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return $_SERVER[$key];
    }

    // 3. Fallback: Parse .env file if it exists in project root
    static $envFileParsed = null;
    if ($envFileParsed === null) {
        $envFileParsed = [];
        $envPath = dirname(__DIR__) . '/.env';
        if (file_exists($envPath) && is_readable($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }
                if (str_contains($line, '=')) {
                    list($k, $v) = explode('=', $line, 2);
                    $envFileParsed[trim($k)] = trim($v, " \t\n\r\0\x0B\"'");
                }
            }
        }
    }

    return $envFileParsed[$key] ?? $default;
}

/**
 * Returns a PDO database connection instance.
 * 
 * @return PDO
 * @throws PDOException
 */
function getDbConnection(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $host     = getEnvValue('DB_HOST', 'db');
        $port     = getEnvValue('DB_PORT', '3306');
        $dbname   = getEnvValue('DB_NAME', 'college_grievance');
        $username = getEnvValue('DB_USER', 'grievance_user');
        $password = getEnvValue('DB_PASS', 'grievance_pass');
        $charset  = 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on SQL errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Return associative arrays
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements
        ];

        try {
            $pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            // Friendly error message for students & administrators
            error_log('Database Connection Error: ' . $e->getMessage());
            die(
                '<div style="font-family:sans-serif; max-width:600px; margin:40px auto; padding:24px; border:1px solid #e5e7eb; border-radius:8px; background:#fff;">' .
                '<h2 style="color:#b91c1c; margin-top:0;">Database Connection Error</h2>' .
                '<p style="color:#374151;">Could not connect to the MySQL database. Please verify your Docker containers are running.</p>' .
                '<p style="color:#6b7280; font-size:13px; font-family:monospace;">Host: ' . htmlspecialchars($host) . ' | Database: ' . htmlspecialchars($dbname) . '</p>' .
                '</div>'
            );
        }
    }

    return $pdo;
}
