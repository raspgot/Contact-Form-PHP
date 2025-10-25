<?php

/**
 * Contact Form Backend with PHPMailer and reCAPTCHA v3
 *
 * @author   Raspgot <contact@raspgot.fr>
 * @link     https://github.com/raspgot/Contact-Form-PHP
 * @version  1.7.5
 * @see      https://github.com/PHPMailer/PHPMailer
 * @see      https://developers.google.com/recaptcha/docs/v3
 */

declare(strict_types=1);

// Start session (required for rate limiting)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Always return responses as JSON
header('Content-Type: application/json');

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;

// Load PHPMailer manually (no-Composer project)
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/PHPMailer/Exception.php';

// ============================================================================
// CONFIGURATION (⚠️ Must be customized before deployment)
// ============================================================================

const SECRET_KEY              = '';                              // reCAPTCHA secret key
const SMTP_HOST               = '';                              // SMTP server address
const SMTP_USERNAME           = '';                              // Email account / sender
const SMTP_PASSWORD           = '';                              // Email password
const SMTP_SECURE             = 'tls';                           // Encryption: "tls" or "ssl"
const SMTP_PORT               = 587;                             // Port: 587 (TLS) or 465 (SSL)
const SMTP_AUTH               = true;                            // Enable SMTP authentication
const FROM_NAME               = 'Raspgot';                       // Sender display name
const EMAIL_SUBJECT_DEFAULT   = '[GitHub] New message received'; // Default subject if empty
const EMAIL_SUBJECT_AUTOREPLY = 'We have received your message'; // Auto-reply subject
const MAX_ATTEMPTS            = 5;                               // Max submissions per session
const RATE_LIMIT_DURATION     = 3600;                            // Rate limit window (1 hour)
const RECAPTCHA_MIN_SCORE     = 0.6;                             // Minimum bot score (0.0 - 1.0)

// ============================================================================
// USER-FACING ERROR MESSAGES
// ============================================================================

const RESPONSES = [
    'success'          => '✉️ Your message has been sent!',
    'enter_name'       => '⚠️ Please enter your name.',
    'enter_email'      => '⚠️ Please enter a valid email address.',
    'enter_message'    => '⚠️ Please enter your message.',
    'enter_subject'    => '⚠️ Please enter a subject.',
    'token_error'      => '⚠️ reCAPTCHA token missing.',
    'domain_error'     => '⚠️ Invalid email domain.',
    'method_error'     => '⚠️ Method not allowed.',
    'constant_error'   => '⚠️ Missing configuration constants.',
    'honeypot_error'   => '🚫 Spam detected.',
    'limit_rate_error' => '🚫 Too many messages sent. Please try again later.',
];

// ============================================================================
// STEP 1: Verify configuration
// ============================================================================

if (empty(SECRET_KEY) || empty(SMTP_HOST) || empty(SMTP_USERNAME) || empty(SMTP_PASSWORD)) {
    respond(false, RESPONSES['constant_error']);
}

// ============================================================================
// STEP 2: Basic request validation
// ============================================================================

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, RESPONSES['method_error']);
}

// Block suspicious User-Agents (bots, scrapers, command-line tools)
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
if ($userAgent === '' || preg_match('/\b(curl|wget|bot|crawler|spider)\b/i', $userAgent)) {
    respond(false, RESPONSES['honeypot_error']);
}

// ============================================================================
// STEP 3: Rate limiting (prevent spam floods)
// ============================================================================

checkSessionRateLimit(MAX_ATTEMPTS, RATE_LIMIT_DURATION);

// ============================================================================
// STEP 4: Collect and validate form data
// ============================================================================

$date     = new DateTime();
$ip       = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: respond(false, RESPONSES['enter_email'], 'email');
$name     = isset($_POST['name']) ? sanitize($_POST['name']) : respond(false, RESPONSES['enter_name']);
$message  = isset($_POST['message']) ? sanitize($_POST['message']) : respond(false, RESPONSES['enter_message']);
$subject  = isset($_POST['subject']) ? sanitize($_POST['subject']) : respond(false, RESPONSES['enter_subject']);
$honeypot = trim($_POST['website'] ?? '');
$token    = isset($_POST['recaptcha_token']) ? $_POST['recaptcha_token'] : respond(false, RESPONSES['token_error']);

// Honeypot trap: bots fill this hidden field, humans don't see it
if ($honeypot !== '') {
    respond(false, RESPONSES['honeypot_error']);
}

// Verify email domain exists (DNS check)
$domain = substr(strrchr($email, "@"), 1);
if (!$domain || (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A'))) {
    respond(false, RESPONSES['domain_error'], 'email');
}

// ============================================================================
// STEP 5: Verify reCAPTCHA (bot protection)
// ============================================================================
validateRecaptcha($token);

// ============================================================================
// STEP 6: Build email content from template
// ============================================================================

$emailBody = renderEmail([
    'subject' => $subject,
    'date'    => $date->format('Y-m-d H:i:s'),
    'name'    => $name,
    'email'   => $email,
    'message' => nl2br($message), // Convert newlines to <br> tags
    'ip'      => $ip,
]);

// ============================================================================
// STEP 7: Send emails (admin notification + user auto-reply)
// ============================================================================

try {
    // Email #1: Notification to site owner/admin
    $adminMail = configureMailer(new PHPMailer(true));
    $adminMail->addAddress(SMTP_USERNAME, 'Admin');
    $adminMail->addReplyTo($email, $name);
    $adminMail->Subject = $subject ?: EMAIL_SUBJECT_DEFAULT;
    $adminMail->Body    = $emailBody;
    $adminMail->AltBody = buildAltBody($emailBody);
    $adminMail->send();

    // Email #2: Auto-reply confirmation to user
    $autoReply = configureMailer(new PHPMailer(true));
    $autoReply->addAddress($email, $name);
    $autoReply->Subject = EMAIL_SUBJECT_AUTOREPLY . ' — ' . $subject;
    $autoReplyHtml = '<p>Hello ' . $name . ',</p>'
        . '<p>Thank you for reaching out. Here is a copy of your message:</p>'
        . '<hr>' . $emailBody;
    $autoReply->Body    = $autoReplyHtml;
    $autoReply->AltBody = buildAltBody($autoReplyHtml);
    $autoReply->send();

    // Success! Return positive response
    respond(true, RESPONSES['success']);
    
} catch (Exception $e) {
    // Email sending failed (SMTP error, network issue, etc.)
    respond(false, '❌ Mail error: ' . $e->getMessage(), 'email');
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Convert HTML email to plain text alternative
 * Replaces <br> with newlines, strips HTML tags, decodes entities
 *
 * @param string $html HTML email body
 * @return string Plain text version
 */
function buildAltBody(string $html): string
{
    // Convert line breaks and paragraph endings to newlines
    $text = preg_replace('/<br\s*\/?>(?i)/', "\n", $html) ?? $html; // case-insensitive via inline modifier
    $text = preg_replace('/<\/p\s*>/i', "\n\n", $text) ?? $text;
    $text = strip_tags($text);
    return html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Verify reCAPTCHA token with Google and validate score
 * Terminates script if validation fails
 *
 * @param string $token The reCAPTCHA token from frontend
 * @return void
 */
function validateRecaptcha(string $token): void
{
    // Contact Google's reCAPTCHA API
    $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');

    $postFields = http_build_query([
        'secret'   => SECRET_KEY,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);

    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);

    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Check 1: cURL request succeeded
    if ($response === false) {
        respond(false, '❌ reCAPTCHA request failed: ' . ($curlError ?: 'Unknown cURL error.'));
    }

    // Check 2: Google returned HTTP 200
    if ($httpCode !== 200) {
        respond(false, '❌ reCAPTCHA HTTP error: ' . $httpCode);
    }

    $data = json_decode($response, true);

    // Check 3: Valid JSON response
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        respond(false, '❌ Invalid JSON response from reCAPTCHA.');
    }

    // Check 4: Google says token is valid
    if (empty($data['success'])) {
        $errors = isset($data['error-codes']) ? implode(', ', $data['error-codes']) : 'Unknown error.';
        respond(false, '❌ reCAPTCHA verification failed: ' . $errors);
    }

    // Check 5: Action matches (prevents token reuse across different forms)
    $expectedAction = 'submit';
    if (($data['action'] ?? '') !== $expectedAction) {
        respond(false, '❌ reCAPTCHA action mismatch.');
    }

    // Check 6: Hostname matches (prevents token theft from other sites)
    $expectedHost = $_SERVER['SERVER_NAME'] ?? '';
    if (!empty($expectedHost) && ($data['hostname'] ?? '') !== $expectedHost) {
        respond(false, '❌ reCAPTCHA hostname mismatch.');
    }

    // Check 7: Score is above minimum threshold (0.0 = bot, 1.0 = human)
    $score = $data['score'] ?? 1.0;
    if ($score < RECAPTCHA_MIN_SCORE) {
        respond(false, '❌ Low reCAPTCHA score (' . $score . '). You might be a robot.');
    }
}

/**
 * Sanitize user input to prevent XSS and injection attacks
 * Removes control characters and escapes HTML entities
 *
 * @param string $data Raw user input
 * @return string Safe, sanitized string
 */
function sanitize(string $data): string
{
    // Remove control characters and null bytes (security)
    $filtered = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $data);
    if ($filtered === null) {
        $filtered = $data; // Fallback if regex fails
    }

    // Escape HTML special characters
    return trim(htmlspecialchars($filtered, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8', true));
}

/**
 * Send JSON response and stop script execution
 * Used for all API responses (success or error)
 *
 * @param bool        $success Success flag
 * @param string      $message User-facing message
 * @param string|null $field   Optional field name for validation errors
 * @return void
 */
function respond(bool $success, string $message, ?string $field = null): void
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'field'   => $field,
    ]);
    exit;
}

/**
 * Render email body from PHP template file
 * Uses output buffering to capture template output
 *
 * @param array{
 *   subject: string,
 *   date: string,
 *   name: string,
 *   email: string,
 *   message: string,
 *   ip: string
 * } $data Template variables
 *
 * @return string Rendered HTML email
 * @throws RuntimeException If template file not found
 */
function renderEmail(array $data): string
{
    $template = __DIR__ . '/email_template.php';
    if (!is_file($template)) {
        throw new RuntimeException("Email template not found: $template");
    }

    // Use closure to isolate template scope
    return (function () use ($data, $template): string {
        extract($data, EXTR_SKIP);  // Convert array to variables
        ob_start();
        require $template;
        return ob_get_clean();
    })();
}

/**
 * Configure PHPMailer with SMTP settings
 * Returns configured instance for fluent chaining
 *
 * @param PHPMailer $mailer PHPMailer instance to configure
 * @return PHPMailer Configured mailer
 */
function configureMailer(PHPMailer $mailer): PHPMailer
{
    $mailer->isSMTP();
    $mailer->Host       = SMTP_HOST;
    $mailer->SMTPAuth   = SMTP_AUTH;
    $mailer->Username   = SMTP_USERNAME;
    $mailer->Password   = SMTP_PASSWORD;
    $mailer->SMTPSecure = SMTP_SECURE;
    $mailer->Port       = SMTP_PORT;
    $mailer->setFrom(SMTP_USERNAME, FROM_NAME);
    $mailer->Sender     = SMTP_USERNAME;
    $mailer->isHTML(true);
    $mailer->CharSet    = 'UTF-8';
    return $mailer;
}

/**
 * Enforce session-based rate limit
 * Prevents users from spamming the form
 *
 * @param int $max    Maximum allowed submissions
 * @param int $window Time window in seconds
 * @return void
 */
function checkSessionRateLimit(int $max = 5, int $window = 3600): void
{
    $now = time();
    $_SESSION['rate_limit_times'] ??= [];
    
    // Remove timestamps older than the window
    $_SESSION['rate_limit_times'] = array_filter(
        $_SESSION['rate_limit_times'],
        fn($timestamp) => $timestamp >= ($now - $window)
    );
    
    // Block if too many recent submissions
    if (count($_SESSION['rate_limit_times']) >= $max) {
        respond(false, RESPONSES['limit_rate_error']);
    }
    
    // Record this submission
    $_SESSION['rate_limit_times'][] = $now;
}
