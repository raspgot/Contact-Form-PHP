<?php

/**
 * Improved basic and secure contact form using PHPMailer and reCAPTCHA.
 * 
 * @see      https://github.com/raspgot/AjaxForm-PHPMailer-reCAPTCHA
 * @package  PHPMailer
 * @author   Gauthier Witkowski <contact@raspgot.fr>
 * @link     https://raspgot.fr
 * @version  1.4.2
 */

declare(strict_types=1);

// The response will be in JSON format
header('Content-Type: application/json');

// Load PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';
require_once 'PHPMailer/Exception.php';

// Configuration constants
const SECRET_KEY    = '';    // Google reCAPTCHA secret key
const SMTP_HOST     = '';    // SMTP server
const SMTP_USERNAME = '';    // SMTP username
const SMTP_PASSWORD = '';    // SMTP password
const SMTP_SECURE   = 'tls'; // Encryption: 'ssl' or 'tls'
const SMTP_PORT     = 587;   // Port: 465 for SSL, 587 for TLS
const SMTP_AUTH     = true;
const EMAIL_SUBJECT = '[raspgot/Contact-Form-PHP] New message !';
const EMAIL_MSG = [
    'success'        => '✔️ Your message has been sent !',
    'enter_name'     => '❌ Please enter your name.',
    'enter_email'    => '❌ Please enter a valid email.',
    'enter_message'  => '❌ Please enter your message.',
    'token_error'    => '❌ No reCAPTCHA token received.',
    'domain_error'   => '❌ The email domain is invalid.',
    'method_error'   => '❌ Method not allowed.',
    'constant_error' => '❌ Some constants are not defined in ' . __FILE__,
];

// Check if essential constants are set
if (empty(SECRET_KEY) || empty(SMTP_HOST) || empty(SMTP_USERNAME) || empty(SMTP_PASSWORD)) {
    respond(false, EMAIL_MSG['constant_error']);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, EMAIL_MSG['method_error']);
}

// Get current date and user IP address
$date = new DateTime();
$ip   = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) ?? 'Unknown';

// Retrieve and sanitize input data
$email   = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: respond(false, EMAIL_MSG['enter_email']);
$name    = isset($_POST['name']) ? sanitize($_POST['name']) : respond(false, EMAIL_MSG['enter_name']);
$message = isset($_POST['message']) ? sanitize($_POST['message']) : respond(false, EMAIL_MSG['enter_message']);
$token   = isset($_POST['recaptcha_token']) ? sanitize($_POST['recaptcha_token']) : respond(false, EMAIL_MSG['token_error']);

// Verify email domain validity
$domain = substr(strrchr($email, "@"), 1);
if (!checkdnsrr($domain, "MX") && !checkdnsrr($domain, "A")) {
    respond(false, EMAIL_MSG['domain_error']);
}

// Build the email body
$email_body = sprintf(
    '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>%s</title>
    </head>
    <body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif;">
        <table role="presentation" width="100%%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td align="center" style="padding: 30px 15px;">
                    <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
                        <tr>
                            <td style="background-color: #4a90e2; color: #ffffff; padding: 30px; text-align: center; font-size: 28px; font-weight: bold;">
                                %s
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 30px; color: #333333; font-size: 16px; line-height: 1.6;">
                                <p style="margin: 0 0 15px;"><strong>Date:</strong> %s</p>
                                <p style="margin: 0 0 15px;"><strong>Name:</strong> %s</p>
                                <p style="margin: 0 0 15px;"><strong>Email:</strong> <a href="mailto:%s" style="color: #4a90e2; text-decoration: none;">%s</a></p>
                                <p style="margin: 0 0 15px;"><strong>Message:</strong><br>%s</p>
                                <hr style="border: none; border-top: 1px solid #dddddd; margin: 30px 0;">
                                <p style="margin: 0; font-size: 14px; color: #888888;"><strong>IP:</strong> %s</p>
                            </td>
                        </tr>
                        <tr>
                            <td style="background-color: #f9f9f9; text-align: center; padding: 15px; font-size: 12px; color: #aaaaaa;">
                                This email was generated automatically. Please do not reply.
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>',
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
    // SMTP server settings
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
    $mail->addCC(SMTP_USERNAME, 'Dev_copy');
    $mail->addReplyTo(SMTP_USERNAME, 'Information');

    // Email content settings
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = EMAIL_SUBJECT;
    $mail->Body    = $email_body;
    $mail->AltBody = strip_tags($email_body);

    // Send email
    $mail->send();
    respond(true, EMAIL_MSG['success']);
} catch (Exception $e) {
    respond(false, '❌ ' . $e->getMessage());
}

/**
 * Validate the reCAPTCHA token using Google's API using cURL.
 *
 * @param string $token
 * @return void
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
        respond(false, '❌ reCAPTCHA validation failed:', $responseData['error-codes'] ?? []);
    }

    if (isset($responseData['score']) && $responseData['score'] < 0.5) {
        respond(false, '❌ reCAPTCHA score too low. Bot risk detected.');
    }
}

/**
 * Sanitize input data.
 *
 * @param string $data
 * @return string
 */
function sanitize(string $data): string
{
    return trim(stripslashes(htmlspecialchars($data, ENT_QUOTES, 'UTF-8')));
}

/**
 * Send a JSON response and terminate the script.
 *
 * @param bool   $success
 * @param string $message
 * @param mixed  $detail
 * @return void
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
