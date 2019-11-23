
# AjaxContactForm

### [DEMO](https://dev.raspgot.fr/github/contact-form-recaptcha-v3)
SMTP email sending system with ajax and recaptcha v3 for PHP.

![raspgot](https://dev.raspgot.fr/github/contact-form-recaptcha-v3/screen.png)

## Description
* SMTP Authentication
* Google reCAPTCHA v3
* Validation and input's security

## Installation
```
git clone https://github.com/raspgot/AjaxContactForm-PHPMailer-reCAPTCHA-v3.git
composer install
```

## Configuration
Captcha key -> [HERE](https://www.google.com/recaptcha/intro/v3.html)
SMTP sender -> [HERE](https://www.infomaniak.com/fr/hebergement/web-et-mail/hebergement-mail)

**AjaxForm.php**
```php
protected $host     = 'pro1.mail.ovh.net';
protected $username = 'hello@raspgot.fr';
protected $password = '';
private $secret     = '';
```

**AjaxForm.js**
```javascript
const publicKey = '';
```

**index.html**
```html
<script src="https://www.google.com/recaptcha/api.js?render=MY_GOOGLE_KEY"></script>
```

#### Optionnal
If you wanna update vendor folder use the update [composer](https://getcomposer.org/) commande
```
composer update
```
## Author
![raspgot](https://dev.raspgot.fr/AjaxContactForm-PHPMailer/raspgot.png)

You can visit my [Portfolio](https://raspgot.fr)

## Dependencies
* [PHPMailer](https://github.com/PHPMailer/PHPMailer)
* [reCAPTCHA PHP client library](https://github.com/google/recaptcha)
* [Bootswatch](https://bootswatch.com/)
* [Jquery](https://jquery.com/)
