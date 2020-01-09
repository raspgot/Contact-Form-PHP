<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;

    date_default_timezone_set('Europe/Paris');

    require_once __DIR__ . '/vendor/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/vendor/PHPMailer/SMTP.php';
    require_once __DIR__ . '/vendor/recaptcha/autoload.php';

    /**
     * Ajax_Form - Send email using ajax with validations security
     * 
     * @see      https://github.com/raspgot/AjaxForm-PHPMailer-reCAPTCHA
     * @package  PHPMailer | reCAPTCHA v3
     * @author   Gauthier Witkowski <contact@raspgot.fr>
     * @link     https://raspgot.fr
     * @version  1.0.2
     */

    class Ajax_Form {
        
        # Constants to redefined
        const HOST        = 'mail.infomaniak.com';
        const USERNAME    = 'contact@raspgot.fr';
        const PASSWORD    = '';
        const SMTP_SECURE = PHPMailer::ENCRYPTION_STARTTLS;
        const SMTP_AUTH   = true;
        const PORT        = 587;
        const SECRET_KEY  = '';
        const SUBJECT     = 'New message !';
        public $handler   = [
            'success'         => 'Your message has been sent 🙂',
            'recaptcha-error' => 'Error in recaptcha response',
            'error'           => 'Sorry, an error occurred while sending your message 😕',
            'enter_name'      => 'Please enter your name.',
            'enter_email'     => 'Please enter a valid email.',
            'enter_message'   => 'Please enter your message.',
            'ajax_only'       => 'Asynchronous anonymous 🎭',
            'body'            => '
                <h1>{{subject}}</h1>
                <p><strong>Name :</strong> {{name}}</p>
                <p><strong>E-Mail :</strong> {{email}}</p>
                <p><strong>Message :</strong> {{message}}</p>
            ',
        ];

        /**
         * Ajax_Form __constructor
         */
        public function __construct() {

            # Check if request is Ajax request
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 'XMLHttpRequest' !== $_SERVER['HTTP_X_REQUESTED_WITH']) {
                $this->statusHandler('ajax_only', 'error');
            }

            # Get secure post data
            $name    = filter_var($this->secure($_POST['name']), FILTER_SANITIZE_STRING);
            $email   = filter_var($this->secure($_POST['email']), FILTER_SANITIZE_EMAIL);
            $message = filter_var($this->secure($_POST['message']), FILTER_SANITIZE_STRING);

            # Check if fields has been entered and valid
            if (!$name) $this->statusHandler('enter_name', 'error');
            if (!$email) $this->statusHandler('enter_email', 'error');
            if (!$message) $this->statusHandler('enter_message', 'error');

            # Prepare body
            $body = $this->getString('body');
            $body = $this->template( $body, [
                'subject' => self::SUBJECT,
                'name'    => $name,
                'email'   => $email,
                'message' => $message,
            ] );

            # Verifying the user's response
            $recaptcha = new \ReCaptcha\ReCaptcha(self::SECRET_KEY);
            $resp = $recaptcha
                ->setExpectedHostname($_SERVER['SERVER_NAME'])
                ->verify($_POST['token'], $_SERVER['REMOTE_ADDR']);

            if ($resp->isSuccess()) {

                $mail = new PHPMailer(true);

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
                    $mail->CharSet = 'UTF-8';
                    $mail->isHTML(true);
                    $mail->Subject = self::SUBJECT;
                    $mail->Body    = $body;
                    $mail->AltBody = strip_tags($body);;
                
                    $mail->send();
                    $this->statusHandler('success', 'success');

                } catch (Exception $e) {
                    $this->statusHandler('error', 'error');
                }
            } else {
                $this->statusHandler('recaptcha-error', 'error');
            }
        }

        /**
         * Template string
         *
         * @param string $string
         * @param array $vars
         * @return string
         */
        public function template($string, $vars)
        {
            foreach ($vars as $name => $val) {
                $string = str_replace("{{{$name}}}", $val, $string);
            }
            return $string;
        }

        /**
         * Get string from $string variable
         *
         * @param string $string
         * @return string
         */
        public function getString($string)
        {
            return isset($this->handler[$string]) ? $this->handler[$string] : $string;
        }

        /**
         * Secure inputs fields
         *
         * @param string $post
         * @return string
         */
        public function secure($post)
        {
            $post = htmlspecialchars($post);
            $post = stripslashes($post);
            $post = trim($post);
            return $post;
        }

        /**
         * Error or success message
         *
         * @param string $message
         * @param string $status
         * @return json
         */
        public function statusHandler($message, $status)
        {
            die(json_encode([
                'type'     => $status,
                'response' => $this->getString($message)
            ]));
        }

    }

    # Instanciation 
    new Ajax_Form();
?>