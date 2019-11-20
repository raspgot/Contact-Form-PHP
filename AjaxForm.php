<?php

# Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

# Composer's autoloader
require __DIR__ . '/vendor/autoload.php';

/**
 * Class Ajax_Form
 *
 * Class to send emails using ajax with validations security
 *
 * @package     PHPMailer | reCAPTCHA v3
 * @author      Gauthier Witkowski <hello@raspgot.fr>
 * @link        https://raspgot.fr
 * @version     1.0.1
 */

class Ajax_Form {
    
    # PHPMailer
    protected $language    = 'fr';
    protected $host        = 'mail.infomaniak.com';
    protected $username    = 'contact@raspgot.fr';
    protected $password    = '';
    protected $smtp_secure = 'tls';
    protected $smtp_auth   = true;
    protected $port        = 587;

    # reCAPTCHA v3 | https://www.google.com/recaptcha
    private $secret = '';

    # Ajax_Form
    public $subject        = 'New message !';
    public $strings        = [
        'success'           => 'Your message has been sent 🙂',
        'recaptcha-error'   => 'Error in recaptcha response',
        'error'             => 'Sorry, an error occurred while sending your message 😕',
        'enter_name'        => 'Please enter your name.',
        'enter_email'       => 'Please enter a valid email.',
        'enter_message'     => 'Please enter your message.',
        'ajax_only'         => 'Asynchronous anonymous 🎭',
        'body'              => '
            <h1>{{subject}}</h1>
            <p><strong>Name :</strong> {{name}}</p>
            <p><strong>E-Mail :</strong> {{email}}</p>
            <p><strong>Message :</strong><br>{{message}}</p>
        ',
    ];

    /**
     * Ajax_Form __constructor
     */
    public function __construct() {

        # Ajax check.
        if (!isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) || 'XMLHttpRequest' !== $_SERVER['HTTP_X_REQUESTED_WITH']) {
            $this->errorHandler('ajax_only');
        }

        # Get secure post data
        $name    = $this->secure($_POST['name']);
        $email   = $this->secure($_POST['email']);
        $message = $this->secure($_POST['message']);

        # Sanitize fields
        $name    = filter_var($name, FILTER_SANITIZE_STRING);
        $email   = filter_var($email, FILTER_SANITIZE_EMAIL);
        $message = filter_var($message, FILTER_SANITIZE_STRING);

        # Validate email
        $isEmailValid = filter_var($email, FILTER_VALIDATE_EMAIL);

        # Check if email has been entered and is valid
        if (!$isEmailValid || !$email) $this->errorHandler('enter_email');

        # Check if name has been entered
        if (!$name) $this->errorHandler('enter_name');

        # Check if message has been entered
        if (!$message) $this->errorHandler('enter_message');

        # Prepare body
        $body = $this->getString('body');
        $body = $this->template( $body, [
            'subject' => $this->subject,
            'name'    => $name,
            'email'   => $email,
            'message' => $message,
        ] );

        # Verifying the user's response
        # reCAPTCHA v3
        $recaptcha = new \ReCaptcha\ReCaptcha($this->secret);
        $resp = $recaptcha
            ->setExpectedHostname($_SERVER['SERVER_NAME'])
            ->verify($_POST['token'], $_SERVER['REMOTE_ADDR']);

        if ($resp->isSuccess()) {

            # PHPMailer 
            $mail = new PHPMailer(true);

            try {

                $mail->setLanguage($this->language, 'vendor/phpmailer/language');

                # Server settings
                $mail->isSMTP();                        # Set mailer to use SMTP
                $mail->Host       = $this->host;        # Specify main and backup SMTP servers
                $mail->SMTPAuth   = $this->smtp_auth;   # Enable SMTP authentication
                $mail->Username   = $this->username;    # SMTP username
                $mail->Password   = $this->password;    # SMTP password
                $mail->SMTPSecure = $this->smtp_secure; # Enable TLS encryption, `ssl` also accepted
                $mail->Port       = $this->port;        # TCP port
            
                # Recipients
                $mail->setFrom($this->username, 'Raspgot');
                $mail->addAddress($email, $name);
                $mail->AddCC($this->username, 'Dev_copy');
                $mail->addReplyTo($this->username, 'Information');
            
                # Content
                $mail->CharSet = 'UTF-8';
                $mail->isHTML(true);
                $mail->Subject = $this->subject;
                $mail->Body    = $body;
                $mail->AltBody = strip_tags($body);;
            
                $mail->send();
                $this->successHandler('success');

            } catch (Exception $e) {
                $this->errorHandler('error');
            }
        } else {
            $this->errorHandler('recaptcha-error');
        }
    }

    /**
     * Template string
     *
     * @param $string
     * @param $vars
     *
     * @return string
     */
    public function template($string, $vars) {
        foreach ($vars as $name => $val) {
            $string = str_replace("{{{$name}}}", $val, $string);
        }
        return $string;
    }

    /**
     * Get string from $string variable
     *
     * @param $string
     *
     * @return string
     */
    public function getString($string) {
        return isset($this->strings[$string]) ? $this->strings[$string] : $string;
    }

    /**
     * Secure input field
     *
     * @param $post
     *
     * @return string
     */
    public function secure($post) {
        $post = htmlspecialchars($post);
        $post = stripslashes($post);
        $post = trim($post);
        return $post;
    }

    /**
     * Error result
     *
     * @param $message
     */
    public function errorHandler($message) {
        die(json_encode(array(
            'type'     => 'error',
            'response' => $this->getString($message),
        )));
    }

    /**
     * Success result
     *
     * @param $message
     */
    public function successHandler($message) {
        die(json_encode(array(
            'type'     => 'success',
            'response' => $this->getString($message),
        )));
    }

}

# Instanciation 
new Ajax_Form();