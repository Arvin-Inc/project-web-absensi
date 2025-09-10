<?php
/**
 * Security Configuration File
 * Contains security settings and utility functions for the application
 */

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 when HTTPS is enabled
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');

    // Start session
    session_start();

    // Regenerate session ID to prevent session fixation
    if (!isset($_SESSION['CREATED'])) {
        $_SESSION['CREATED'] = time();
    } else if (time() - $_SESSION['CREATED'] > 1800) {
        // Regenerate session ID every 30 minutes
        session_regenerate_id(true);
        $_SESSION['CREATED'] = time();
    }
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true;
}

/**
 * Sanitize input data
 */
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate secure random string
 */
function generate_secure_token($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Log security events
 */
function log_security_event($event, $details = '') {
    $log_file = __DIR__ . '/../logs/security.log';
    $log_dir = dirname($log_file);

    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $user_id = $_SESSION['user_id'] ?? 'guest';

    $log_entry = sprintf(
        "[%s] [%s] [User: %s] [IP: %s] [UA: %s] %s - %s\n",
        $timestamp,
        strtoupper($event),
        $user_id,
        $ip,
        substr($user_agent, 0, 100),
        $details,
        PHP_EOL
    );

    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

/**
 * Check if user is rate limited
 */
function is_rate_limited($action, $max_attempts = 5, $time_window = 900) { // 15 minutes
    $key = $action . '_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [];
    }

    $now = time();

    if (!isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = ['attempts' => 1, 'first_attempt' => $now];
        return false;
    }

    $attempts = $_SESSION['rate_limit'][$key]['attempts'];
    $first_attempt = $_SESSION['rate_limit'][$key]['first_attempt'];

    // Reset if time window has passed
    if ($now - $first_attempt > $time_window) {
        $_SESSION['rate_limit'][$key] = ['attempts' => 1, 'first_attempt' => $now];
        return false;
    }

    // Increment attempts
    $_SESSION['rate_limit'][$key]['attempts'] = $attempts + 1;

    return $attempts >= $max_attempts;
}

/**
 * Send security headers
 */
function send_security_headers() {
    // Prevent clickjacking
    header("X-Frame-Options: DENY");

    // Prevent MIME type sniffing
    header("X-Content-Type-Options: nosniff");

    // Enable XSS protection
    header("X-XSS-Protection: 1; mode=block");

    // Referrer policy
    header("Referrer-Policy: strict-origin-when-cross-origin");

    // Content Security Policy (basic)
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; img-src 'self' data: http: https: https://ui-avatars.com; font-src 'self' https://fonts.gstatic.com;");

    // HSTS (uncomment when HTTPS is enabled)
    // header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
}

/**
 * Validate file upload security
 */
function validate_file_upload($file, $allowed_types = ['image/jpeg', 'image/png'], $max_size = 5242880) { // 5MB
    $errors = [];

    // Check if file was uploaded
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload failed';
        return $errors;
    }

    // Check file size
    if ($file['size'] > $max_size) {
        $errors[] = 'File size exceeds maximum allowed size';
    }

    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        $errors[] = 'File type not allowed';
    }

    // Check file extension
    $allowed_extensions = ['jpg', 'jpeg', 'png'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($file_extension, $allowed_extensions)) {
        $errors[] = 'File extension not allowed';
    }

    // Additional security checks
    if (strpos($file['name'], '..') !== false) {
        $errors[] = 'Invalid file name';
    }

    return $errors;
}

/**
 * Secure file upload
 */
function secure_file_upload($file, $upload_dir, $filename_prefix = '') {
    // Generate secure filename
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $secure_filename = $filename_prefix . '_' . generate_secure_token(16) . '.' . $extension;

    $target_path = $upload_dir . '/' . $secure_filename;

    // Ensure upload directory exists and is secure
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Create .htaccess to prevent script execution in upload directory
    $htaccess_path = $upload_dir . '/.htaccess';
    if (!file_exists($htaccess_path)) {
        file_put_contents($htaccess_path, "php_flag engine off\nAddType text/plain .php .php3 .php4 .php5 .phtml .pl .py .cgi");
    }

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return $secure_filename;
    }

    return false;
}

// Send security headers on every request
send_security_headers();
?>
