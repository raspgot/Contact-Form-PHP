<?php

/**
 * Secure Contact Form using PHPMailer & reCAPTCHA v3 with autoreply
 *
 * Secure AJAX contact form using PHPMailer and reCAPTCHA v3 with autoreply, validating and sanitizing input, verifying tokens, checking email domains, sending SMTP notifications and acknowledgments, detecting honeypots, and enforcing rate limits.
 *
 * @author    Raspgot <contact@raspgot.fr>
 * @link      https://github.com/raspgot/AjaxForm-PHPMailer-reCAPTCHA
 * @license   MIT
 * @version   1.7.0
 * @package   PHPMailer
 * @category  Forms
 * @see       https://github.com/PHPMailer/PHPMailer
 * @see       https://developers.google.com/recaptcha/docs/v3
 * @todo      Add logging to a file
 * @todo      Implement IP blacklist
 */

declare(strict_types=1);

// Start session for rate-limiting
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// JSON response header
header('Content-Type: application/json');

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer source files (no Composer)
require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';
require_once 'PHPMailer/Exception.php';

// 🛠️ Configuration constants
const SECRET_KEY              = '';                              // reCAPTCHA secret
const SMTP_HOST               = '';                              // SMTP host
const SMTP_USERNAME           = '';                              // SMTP username
const SMTP_PASSWORD           = '';                              // SMTP password
const SMTP_SECURE             = 'tls';                           // Encryption method
const SMTP_PORT               = 587;                             // SMTP port (TLS)
const SMTP_AUTH               = true;                            // Enable SMTP auth
const FROM_NAME               = 'Raspgot';                       // Sender name
const EMAIL_SUBJECT           = '[GitHub] New message received'; // Email subject
const EMAIL_SUBJECT_AUTOREPLY = 'We have received your message'; // Auto-reply subject

/**
 * Predefined user messages
 */
const RESPONSES = [
    'success'          => '✉️ Your message has been sent !',
    'enter_name'       => '⚠️ Please enter your name.',
    'enter_email'      => '⚠️ Please enter a valid email.',
    'enter_message'    => '⚠️ Please enter your message.',
    'token_error'      => '⚠️ reCAPTCHA token is missing.',
    'domain_error'     => '⚠️ Invalid email domain.',
    'method_error'     => '⚠️ Method not allowed.',
    'constant_error'   => '⚠️ Configuration is incomplete.',
    'honeypot_error'   => '🚫 Spam detected.',
    'limit_rate_error' => '🚫 Too many messages sent. Please try again later.',
];

// Ensure all necessary constants are set
if (empty(SECRET_KEY) || empty(SMTP_HOST) || empty(SMTP_USERNAME) || empty(SMTP_PASSWORD)) {
    respond(false, RESPONSES['constant_error']);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, RESPONSES['method_error']);
}

// Basic bot detection
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
if ($userAgent === '' || preg_match('/\b(curl|wget|bot|crawler|spider)\b/i', $userAgent)) {
    respond(false, RESPONSES['honeypot_error']);
}

// Rate limit: max 3 per hour (3600s)
checkSessionRateLimit(3, 3600);

// Input validation
$date     = new DateTime();
$ip       = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: respond(false, RESPONSES['enter_email']);
$name     = isset($_POST['name']) ? sanitize($_POST['name']) : respond(false, RESPONSES['enter_name']);
$message  = isset($_POST['message']) ? sanitize($_POST['message']) : respond(false, RESPONSES['enter_message']);
$token    = isset($_POST['recaptcha_token']) ? sanitize($_POST['recaptcha_token']) : respond(false, RESPONSES['token_error']);
$honeypot = trim($_POST['website'] ?? '');

if ($honeypot !== '') {
    respond(false, RESPONSES['honeypot_error']);
}

// Validate email domain
$domain = substr(strrchr($email, "@"), 1);
if (!$domain || (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A'))) {
    respond(false, RESPONSES['domain_error']);
}

// Validate reCAPTCHA (Google)
validateRecaptcha($token);

// Prepare email content
$emailBody = renderEmail([
    'subject' => EMAIL_SUBJECT,
    'date'    => $date->format('Y-m-d H:i:s'),
    'name'    => $name,
    'email'   => $email,
    'message' => nl2br($message),
    'ip'      => $ip,
]);

try {
    // Send main email
    $mail = new PHPMailer(true);
    configureMailer($mail);
    $mail->addAddress(SMTP_USERNAME, 'Admin');
    $mail->addReplyTo($email, $name);
    $mail->Subject = EMAIL_SUBJECT;
    $mail->Body    = $emailBody;
    $mail->AltBody = strip_tags($emailBody);
    $mail->send();

    // Send auto-reply
    $autoReply = new PHPMailer(true);
    configureMailer($autoReply);
    $autoReply->addAddress($email, $name);
    $autoReply->Subject = EMAIL_SUBJECT_AUTOREPLY;
    $autoReply->Body = '
        <p>Hello ' . htmlspecialchars($name) . ',</p>
        <p>Thank you for reaching out. Here is a copy of your message:</p>
        <hr>' . $emailBody;
    $autoReply->AltBody = strip_tags($emailBody);
    $autoReply->send();

    respond(true, RESPONSES['success']);
} catch (Exception $e) {
    respond(false, '❌ Mail error: ' . $e->getMessage());
}

/**
 * Validates reCAPTCHA token against Google
 *
 * @param string $token reCAPTCHA token received from frontend
 */
function validateRecaptcha(string $token): void
{
    $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'secret'   => SECRET_KEY,
            'response' => $token,
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);

    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        respond(false, '❌ reCAPTCHA request failed : ' . ($curlError ?: "HTTP $httpCode"));
    }

    $data = json_decode($response, true);
    if (empty($data['success'])) {
        respond(false, '❌ reCAPTCHA failed : ', $data['error-codes'] ?? []);
    }

    // Reject if score is too low (likely a bot)
    if (($data['score'] ?? 1) < 0.5) {
        respond(false, '❌ Low reCAPTCHA score. You might be a robot.');
    }
}

/**
 * Sanitize input to prevent header injection and XSS
 *
 * @param string $data
 * @return string
 */
function sanitize(string $data): string
{
    // Remove null bytes and other control characters (except \t)
    $data = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $data);

    // Escape HTML entities (with strict quote handling and UTF-8 safety)
    return trim(htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8', true));
}

/**
 * Send a JSON response and stops execution
 *
 * @param bool   $success
 * @param string $message
 * @param mixed  $detail
 * @return void
 */
function respond(bool $success, string $message, mixed $detail = null): void
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'detail'  => $detail,
    ]);
    exit;
}

/**
 * Renders the email HTML using a template
 *
 * @param array $data
 * @return string
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
 * Configures a PHPMailer instance
 *
 * @param PHPMailer $mailer
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
 * Enforces rate-limiting per session
 *
 * @param int $max
 * @param int $window
 * @return void
 */
function checkSessionRateLimit(int $max = 3, int $window = 3600): void
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
