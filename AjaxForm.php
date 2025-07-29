<?php

/**
 * Secure Contact Form using PHPMailer & reCAPTCHA v3 with autoreply
 *
 * An advanced, secure AJAX contact form implementation.
 * Features:
 * - Input validation and sanitization
 * - reCAPTCHA v3 verification
 * - Honeypot field detection
 * - Rate limiting per session
 * - SMTP email delivery with auto-reply
 *
 * @author    Raspgot <contact@raspgot.fr>
 * @link      https://github.com/raspgot/AjaxForm-PHPMailer-reCAPTCHA
 * @version   1.7.2
 * @see       https://github.com/PHPMailer/PHPMailer
 * @see       https://developers.google.com/recaptcha/docs/v3
 */

declare(strict_types=1);

// Start session to store rate-limit timestamps
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Always return JSON responses
header('Content-Type: application/json');

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer manually (no-Composer project)
require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';
require_once 'PHPMailer/Exception.php';

// Configuration constants (must be customized)
const SECRET_KEY              = '';                               // Your reCAPTCHA secret key
const SMTP_HOST               = '';                               // SMTP server hostname
const SMTP_USERNAME           = '';                               // SMTP username (sender address)
const SMTP_PASSWORD           = '';                               // SMTP password
const SMTP_SECURE             = 'tls';                            // Encryption protocol: ssl or tls
const SMTP_PORT               = 587;                              // SMTP server port
const SMTP_AUTH               = true;                             // Whether SMTP authentication is required
const FROM_NAME               = 'Raspgot';                        // Name displayed as sender
const EMAIL_SUBJECT_DEFAULT   = '[GitHub] New message received';  // Default subject if none provided
const EMAIL_SUBJECT_AUTOREPLY = 'We have received your message';  // Subject of auto-reply email
const MAX_ATTEMPTS            = 55;                                // Maximum allowed submissions per session
const RATE_LIMIT_DURATION     = 3600;                             // 30 minutes

// User-facing response messages
const RESPONSES = [
    'success'          => '✉️ Your message has been sent !',
    'enter_name'       => '⚠️ Please enter your name.',
    'enter_email'      => '⚠️ Please enter a valid email.',
    'enter_message'    => '⚠️ Please enter your message.',
    'enter_subject'    => '⚠️ Please enter your subject.',
    'token_error'      => '⚠️ reCAPTCHA token is missing.',
    'domain_error'     => '⚠️ Invalid email domain.',
    'method_error'     => '⚠️ Method not allowed.',
    'constant_error'   => '⚠️ Configuration is incomplete.',
    'honeypot_error'   => '🚫 Spam detected.',
    'limit_rate_error' => '🚫 Too many messages sent. Try again later.',
];

// Verify that all configuration constants are set
if (empty(SECRET_KEY) || empty(SMTP_HOST) || empty(SMTP_USERNAME) || empty(SMTP_PASSWORD)) {
    respond(false, RESPONSES['constant_error']);
}

// Enforce POST requests only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, RESPONSES['method_error']);
}

// Basic bot detection via User-Agent header
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
if ($userAgent === '' || preg_match('/\b(curl|wget|bot|crawler|spider)\b/i', $userAgent)) {
    respond(false, RESPONSES['honeypot_error']);
}

// Rate-limiting: allow maximum 5 submissions per hour
checkSessionRateLimit(MAX_ATTEMPTS, RATE_LIMIT_DURATION);

// Input collection and validation
$date     = new DateTime();
$ip       = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: respond(false, RESPONSES['enter_email'], 'email');
$name     = isset($_POST['name']) ? sanitize($_POST['name']) : respond(false, RESPONSES['enter_name']);
$message  = isset($_POST['message']) ? sanitize($_POST['message']) : respond(false, RESPONSES['enter_message']);
$subject  = isset($_POST['subject']) ? sanitize($_POST['subject']) : respond(false, RESPONSES['enter_subject']);
$token    = isset($_POST['recaptcha_token']) ? sanitize($_POST['recaptcha_token']) : respond(false, RESPONSES['token_error']);
$honeypot = trim($_POST['website'] ?? '');

// Honeypot trap (field must stay empty)
if ($honeypot !== '') {
    respond(false, RESPONSES['honeypot_error']);
}

// Check if email domain has valid DNS records
$domain = substr(strrchr($email, "@"), 1);
if (!$domain || (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A'))) {
    respond(false, RESPONSES['domain_error'], 'email');
}

// Validate reCAPTCHA score and authenticity
validateRecaptcha($token);

// Compose HTML email body
$emailBody = renderEmail([
    'subject' => $subject,
    'date'    => $date->format('Y-m-d H:i:s'),
    'name'    => $name,
    'email'   => $email,
    'message' => nl2br($message),
    'ip'      => $ip,
]);

try {
    // Send notification to site owner
    $mail = new PHPMailer(true);
    configureMailer($mail);
    $mail->addAddress(SMTP_USERNAME, 'Admin');
    $mail->addReplyTo($email, $name);
    $mail->Subject = $subject ?: EMAIL_SUBJECT_DEFAULT;
    $mail->Body    = $emailBody;
    $mail->AltBody = strip_tags($emailBody);
    $mail->send();

    // Send confirmation auto-reply to user
    $autoReply = new PHPMailer(true);
    configureMailer($autoReply);
    $autoReply->addAddress($email, $name);
    $autoReply->Subject = EMAIL_SUBJECT_AUTOREPLY . ' — ' . $subject;
    $autoReply->Body = '
        <p>Hello ' . htmlspecialchars($name) . ',</p>
        <p>Thank you for reaching out. Here is a copy of your message:</p>
        <hr>' . $emailBody;
    $autoReply->AltBody = strip_tags($emailBody);
    $autoReply->send();

    respond(true, RESPONSES['success']);
} catch (Exception $e) {
    respond(false, '❌ Mail error: ' . $e->getMessage(), 'email');
}

/**
 * Verifies reCAPTCHA token with Google API and checks score.
 *
 * @param string $token reCAPTCHA token submitted by the form.
 * @return void
 */
function validateRecaptcha(string $token): void
{
    // Initialize cURL session to verify the token
    $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');

    $postFields = http_build_query([
        'secret'   => SECRET_KEY,
        'response' => $token,
    ]);

    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Handle HTTP or cURL error
    if ($response === false) {
        respond(false, '❌ reCAPTCHA request failed : ' . ($curlError ?: 'Unknown cURL error.'));
    }

    if ($httpCode !== 200) {
        respond(false, '❌ reCAPTCHA HTTP error : ' . $httpCode);
    }

    // Parse JSON response
    $data = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        respond(false, '❌ Invalid JSON response from reCAPTCHA.');
    }

    // Check success flag
    if (empty($data['success'])) {
        $errors = isset($data['error-codes']) ? implode(', ', $data['error-codes']) : 'Unknown error.';
        respond(false, '❌ reCAPTCHA verification failed : ' . $errors);
    }

    // Check score (threshold configurable if needed)
    $score = $data['score'] ?? 1.0;
    if ($score < 0.6) {
        respond(false, '❌ Low reCAPTCHA score (' . $score . '). You might be a robot.');
    }
}

/**
 * Cleans and encodes user input to prevent header injection and XSS
 *
 * @param string $data Raw input string
 * @return string Sanitized string
 */
function sanitize(string $data): string
{
    // Remove null bytes and other control characters (except \t)
    $data = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $data);

    // Escape HTML entities (with strict quote handling and UTF-8 safety)
    return trim(htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8', true));
}

/**
 * Sends a JSON response and terminates the script.
 *
 * @param bool   $success Whether the operation was successful.
 * @param string $message Message to be displayed to the user.
 * @param string|null $field Optional field name to mark as invalid.
 *
 * @return never This function does not return; it ends execution with exit().
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
 * Renders the email HTML body from template
 *
 * @param array $data Variables to inject into the template
 * @return string HTML content
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
 * Configures a PHPMailer instance with SMTP settings
 *
 * @param PHPMailer $mailer The PHPMailer instance to configure
 * @return void
 */
function configureMailer(PHPMailer $mailer): void
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
}

/**
 * Tracks and limits number of submissions per session
 *
 * @param int $max    Maximum allowed submissions within the window
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
