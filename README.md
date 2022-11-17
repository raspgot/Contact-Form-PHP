# Contact-Form-PHP
Simple, customizable and secure bootstrap contact form using Ajax, validations inputs, SMTP protocol and Google reCAPTCHA v3.

![](https://dev.raspgot.fr/github/contact-form-php/gif_github.gif)

## Live Demo
You can try this here: https://dev.raspgot.fr/github/contact-form-php

## Features
* PHP 8.1 [(reference)](https://www.php.net/supported-versions.php)
* Bootstrap 5.2
* Ajax submission
* Google reCAPTCHA v3
* PHPMailer SMTP Authentication
* Validation and inputs security (PHP & JS)

## Installation
Use clone command or [direct download](https://github.com/raspgot/Contact-Form-PHP/archive/master.zip)

```shell
git clone https://github.com/raspgot/Contact-Form-PHP.git
cd Contact-Form-PHP
php -S localhost:8000
```
Or use [XAMMP](https://www.apachefriends.org)

## Configuration
SMTP sender &rarr; [HERE](https://www.infomaniak.com/fr/hebergement/web-et-mail/hebergement-mail)     
GOOGLE keys &rarr; [HERE](https://www.google.com/recaptcha/intro/v3.html)     
More configurations examples &rarr; [HERE](https://github.com/PHPMailer/PHPMailer/tree/master/examples)

**AjaxForm.php**     
Timezones doc &rarr; [HERE](https://www.php.net/manual/fr/timezones.php)     

```php
date_default_timezone_set(''); # your country     

const HOST = ''; # SMTP server
const USERNAME = ''; # SMTP username
const PASSWORD = ''; # SMTP password
const SECRET_KEY = ''; # GOOGLE secret key
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
![logo](https://dev.raspgot.fr/github/contact-form-php/raspgot-blue.png)

You can visit my [Portfolio](https://raspgot.fr) and star this repo if you like it 🤖

## Dependencies
* [PHPMailer](https://github.com/PHPMailer/PHPMailer)
* [reCAPTCHA PHP client library](https://github.com/google/recaptcha)
* [Bootstrap](https://getbootstrap.com)
* [Jquery](https://github.com/jquery/jquery)
* [jquery-validation](https://github.com/jquery-validation/jquery-validation)