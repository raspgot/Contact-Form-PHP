<?php

/**
 * CSRF Token Provider
 * Returns a secure CSRF token for form submission
 *
 * @author   Raspgot <contact@raspgot.fr>
 * @link     https://github.com/raspgot/Contact-Form-PHP
 * @version  1.7.5
 */

declare(strict_types=1);

// Start session with secure cookie settings
if (session_status() === PHP_SESSION_NONE) {
    // Configure secure session parameters
    $sessionCookieParams = [
        'lifetime' => 0,                    // Session cookie (expires on browser close)
        'path'     => '/',
        'domain'   => $_SERVER['HTTP_HOST'] ?? '',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',  // HTTPS only
        'httponly' => true,                 // Prevent JavaScript access
        'samesite' => 'Strict'              // CSRF protection
    ];
    session_set_cookie_params($sessionCookieParams);
    session_start();
    
    // Regenerate session ID to prevent session fixation attacks
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
    }
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Always return responses as JSON
header('Content-Type: application/json');

// Generate and return CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

echo json_encode([
    'csrf_token' => $_SESSION['csrf_token']
]);
