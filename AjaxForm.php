<?php

/**
 * This script processes AJAX form submissions, validates input, applies
 * anti-spam measures (honeypot, rate limiting, DNS & reCAPTCHA checks), and
 * sends both an admin notification and an optional autoreply to the user
 *
 * @author   Raspgot <contact@raspgot.fr>
 * @link     https://github.com/raspgot/AjaxForm-PHPMailer-reCAPTCHA
 * @version  1.7.4
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
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer manually (no-Composer project)
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/PHPMailer/Exception.php';

// Configuration constants (⚠️ must be customized before deployment)
const SECRET_KEY              = '';                               // Google reCAPTCHA secret key
const SMTP_HOST               = '';                               // SMTP server hostname
const SMTP_USERNAME           = '';                               // SMTP account / sender address
const SMTP_PASSWORD           = '';                               // SMTP account password
const SMTP_SECURE             = 'tls';                            // Encryption protocol: "ssl" or "tls"
const SMTP_PORT               = 587;                              // SMTP server port (e.g. 465 for SSL, 587 for TLS)
const SMTP_AUTH               = true;                             // Enable/disable SMTP authentication
const FROM_NAME               = 'Raspgot';                        // Sender display name
const EMAIL_SUBJECT_DEFAULT   = '[GitHub] New message received';  // Default subject if none provided
const EMAIL_SUBJECT_AUTOREPLY = 'We have received your message';  // Subject of user autoreply
const MAX_ATTEMPTS            = 5;                                // Max form submissions allowed per session
const RATE_LIMIT_DURATION     = 3600;                             // Rate limit in seconds (1 hour)

// Predefined user-facing response messages
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

// Ensure configuration is properly set
if (empty(SECRET_KEY) || empty(SMTP_HOST) || empty(SMTP_USERNAME) || empty(SMTP_PASSWORD)) {
    respond(false, RESPONSES['constant_error']);
}

// Accept only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, RESPONSES['method_error']);
}

// Basic bot detection using User-Agent
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
if ($userAgent === '' || preg_match('/\b(curl|wget|bot|crawler|spider)\b/i', $userAgent)) {
    respond(false, RESPONSES['honeypot_error']);
}

// Enforce session-based rate limiting
checkSessionRateLimit(MAX_ATTEMPTS, RATE_LIMIT_DURATION);

// Collect and validate user input
$date     = new DateTime();
$ip       = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: respond(false, RESPONSES['enter_email'], 'email');
$name     = isset($_POST['name']) ? sanitize($_POST['name']) : respond(false, RESPONSES['enter_name']);
$message  = isset($_POST['message']) ? sanitize($_POST['message']) : respond(false, RESPONSES['enter_message']);
$subject  = isset($_POST['subject']) ? sanitize($_POST['subject']) : respond(false, RESPONSES['enter_subject']);
$token    = isset($_POST['recaptcha_token']) ? sanitize($_POST['recaptcha_token']) : respond(false, RESPONSES['token_error']);
$honeypot = trim($_POST['website'] ?? '');

// Honeypot trap (hidden field must remain empty)
if ($honeypot !== '') {
    respond(false, RESPONSES['honeypot_error']);
}

// Check if email domain is valid (DNS MX or A record)
$domain = substr(strrchr($email, "@"), 1);
if (!$domain || (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A'))) {
    respond(false, RESPONSES['domain_error'], 'email');
}

// Verify reCAPTCHA token authenticity and score
validateRecaptcha($token);

// Build email body (HTML) from template
$emailBody = renderEmail([
    'subject' => $subject,
    'date'    => $date->format('Y-m-d H:i:s'),
    'name'    => $name,
    'email'   => $email,
    'message' => nl2br($message),
    'ip'      => $ip,
]);

try {
    // Build minimal plain text alternative part from HTML
    $altText = buildAltBody($emailBody);

    // Send notification email to site owner
    $mail = configureMailer(new PHPMailer(true));
    $mail->addAddress(SMTP_USERNAME, 'Admin');
    $mail->addReplyTo($email, $name);
    $mail->Subject = $subject ?: EMAIL_SUBJECT_DEFAULT;
    $mail->Body    = $emailBody;
    $mail->AltBody = $altText;
    $mail->send();

    // Send autoreply confirmation to user
    $autoReply = configureMailer(new PHPMailer(true));
    $autoReply->addAddress($email, $name);
    $autoReply->Subject = EMAIL_SUBJECT_AUTOREPLY . ' — ' . $subject;
    $autoReply->Body = '<p>Hello ' . htmlspecialchars($name) . ',</p>' .
        '<p>Thank you for reaching out. Here is a copy of your message:</p>' .
        '<hr>' . $emailBody;
    $autoReply->AltBody = $altText;
    $autoReply->send();

    respond(true, RESPONSES['success']);
} catch (Exception $e) {
    respond(false, '❌ Mail error: ' . $e->getMessage(), 'email');
}

/**
 * Create a plain-text alternative body from an HTML email fragment
 *
 * @param string $html HTML email body
 * @return string Plain text version
 */
function buildAltBody(string $html): string
{
    $text = preg_replace('/<br\s*\/??>/i', "\n", $html) ?? $html;
    $text = strip_tags($text);
    return html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Verify reCAPTCHA token with Google API and validate score, action, and hostname
 *
 * @param string $token The reCAPTCHA token received from the frontend
 * @return void
 */
function validateRecaptcha(string $token): void
{
    // Initialize cURL request to Google verification API
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

    // If cURL execution failed (network error, timeout, etc.)
    if ($response === false) {
        respond(false, '❌ reCAPTCHA request failed : ' . ($curlError ?: 'Unknown cURL error.'));
    }

    // If Google did not respond with a 200 OK
    if ($httpCode !== 200) {
        respond(false, '❌ reCAPTCHA HTTP error : ' . $httpCode);
    }

    $data = json_decode($response, true);

    // If response cannot be decoded as valid JSON
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        respond(false, '❌ Invalid JSON response from reCAPTCHA.');
    }

    // If Google says "success" flag is false (invalid token, wrong secret, expired, etc.)
    if (empty($data['success'])) {
        $errors = isset($data['error-codes']) ? implode(', ', $data['error-codes']) : 'Unknown error.';
        respond(false, '❌ reCAPTCHA verification failed : ' . $errors);
    }

    // If the "action" does not match the one expected (mitigates token reuse across forms)
    $expectedAction = 'submit';
    if (($data['action'] ?? '') !== $expectedAction) {
        respond(false, '❌ reCAPTCHA action mismatch.');
    }

    // If the hostname returned by Google does not match our server's hostname (prevents token theft)
    $expectedHost = $_SERVER['SERVER_NAME'] ?? '';
    if (!empty($expectedHost) && ($data['hostname'] ?? '') !== $expectedHost) {
        respond(false, '❌ reCAPTCHA hostname mismatch.');
    }

    // If the score is below threshold (Google thinks the request looks like a bot)
    $score = $data['score'] ?? 1.0;
    if ($score < 0.6) {
        respond(false, '❌ Low reCAPTCHA score (' . $score . '). You might be a robot.');
    }
}

/**
 * Sanitize user input to prevent XSS and header injection
 *
 * @param string $data Raw user-supplied input
 * @return string Sanitized value
 */
function sanitize(string $data): string
{
    // Remove control characters and null bytes
    $filtered = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $data);
    if ($filtered === null) {
        $filtered = $data; // Fallback to original if regex engine fails
    }

    // Escape HTML entities (UTF-8 safe)
    return trim(htmlspecialchars($filtered, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8', true));
}

/**
 * Send JSON response and terminate execution
 *
 * @param bool        $success Success flag
 * @param string      $message Message to display
 * @param string|null $field Optional field to highlight as invalid
 * @return never
 */
function respond(bool $success, string $message, ?string $field = null): never
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'field'   => $field,
    ]);
    exit;
}

/**
 * Render the HTML email body using the external template file
 *
 * @param array{
 *   subject: string,
 *   date: string,
 *   name: string,
 *   email: string,
 *   message: string,
 *   ip: string
 * } $data Strictly typed template variables
 *
 * Extraction uses EXTR_SKIP to avoid overwriting existing variables inside the closure scope
 * Output buffering captures the template output as a string
 *
 * @return string Fully rendered HTML fragment
 * @throws RuntimeException If the template file cannot be found
 */
function renderEmail(array $data): string
{
    // Path to the email template (adjustable if needed)
    $template = __DIR__ . '/email_template.php';
    if (!is_file($template)) {
        throw new RuntimeException("Email template not found: $template");
    }

    // Encapsulate in a local scope to avoid variable pollution
    return (function () use ($data, $template): string {
        extract($data, EXTR_SKIP); // convert array keys into local variables
        ob_start();
        require $template;
        return ob_get_clean();
    })();
}

/**
 * Configure a PHPMailer instance with project SMTP defaults
 *
 * @param PHPMailer $mailer The PHPMailer instance to configure
 * @return PHPMailer Configured PHPMailer instance
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
 * Enforce a simple session-based rate limit
 *
 * @param int $max Max submissions allowed
 * @param int $window Time window in seconds
 * @return void
 */
function checkSessionRateLimit(int $max = 5, int $window = 3600): void
{
    $now = time();
    $_SESSION['rate_limit_times'] ??= [];
    $_SESSION['rate_limit_times'] = array_filter(
        $_SESSION['rate_limit_times'],
        fn($timestamp) => $timestamp >= ($now - $window)
    );
    if (count($_SESSION['rate_limit_times']) >= $max) {
        respond(false, RESPONSES['limit_rate_error']);
    }
    $_SESSION['rate_limit_times'][] = $now;
}
