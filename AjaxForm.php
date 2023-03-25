<?php

/**
 * Basic, simple and secure bootstrap contact form.
 * 
 * @see      https://github.com/raspgot/AjaxForm-PHPMailer-reCAPTCHA
 * @package  PHPMailer | reCAPTCHA v3
 * @author   Gauthier Witkowski <contact@raspgot.fr>
 * @link     https://raspgot.fr
 * @version  1.3.0
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

# https://www.php.net/manual/fr/timezones.php
date_default_timezone_set('America/Los_Angeles');

require __DIR__ . '/vendor/PHPMailer/Exception.php';
require __DIR__ . '/vendor/PHPMailer/PHPMailer.php';
require __DIR__ . '/vendor/PHPMailer/SMTP.php';
require __DIR__ . '/vendor/recaptcha/autoload.php';

# Constants to redefined
# Check this for more configurations: https://blog.mailtrap.io/phpmailer
const HOST        = ''; # SMTP server
const USERNAME    = ''; # SMTP username
const PASSWORD    = ''; # SMTP password
const SECRET_KEY  = ''; # GOOGLE secret key
const SMTP_SECURE = PHPMailer::ENCRYPTION_STARTTLS; # or ENCRYPTION_SMTPS
const SMTP_AUTH   = true;
const PORT        = 587;
const SUBJECT     = 'New message !';
const HANDLER_MSG = [
    'success'       => '✔️ Your message has been sent !',
    'token-error'   => '❌ Error recaptcha token.',
    'enter_name'    => '❌ Please enter your name.',
    'enter_email'   => '❌ Please enter a valid email.',
    'enter_message' => '❌ Please enter your message.',
    'ajax_only'     => '❌ Asynchronous anonymous.',
    # Mail body
    'email_body'    => '
        <h1>{{subject}}</h1>
        <p><b>Date</b>: {{date}}</p>
        <p><b>Name</b>: {{name}}</p>
        <p><b>E-Mail</b>: {{email}}</p>
        <p><b>Message</b>: {{message}}</p>
        <p><b>IP</b>: {{ip}}</p>
    '
];

# Check if request is Ajax request
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    statusHandler(true, HANDLER_MSG['ajax_only']);
}

# Check if fields has been entered and valid
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = secure($_POST['name']) ?? statusHandler(true, HANDLER_MSG['enter_name']);
    $email   = filter_var(secure($_POST['email']), FILTER_SANITIZE_EMAIL) ?? statusHandler(true, HANDLER_MSG['enter_email']);
    $message = secure($_POST['message']) ?? statusHandler(true, HANDLER_MSG['enter_message']);
    $token   = secure($_POST['recaptcha-token']) ?? statusHandler(true, HANDLER_MSG['token-error']);
    $ip      = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'];
    $date    = new DateTime();
}

# Prepare email body
$email_body = HANDLER_MSG['email_body'];
$email_body = template($email_body, [
    'subject' => SUBJECT,
    'date'    => $date->format('j/m/Y H:i:s'),
    'name'    => $name,
    'email'   => $email,
    'ip'      => filter_var($ip, FILTER_VALIDATE_IP),
    'message' => $message
]);

# Verifying the user's response
$recaptcha = new \ReCaptcha\ReCaptcha(SECRET_KEY);
$resp = $recaptcha
    ->setExpectedHostname($_SERVER['SERVER_NAME'])
    ->verify($token, filter_var($ip, FILTER_VALIDATE_IP));

if ($resp->isSuccess()) {
    # Instanciation of PHPMailer
    $mail = new PHPMailer(true);
    $mail->setLanguage('en', __DIR__ . '/vendor/PHPMailer/language/');

    try {
        # Server settings
        $mail->SMTPDebug  = SMTP::DEBUG_OFF; # Enable verbose debug output
        $mail->isSMTP();                     # Set mailer to use SMTP
        $mail->Host       = HOST;            # Specify main and backup SMTP servers
        $mail->Port       = PORT;            # TCP port
        $mail->SMTPAuth   = SMTP_AUTH;       # Enable SMTP authentication
        $mail->Username   = USERNAME;        # SMTP username
        $mail->Password   = PASSWORD;        # SMTP password
        $mail->SMTPSecure = SMTP_SECURE;     # Enable TLS encryption, `ssl` also accepted

        # Recipients
        $mail->setFrom(USERNAME, 'Raspgot');
        $mail->addAddress($email, $name);
        $mail->AddCC(USERNAME, 'Dev_copy');
        $mail->addReplyTo(USERNAME, 'Information');

        # Content
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = SUBJECT;
        $mail->Body    = $email_body;
        $mail->AltBody = strip_tags($email_body);

        # Send email
        $mail->send();
        statusHandler(false, HANDLER_MSG['success']);

    } catch (Exception $e) {
        statusHandler(true, $mail->ErrorInfo);
    }
} else {
    statusHandler(true, $resp->getErrorCodes());
}

/**
 * Template string values
 * @param string $string
 * @param array $vars
 * @return string
 */
function template(string $string, array $vars): string
{
    foreach ($vars as $name => $val) {
        $string = str_replace("{{{$name}}}", $val, $string);
    }

    return $string;
}

/**
 * Secure inputs fields
 * @param string $post
 * @return string
 */
function secure(string $post): string
{
    $post = htmlspecialchars($post, ENT_QUOTES);
    $post = stripslashes($post);
    $post = trim($post);

    return $post;
}

/**
 * Error or success message
 * @param bool $error
 * @param mixed $message
 * @return string
 */
function statusHandler(bool $error, $message): string
{
    die(json_encode([
        'error'   => $error,
        'message' => $message
    ]));
}