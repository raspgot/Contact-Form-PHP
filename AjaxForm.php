<?php

/**
 * Secure Contact Form using PHPMailer & reCAPTCHA v3
 * Includes honeypot protection and enhanced email formatting
 *
 * @see      https://github.com/raspgot/AjaxForm-PHPMailer-reCAPTCHA
 * @package  PHPMailer
 * @version  1.6.0
 */

declare(strict_types=1);

// Set the HTTP response content type to JSON
header('Content-Type: application/json');

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer source files (no Composer)
require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';
require_once 'PHPMailer/Exception.php';

// 🛠️ Configuration constants for SMTP and reCAPTCHA
const SECRET_KEY     = '';                       // Google reCAPTCHA secret key
const SMTP_HOST      = '';                       // SMTP server hostname
const SMTP_USERNAME  = '';                       // SMTP username
const SMTP_PASSWORD  = '';                       // SMTP password
const SMTP_SECURE    = 'tls';                    // Encryption method: TLS or SSL
const SMTP_PORT      = 587;                      // Port for TLS (587) or SSL (465)
const SMTP_AUTH      = true;                     // Enable SMTP authentication
const FROM_NAME      = 'Raspgot';                // Sender name
const EMAIL_SUBJECT  = '[Github] New message !'; // Subject for outgoing emails

// Predefined response messages
const EMAIL_MESSAGES = [
    'success'        => '✉️ Your message has been sent !',
    'enter_name'     => '⚠️ Please enter your name.',
    'enter_email'    => '⚠️ Please enter a valid email.',
    'enter_message'  => '⚠️ Please enter your message.',
    'token_error'    => '⚠️ No reCAPTCHA token received.',
    'domain_error'   => '⚠️ The email domain is invalid.',
    'method_error'   => '⚠️ Method not allowed.',
    'constant_error' => '⚠️ Missing configuration constants.',
    'honeypot_error' => '🚫 Spam detected.',
];

// Ensure all necessary constants are set
if (empty(SECRET_KEY) || empty(SMTP_HOST) || empty(SMTP_USERNAME) || empty(SMTP_PASSWORD)) {
    respond(false, EMAIL_MESSAGES['constant_error']);
}

// Allow only POST requests (reject GET or others)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, EMAIL_MESSAGES['method_error']);
}

// Simple bot detection based on the user-agent string
if (empty($_SERVER['HTTP_USER_AGENT']) || preg_match('/\b(curl|wget|httpie|python-requests|httpclient|bot|spider|crawler|scrapy)\b/i', $_SERVER['HTTP_USER_AGENT'])) {
    respond(false, EMAIL_MESSAGES['honeypot_error']);
}

// Gather and validate user input from POST data
$date     = new DateTime();
$ip       = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: respond(false, EMAIL_MESSAGES['enter_email']);
$name     = isset($_POST['name']) ? sanitize($_POST['name']) : respond(false, EMAIL_MESSAGES['enter_name']);
$message  = isset($_POST['message']) ? sanitize($_POST['message']) : respond(false, EMAIL_MESSAGES['enter_message']);
$token    = isset($_POST['recaptcha_token']) ? sanitize($_POST['recaptcha_token']) : respond(false, EMAIL_MESSAGES['token_error']);
$honeypot = isset($_POST['website']) ? trim($_POST['website']) : '';
if (!empty($honeypot)) {
    respond(false, EMAIL_MESSAGES['honeypot_error']);
}

// Check if the email domain is valid (DNS records)
$domain = substr(strrchr($email, "@"), 1);
if (!checkdnsrr($domain, "MX") && !checkdnsrr($domain, "A")) {
    respond(false, EMAIL_MESSAGES['domain_error']);
}

// Validate the reCAPTCHA token with Google
validateRecaptcha($token);

// Construct the HTML email content
$email_body = render_email([
    'subject' => EMAIL_SUBJECT,
    'date'    => $date->format('m/d/Y H:i:s'),
    'name'    => $name,
    'email'   => $email,
    'message' => $message,
    'ip'      => $ip,
]);

// Send the email using PHPMailer
$mail = new PHPMailer(true);

try {
    // SMTP configuration
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = SMTP_AUTH;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port       = SMTP_PORT;

    // Set sender and recipient
    $mail->setFrom(SMTP_USERNAME, FROM_NAME);
    $mail->Sender = SMTP_USERNAME;
    $mail->addAddress($email, $name);
    $mail->addCC(SMTP_USERNAME, 'Admin');
    $mail->addReplyTo($email, $name);

    // Email content
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = EMAIL_SUBJECT;
    $mail->Body    = $email_body;
    $mail->AltBody = strip_tags($email_body);

    // Attempt to send email
    $mail->send();
    respond(true, EMAIL_MESSAGES['success']);
} catch (Exception $e) {
    // Catch errors and return the message
    respond(false, '❌ ' . $e->getMessage());
}

/**
 * Validate the reCAPTCHA token via Google's API
 *
 * @param string $token reCAPTCHA token received from frontend
 */
function validateRecaptcha(string $token): void
{
    $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'secret'   => SECRET_KEY,
            'response' => $token
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);

    $response   = curl_exec($ch);
    $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($response === false || $http_code !== 200) {
        respond(false, '❌ Error during the Google reCAPTCHA request : ' . ($curl_error ?: "HTTP $http_code"));
    }

    $data = json_decode($response, true);

    if (empty($data['success'])) {
        respond(false, '❌ reCAPTCHA validation failed : ', $data['error-codes'] ?? []);
    }

    // Reject if score is too low (likely a bot)
    if (isset($data['score']) && $data['score'] < 0.5) {
        respond(false, '❌ reCAPTCHA score too low. You might be a robot 🤖');
    }
}

/**
 * Sanitize input to prevent header injection, XSS, and control characters
 *
 * @param string $data Raw input string
 * @return string Sanitized and safe string
 */
function sanitize(string $data): string
{
    // Remove null bytes and other control characters (except \t)
    $data = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $data);

    // Prevent header injection by replacing line breaks with spaces
    $data = preg_replace('/\r|\n/', ' ', $data);

    // Escape HTML entities (with strict quote handling and UTF-8 safety)
    return trim(htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8', true));
}

/**
 * Send a JSON response and stop script execution
 *
 * @param bool   $success Success status
 * @param string $message Message to send
 * @param mixed  $detail  Optional additional data
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
 * Generates the HTML email content using a PHP template and provided data.
 *
 * @param array $data Array containing the required variables for the template
 * @return string The complete rendered HTML content
 */
function render_email(array $data): string
{
    // Path to the email template (adjustable if needed)
    $templateFile = __DIR__ . '/email_template.php';

    if (!is_file($templateFile)) {
        throw new RuntimeException("Template file not found: $templateFile");
    }

    // Encapsulate in a local scope to avoid variable pollution
    return (function () use ($data, $templateFile): string {
        extract($data, EXTR_SKIP); // convert array keys into local variables
        ob_start();
        require $templateFile;
        return ob_get_clean();
    })();
}
