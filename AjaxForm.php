<?php

/**
 * Improved basic and secure contact form using PHPMailer and reCAPTCHA.
 * Includes honeypot protection and enhanced email formatting.
 *
 * @see      https://github.com/raspgot/AjaxForm-PHPMailer-reCAPTCHA
 * @package  PHPMailer
 * @version  1.5.0
 */

declare(strict_types=1);

// Set response type and security headers
header('Content-Type: application/json');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline';");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';
require_once 'PHPMailer/Exception.php';

// Configuration constants - replace these with your actual values
const SECRET_KEY    = '';    // Google reCAPTCHA secret key
const SMTP_HOST     = '';    // SMTP server address
const SMTP_USERNAME = '';    // SMTP username (your email address)
const SMTP_PASSWORD = '';    // SMTP password
const SMTP_SECURE   = 'tls'; // Encryption type: 'ssl' or 'tls'
const SMTP_PORT     = 587;   // Port number: 465 for SSL, 587 for TLS
const SMTP_AUTH     = true;  // Enable SMTP authentication
const EMAIL_SUBJECT = '[Github] New message !';

// User-facing messages
const EMAIL_MESSAGES = [
    'success'        => '✔️ Your message has been sent !',
    'enter_name'     => '❌ Please enter your name.',
    'enter_email'    => '❌ Please enter a valid email.',
    'enter_message'  => '❌ Please enter your message.',
    'token_error'    => '❌ No reCAPTCHA token received.',
    'domain_error'   => '❌ The email domain is invalid.',
    'method_error'   => '❌ Method not allowed.',
    'constant_error' => '❌ Some constants are not defined in ' . __FILE__,
    'honeypot_error' => '❌ Spam detected.',
];

// Ensure essential configuration is present
if (empty(SECRET_KEY) || empty(SMTP_HOST) || empty(SMTP_USERNAME) || empty(SMTP_PASSWORD)) {
    respond(false, EMAIL_MESSAGES['constant_error']);
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, EMAIL_MESSAGES['method_error']);
}

// Get client IP and current date
$date = new DateTime();
$ip   = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) ?? 'Unknown';

// Retrieve and sanitize form data
$email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: respond(false, EMAIL_MESSAGES['enter_email']);
$name     = isset($_POST['name']) ? sanitize($_POST['name']) : respond(false, EMAIL_MESSAGES['enter_name']);
$message  = isset($_POST['message']) ? sanitize($_POST['message']) : respond(false, EMAIL_MESSAGES['enter_message']);
$token    = isset($_POST['recaptcha_token']) ? sanitize($_POST['recaptcha_token']) : respond(false, EMAIL_MESSAGES['token_error']);
$honeypot = isset($_POST['website']) ? trim($_POST['website']) : '';

// Honeypot trap - reject bots that fill hidden field
if (!empty($honeypot)) {
    respond(false, EMAIL_MESSAGES['honeypot_error']);
}

// Verify the email domain has MX or A DNS record
$domain = substr(strrchr($email, "@"), 1);
if (!checkdnsrr($domain, "MX") && !checkdnsrr($domain, "A")) {
    respond(false, EMAIL_MESSAGES['domain_error']);
}

// Generate the HTML email body
$email_body = sprintf(
    '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>%s</title></head><body style="margin:0;padding:0;background-color:#f4f4f4;font-family:Arial,sans-serif;"><table role="presentation" width="100%%"><tr><td align="center" style="padding:30px 15px;"><table width="600" style="background:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.08);overflow:hidden;"><tr><td style="background:#4a90e2;color:#fff;padding:30px;text-align:center;font-size:28px;font-weight:bold;">%s</td></tr><tr><td style="padding:30px;color:#333;font-size:16px;line-height:1.6;"><p><strong>Date:</strong> %s</p><p><strong>Name:</strong> %s</p><p><strong>Email:</strong> <a href="mailto:%s" style="color:#4a90e2;text-decoration:none;">%s</a></p><p><strong>Message:</strong><br>%s</p><hr style="border:none;border-top:1px solid #ddd;margin:30px 0;"><p style="font-size:14px;color:#888;"><strong>IP:</strong> %s</p></td></tr><tr><td style="background:#f9f9f9;text-align:center;padding:15px;font-size:12px;color:#aaa;">This email was generated automatically. Please do not reply.</td></tr></table></td></tr></table></body></html>',
    EMAIL_SUBJECT,
    EMAIL_SUBJECT,
    $date->format('m/d/Y H:i:s'),
    $name,
    $email,
    $email,
    nl2br($message),
    $ip
);

// Validate the reCAPTCHA token
validateRecaptcha($token);

// Create and configure PHPMailer instance
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->Port       = SMTP_PORT;
    $mail->SMTPAuth   = SMTP_AUTH;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_SECURE;

    // Sender and recipient settings
    $mail->setFrom(SMTP_USERNAME, 'Raspgot');
    $mail->addAddress($email, $name);
    $mail->addCC(SMTP_USERNAME, 'Admin');
    $mail->addReplyTo($email, $name);

    // Email content
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = EMAIL_SUBJECT;
    $mail->Body    = $email_body;
    $mail->AltBody = strip_tags($email_body);

    $mail->send();
    respond(true, EMAIL_MESSAGES['success']);
} catch (Exception $e) {
    respond(false, '❌ ' . $e->getMessage());
}

/**
 * Validates the reCAPTCHA token with Google's API.
 *
 * @param string $token The token from the frontend
 */
function validateRecaptcha(string $token): void
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'secret'   => SECRET_KEY,
        'response' => $token
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response   = curl_exec($ch);
    $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($response === false || $http_code !== 200) {
        respond(false, '❌ Error during the Google reCAPTCHA request:' . ($curl_error ?: "HTTP $http_code"));
    }

    $responseData = json_decode($response, true);
    if (!isset($responseData['success']) || !$responseData['success']) {
        respond(false, '❌ reCAPTCHA validation failed.', $responseData['error-codes'] ?? []);
    }

    if (isset($responseData['score']) && $responseData['score'] < 0.5) {
        respond(false, '❌ reCAPTCHA score too low. Bot risk detected.');
    }
}

/**
 * Sanitize input to avoid injections and XSS
 *
 * @param string $data The user input
 * @return string Cleaned value
 */
function sanitize(string $data): string
{
    $data = preg_replace('/[\r\n]+/', ' ', $data); // Prevent header injection
    return trim(htmlspecialchars($data, ENT_QUOTES, 'UTF-8'));
}

/**
 * Send a JSON response and stop script execution
 *
 * @param bool   $success Request status
 * @param string $message Message for the frontend
 * @param mixed  $detail  Optional additional details
 */
function respond(bool $success, string $message, $detail = null): void
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'detail'  => $detail,
    ]);
    exit;
}