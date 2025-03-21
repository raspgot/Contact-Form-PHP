<?php

/**
 * Basic, simple and secure contact form.
 * 
 * @see      https://github.com/raspgot/AjaxForm-PHPMailer-reCAPTCHA
 * @package  PHPMailer
 * @author   Gauthier Witkowski <contact@raspgot.fr>
 * @link     https://raspgot.fr
 * @version  1.4.1
 */

declare(strict_types=1);

// The response will be in JSON format
header('Content-Type: application/json');

// Include PHPMailer and its dependencies
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';
require_once 'PHPMailer/Exception.php';

// Define constants for configuration
const SECRET_KEY    = '';    // Google reCAPTCHA secret key
const SMTP_HOST     = '';    // SMTP server
const SMTP_USERNAME = '';    // SMTP username
const SMTP_PASSWORD = '';    // SMTP password
const SMTP_SECURE   = 'tls'; // 'ssl' or 'tls'
const SMTP_PORT     = 587;   // 465 for SSL, 587 for TLS
const SMTP_AUTH     = true;
const EMAIL_SUBJECT = '[raspgot/Contact-Form-PHP] New message !';
const EMAIL_MSG = [
    'success'        => '✔️ Your message has been sent !',
    'enter_name'     => '❌ Please enter your name',
    'enter_email'    => '❌ Please enter a valid email',
    'enter_message'  => '❌ Please enter your message',
    'token_error'    => '❌ No reCAPTCHA token received',
    'domain_error'   => '❌ The email domain is invalid',
    'method_error'   => '❌ Method not allowed',
    'constant_error' => '❌ Some constants are not defined in ' . __FILE__,
];

// Check if required constants are defined
if (empty(SECRET_KEY) || empty(SMTP_HOST) || empty(SMTP_USERNAME) || empty(SMTP_PASSWORD)) {
    statusHandler(false, EMAIL_MSG['constant_error']);
}

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    statusHandler(false, EMAIL_MSG['method_error']);
}

// Retrieve and sanitize data
$date    = new DateTime();
$ip      = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) ?? 'Unknown';
$email   = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?: statusHandler(false, EMAIL_MSG['enter_email']);
$name    = (string) secure($_POST['name']) ?: statusHandler(false, EMAIL_MSG['enter_name']);
$message = (string) secure($_POST['message']) ?: statusHandler(false, EMAIL_MSG['enter_message']);
$token   = (string) secure($_POST['recaptcha_token']) ?: statusHandler(false, EMAIL_MSG['token_error']);

// Verify email domain
$domain = substr(strrchr($email, "@"), 1);
if (!checkdnsrr($domain, "MX") && !checkdnsrr($domain, "A")) {
    statusHandler(false, EMAIL_MSG['domain_error']);
}

// Prepare email body with inline styles for maximum compatibility
$email_body = sprintf(
    '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>%s</title>
    </head>
    <body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;">
        <table width="100%%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td align="center">
                    <table width="600" cellpadding="20" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <tr>
                            <td align="center" style="font-size: 24px; color: #333333;">
                                <strong>%s</strong>
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #555555; line-height: 1.5;">
                                <p style="margin: 12px 0;"><strong>Date:</strong> %s</p>
                                <p style="margin: 12px 0;"><strong>Name:</strong> %s</p>
                                <p style="margin: 12px 0;"><strong>Email:</strong> %s</p>
                                <p style="margin: 12px 0;"><strong>Message:</strong><br>%s</p>
                                <p style="margin: 12px 0;"><strong>IP:</strong> %s</p>
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
    $date->format('j/m/Y H:i:s'),
    $name,
    $email,
    nl2br($message),
    $ip
);

// Validate the reCAPTCHA token
validRecaptcha($token);

// Instantiate PHPMailer and send the email
$mail = new PHPMailer(true);

try {
    // SMTP server settings
    $mail->isSMTP();                   # Set mailer to use SMTP
    $mail->Host       = SMTP_HOST;     # Specify main and backup SMTP servers
    $mail->Port       = SMTP_PORT;     # TCP port
    $mail->SMTPAuth   = SMTP_AUTH;     # Enable SMTP authentication
    $mail->Username   = SMTP_USERNAME; # SMTP username
    $mail->Password   = SMTP_PASSWORD; # SMTP password
    $mail->SMTPSecure = SMTP_SECURE;   # Enable TLS encryption, `ssl` also accepted

    // Set sender and recipients
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

    // Send the email
    $mail->send();
    statusHandler(true, EMAIL_MSG['success']);
} catch (Exception $e) {
    statusHandler(false, '❌ ' . $e->getMessage());
}

/**
 * Validate the reCAPTCHA token by calling Google's API using cURL
 *
 * @param string $token
 * @return bool|string
 */
function validRecaptcha(string $token): bool|string
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
        statusHandler(false, '❌ Error during the Google reCAPTCHA request:' . ($curl_error ?: "HTTP $http_code"));
    }

    $responseData = json_decode($response, true);
    if (!$responseData['success']) {
        statusHandler(false, '❌ reCAPTCHA validation failed:', $responseData['error-codes'] ?? []);
    }

    if (isset($responseData['score']) && $responseData['score'] < 0.5) {
        statusHandler(false, '❌ reCAPTCHA score too low. Bot risk detected');
    }

    return true;
}

/**
 * Sanitize input data.
 *
 * @param string $data
 * @return string
 */
function secure(string $data): string
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
function statusHandler(bool $success, string $message, $detail = null): void
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'detail'  => $detail,
    ]);
    exit;
}
