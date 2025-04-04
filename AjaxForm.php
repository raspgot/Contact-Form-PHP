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
if (empty($_SERVER['HTTP_USER_AGENT']) || preg_match('/curl|bot|spider|crawler/i', $_SERVER['HTTP_USER_AGENT'])) {
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
$email_body = '<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>' . EMAIL_SUBJECT . '</title>
    </head>
    <body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif">
        <table width="100%%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f4f4f4">
            <tr>
                <td align="center" style="padding: 30px 15px">
                    <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e0e0e0">
                        <tr>
                            <td bgcolor="#4a90e2" style="padding: 24px; text-align: center; color: #ffffff; font-size: 24px; font-weight: bold">' . EMAIL_SUBJECT . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 24px; color: #333333; font-size: 16px; line-height: 1.5">
                                <p style="margin: 0 0 12px"><strong>Date</strong><br />' . $date->format('m/d/Y H:i:s') . '</p>
                                <p style="margin: 0 0 12px"><strong>Name</strong><br />' . $name .'</p>
                                <p style="margin: 0 0 12px"><strong>Email</strong><br /><a href="mailto:' . $email . '" style="color: #4a90e2; text-decoration: none">' . $email . '</a></p>
                                <p style="margin: 0 0 12px"><strong>Message</strong><br />' . $message . '</p>
                                <hr style="border: none; border-top: 1px solid #dddddd; margin: 24px 0" />
                                <p style="font-size: 14px; color: #888888; margin: 0"><strong>IP: </strong>' . $ip . '</p>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#f4f4f4" style="padding: 16px; text-align: center; font-size: 12px; color: #aaaaaa">This email was generated automatically.</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>';

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
    $mail->addAddress($email, $name);
    $mail->addBCC(SMTP_USERNAME, 'Admin');
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
 * Sanitize input to prevent header injection and XSS
 *
 * @param string $data Raw input string
 * @return string Cleaned and safe input
 */
function sanitize(string $data): string
{
    $data = preg_replace('/[\r\n]+/', ' ', $data); // Prevent email header injection
    return trim(htmlspecialchars($data, ENT_QUOTES, 'UTF-8')); // Escape HTML
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
