<?php

/**
 * Basic, simple and secure bootstrap contact form.
 * 
 * @see      https://github.com/raspgot/AjaxForm-PHPMailer-reCAPTCHA
 * @package  PHPMailer
 * @author   Gauthier Witkowski <contact@raspgot.fr>
 * @link     https://raspgot.fr
 * @version  1.4.0
 */

declare(strict_types=1);

# The response is in JSON format
header('Content-Type: application/json');

# Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';
require_once 'PHPMailer/Exception.php';

# Constants to redefine
const SECRET_KEY    = '';    # GOOGLE secret key
const SMTP_HOST     = '';    # SMTP server
const SMTP_USERNAME = '';    # SMTP username
const SMTP_PASSWORD = '';    # SMTP password
const SMTP_SECURE   = 'tls'; # 'ssl' or 'tls'
const SMTP_PORT     = 587;   # 465 pour SSL, 587 pour TLS
const SMTP_AUTH     = true;
const EMAIL_SUBJECT = 'New message !';
const EMAIL_MSG = [
    'success'        => '✔️ Your message has been sent !',
    'enter_name'     => '❌ Please enter your name',
    'enter_email'    => '❌ Please enter a valid email',
    'enter_message'  => '❌ Please enter your message',
    'token_error'    => '❌ No reCAPTCHA token received',
    'domain_error'   => '❌ Le domaine de l\'e-mail est invalide',
    'method_error'   => '❌ Méthode non autorisée',
    'constant_error' => '❌ Some constants are not defined in ' . __FILE__,
];

# Check if constants are defined
if (empty(SECRET_KEY) || empty(SMTP_HOST) || empty(SMTP_USERNAME) || empty(SMTP_PASSWORD)) {
    statusHandler(false, EMAIL_MSG['constant_error']);
}

# Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    statusHandler(false, EMAIL_MSG['method_error']);
}

# Retrieve and clean data
$date    = new DateTime();
$ip      = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) ?? 'Unknown';
$email   = filter_var(secure($_POST['email']), FILTER_SANITIZE_EMAIL) ?? statusHandler(false, EMAIL_MSG['enter_email']);
$name    = secure($_POST['name']) ?? statusHandler(false, EMAIL_MSG['enter_name']);
$message = secure($_POST['message']) ?? statusHandler(false, EMAIL_MSG['enter_message']);
$token   = secure($_POST['recaptcha_token']) ?? statusHandler(false, EMAIL_MSG['token_error']);

# Vérification du domaine de l'email
$domain  = substr(strrchr($email, "@"), 1);
if (!checkdnsrr($domain, "MX") && !checkdnsrr($domain, "A")) {
    statusHandler(false, EMAIL_MSG['domain_error']);
}

# Prepare email body
$email_body = sprintf(
    '<h1>%s</h1>
    <p><b>Date</b>: %s</p>
    <p><b>Name</b>: %s</p>
    <p><b>E-Mail</b>: %s</p>
    <p><b>Message</b>: %s</p>
    <p><b>IP</b>: %s</p>',
    EMAIL_SUBJECT,
    $date->format('j/m/Y H:i:s'),
    $name,
    $email,
    nl2br($message),
    $ip
);

# Verifying the user's response
validRecaptcha($token);

# Instantiation of PHPMailer
$mail = new PHPMailer(true);

try {
    # Server settings
    $mail->isSMTP();                   # Set mailer to use SMTP
    $mail->Host       = SMTP_HOST;     # Specify main and backup SMTP servers
    $mail->Port       = SMTP_PORT;     # TCP port
    $mail->SMTPAuth   = SMTP_AUTH;     # Enable SMTP authentication
    $mail->Username   = SMTP_USERNAME; # SMTP username
    $mail->Password   = SMTP_PASSWORD; # SMTP password
    $mail->SMTPSecure = SMTP_SECURE;   # Enable TLS encryption, `ssl` also accepted

    # Recipients
    $mail->setFrom(SMTP_USERNAME, 'Raspgot');
    $mail->addAddress($email, $name);
    $mail->addCC(SMTP_USERNAME, 'Dev_copy');
    $mail->addReplyTo(SMTP_USERNAME, 'Information');

    # Content
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = EMAIL_SUBJECT;
    $mail->Body    = $email_body;
    $mail->AltBody = strip_tags($email_body);

    # Send email
    $mail->send();
    statusHandler(true, EMAIL_MSG['success']);
} catch (Exception $e) {
    statusHandler(false, '❌ ' . $e->getMessage());
}

/**
 * Call to Google reCAPTCHA with cURL
 *
 * @param string $token
 * @return string|boolean
 */
function validRecaptcha(string $token): string|bool
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        "secret" => SECRET_KEY,
        "response" => $token
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    # Check cURL errors
    if ($response === false || $http_code !== 200) {
        statusHandler(false, '❌ Error during the Google reCAPTCHA request: ', $curl_error ?: "HTTP $http_code");
    }

    $responseData = json_decode($response, true);

    # Handle errors returned by Google
    if (!$responseData["success"]) {
        statusHandler(false, '❌ reCAPTCHA validation failed', $responseData["error-codes"] ?? []);
    }

    # Check score threshold (recommended: 0.5)
    if (isset($responseData["score"]) && $responseData["score"] < 0.5) {
        statusHandler(false, '❌ reCAPTCHA score too low. Bot risk detected');
    }

    return true;
}

/**
 * Secure input fields
 *
 * @param string $data
 * @return string
 */
function secure(string $data): string
{
    return trim(stripslashes(htmlspecialchars($data, ENT_QUOTES, 'UTF-8')));
}

/**
 * Return error or success JSON response
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
