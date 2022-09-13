<?php

/**
 * Simple and secure contact form using Ajax, validations inputs, SMTP protocol and Google reCAPTCHA v3 in PHP.
 * 
 * @see      https://github.com/raspgot/AjaxForm-PHPMailer-reCAPTCHA
 * @package  PHPMailer | reCAPTCHA v3
 * @author   Gauthier Witkowski <contact@raspgot.fr>
 * @link     https://raspgot.fr
 * @version  1.1.0
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

class Ajax_Form {
    # Constants to redefined
    # Check this for more configurations: https://blog.mailtrap.io/phpmailer
    const HOST        = ''; # SMTP server
    const USERNAME    = ''; # SMTP username
    const PASSWORD    = ''; # SMTP password
    const SECRET_KEY  = ''; # GOOGLE secret key
    const SMTP_SECURE = PHPMailer::ENCRYPTION_STARTTLS;
    const SMTP_AUTH   = true;
    const PORT        = 587;
    const SUBJECT     = 'New message !';
    const HANDLER_MSG = [
        'success'       => '✔️ Your message has been sent !',
        'token-error'   => '❌ Error recaptcha token.',
        'enter_name'    => '❌ Please enter your name.',
        'enter_email'   => '❌ Please enter a valid email.',
        'enter_message' => '❌ Please enter your message.',
        'bad_ip'        => '❌ 56k ?',
        'ajax_only'     => '❌ Asynchronous anonymous.',
        'email_body'    => '
            <h1>{{subject}}</h1>
            <p><b>Date</b>: {{date}}</p>
            <p><b>Name</b>: {{name}}</p>
            <p><b>E-Mail</b>: {{email}}</p>
            <p><b>Message</b>: {{message}}</p>
            <p><b>IP</b>: {{ip}}</p>
        '
    ];

    /**
     * Ajax_Form constructor
     */
    public function __construct()
    {
        # Check if request is Ajax request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
            $this->statusHandler('ajax_only');
        }

        # Check if fields has been entered and valid
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name    = $this->secure($_POST['name']) ?? $this->statusHandler('enter_name');
            $email   = filter_var($this->secure($_POST['email']), FILTER_SANITIZE_EMAIL) ?? $this->statusHandler('enter_email');
            $message = $this->secure($_POST['message']) ?? $this->statusHandler('enter_message');
            $token   = $this->secure($_POST['recaptcha-token']) ?? $this->statusHandler('token-error');
            $ip      = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) ?? $this->statusHandler('bad_ip');
            $date    = new DateTime();
        }

        # Prepare email body
        $email_body = self::HANDLER_MSG['email_body'];
        $email_body = $this->template($email_body, [
            'subject' => self::SUBJECT,
            'date'    => $date->format('j/m/Y H:i:s'),
            'name'    => $name,
            'email'   => $email,
            'ip'      => $ip,
            'message' => $message
        ]);

        # Verifying the user's response
        $recaptcha = new \ReCaptcha\ReCaptcha(self::SECRET_KEY);
        $resp = $recaptcha
            ->setExpectedHostname($_SERVER['SERVER_NAME'])
            ->verify($token, $_SERVER['REMOTE_ADDR']);

        if ($resp->isSuccess()) {
            # Instanciation of PHPMailer
            $mail = new PHPMailer(true);
            $mail->setLanguage('en', __DIR__ . '/vendor/PHPMailer/language/');

            try {
                # Server settings
                $mail->SMTPDebug  = SMTP::DEBUG_OFF;   # Enable verbose debug output
                $mail->isSMTP();                       # Set mailer to use SMTP
                $mail->Host       = self::HOST;        # Specify main and backup SMTP servers
                $mail->SMTPAuth   = self::SMTP_AUTH;   # Enable SMTP authentication
                $mail->Username   = self::USERNAME;    # SMTP username
                $mail->Password   = self::PASSWORD;    # SMTP password
                $mail->SMTPSecure = self::SMTP_SECURE; # Enable TLS encryption, `ssl` also accepted
                $mail->Port       = self::PORT;        # TCP port

                # Recipients
                $mail->setFrom(self::USERNAME, 'Raspgot');
                $mail->addAddress($email, $name);
                $mail->AddCC(self::USERNAME, 'Dev_copy');
                $mail->addReplyTo(self::USERNAME, 'Information');

                # Content
                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8';
                $mail->Subject = self::SUBJECT;
                $mail->Body    = $email_body;
                $mail->AltBody = strip_tags($email_body);

                # Send email
                $mail->send();
                $this->statusHandler('success');

            } catch (Exception $e) {
                die(json_encode($mail->ErrorInfo));
            }
        } else {
            die(json_encode($resp->getErrorCodes()));
        }
    }

    /**
     * Template string values
     *
     * @param string $string
     * @param array $vars
     * @return string
     */
    public function template(string $string, array $vars): string
    {
        foreach ($vars as $name => $val) {
            $string = str_replace("{{{$name}}}", $val, $string);
        }

        return $string;
    }

    /**
     * Secure inputs fields
     *
     * @param string $post
     * @return string
     */
    public function secure(string $post): string
    {
        $post = htmlspecialchars($post, ENT_QUOTES);
        $post = stripslashes($post);
        $post = trim($post);

        return $post;
    }

    /**
     * Error or success message
     *
     * @param string $message
     * @return string
     */
    public function statusHandler(string $message): string
    {
        die(json_encode(self::HANDLER_MSG[$message]));
    }
}

# Instanciation 
new Ajax_Form();
