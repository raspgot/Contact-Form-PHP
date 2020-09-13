# AjaxForm - SMTP & reCAPTCHA v3
Simple form using Ajax, validations, SMTP and reCAPTCHA v3 for PHP.

![raspgot](https://dev.raspgot.fr/github/contact-form-recaptcha-v3/screen-form.png)

## Live Demo
You can try this: https://dev.raspgot.fr/github/contact-form-recaptcha-v3

## Features
* Ajax submission
* SMTP Authentication
* Google reCAPTCHA v3
* Validation and inputs security (PHP and JS)

## Installation
Use clone command or [direct download](https://github.com/raspgot/AjaxForm-PHPMailer-reCAPTCHA/archive/master.zip)

```shell
git clone https://github.com/raspgot/AjaxForm-PHPMailer-reCAPTCHA.git
cd AjaxForm-PHPMailer-reCAPTCHA
php -S localhost:8000
```
Or use [XAMMP](https://www.apachefriends.org)

## Configuration
SMTP sender &rarr; [HERE](https://www.infomaniak.com/fr/hebergement/web-et-mail/hebergement-mail)     
GOOGLE keys &rarr; [HERE](https://www.google.com/recaptcha/intro/v3.html)     
More configuration example &rarr; [HERE](https://github.com/PHPMailer/PHPMailer/tree/master/examples)

**AjaxForm.php**
https://www.php.net/manual/fr/timezones.php
```php
date_default_timezone_set('America/Los_Angeles');     

const HOST = 'mail.infomaniak.com'; #SMTP server
const USERNAME = ''; #SMTP username
const PASSWORD = ''; #SMTP password
const SECRET_KEY = ''; #GOOGLE secret key
```

**AjaxForm.js**
```javascript
const publicKey = ''; // GOOGLE public key
```

**index.html**
```html
<script src="https://www.google.com/recaptcha/api.js?render=GOOGLE_PUBLIC_KEY"></script>
```

## Author
![logo](https://dev.raspgot.fr/github/contact-form-recaptcha-v3/raspgot-blue.png)

You can visit my [Portfolio](https://raspgot.fr) and star this repo if you like it 🤖

## Dependencies
* [PHPMailer](https://github.com/PHPMailer/PHPMailer)
* [reCAPTCHA PHP client library](https://github.com/google/recaptcha)
* [Bootswatch](https://github.com/thomaspark/bootswatch) (Flaty)
* [Jquery](https://github.com/jquery/jquery)
* [jquery-validation](https://github.com/jquery-validation/jquery-validation)