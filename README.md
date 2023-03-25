# Contact-Form-PHP

![version](https://img.shields.io/badge/version-1.3.0-blue.svg) ![code size](https://img.shields.io/github/languages/code-size/raspgot/Contact-Form-PHP) [![closed issues](https://img.shields.io/github/issues-closed-raw/raspgot/Contact-Form-PHP)](https://github.com/raspgot/Contact-Form-PHP/issues?q=is%3Aissue+is%3Aclosed)          
[![stars](https://img.shields.io/github/stars/raspgot/Contact-Form-PHP?style=social)](https://github.com/raspgot/Contact-Form-PHP/stargazers)

Basic, simple and secure bootstrap contact form.    
Using Ajax protocol, PHP & JS validations inputs, SMTP mail sending, rejected not found domain and Google reCAPTCHA v3.    
**Jquery FREE.**

![](https://dev.raspgot.fr/github/contact-form-php/gif_github_1.2.0.gif)

## Live Demo
You can try this form here: https://dev.raspgot.fr/github/contact-form-php

## Features
* PHP 8.2.4 ✔️ [(See supported versions)](https://www.php.net/supported-versions.php)
* Bootstrap 5.X
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
Or use [Laragon](https://laragon.org) / [XAMMP](https://www.apachefriends.org)

## Configuration
SMTP sender &rarr; [HERE](https://www.infomaniak.com/fr/hebergement/web-et-mail/hebergement-mail)     
GOOGLE keys &rarr; [HERE](https://www.google.com/recaptcha/intro/v3.html)     
More PHPMailer configurations &rarr; [HERE](https://github.com/PHPMailer/PHPMailer/tree/master/examples)

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
const publicKey = 'GOOGLE_PUBLIC_KEY';
```

**index.html**
```html
<script src="https://www.google.com/recaptcha/api.js?render=GOOGLE_PUBLIC_KEY"></script>
```

You can cuztomise text error:
```html
<div class="valid-feedback">Name looks good</div>
<div class="invalid-feedback">Please provide a valid name</div>
```

YOU MUST ALLOW *allow_url_fopen* OR *cURL* DIRECTIVE ON YOUR SERVER IN `php.ini`
`extension=curl;`
`allow_url_fopen = On`

## Author
![logo](https://dev.raspgot.fr/github/contact-form-php/raspgot-blue.png)

You can visit my [Portfolio](https://raspgot.fr) and star this repo if you like it 🤖

## Dependencies
* [reCAPTCHA PHP client library](https://github.com/google/recaptcha)
* [PHPMailer](https://github.com/PHPMailer/PHPMailer)
* [Bootstrap](https://github.com/twbs/bootstrap)
