
# AjaxForm

### [DEMO](https://dev.raspgot.fr/github/contact-form-recaptcha-v3)
Simple form system using Ajax, SMTP and recaptcha v3 for PHP.

![raspgot](https://dev.raspgot.fr/github/contact-form-recaptcha-v3/screen.png)

## Description
* SMTP Authentication
* Google reCAPTCHA v3
* Validation and input's security

## Installation
Use clone command or [direct download](https://github.com/raspgot/AjaxForm-PHPMailer-reCAPTCHA/archive/master.zip)
```
git clone https://github.com/raspgot/AjaxForm-PHPMailer-reCAPTCHA.git
```

## Configuration
Captcha key -> [HERE](https://www.google.com/recaptcha/intro/v3.html)   
SMTP sender -> [HERE](https://www.infomaniak.com/fr/hebergement/web-et-mail/hebergement-mail)

**AjaxForm.php**
```php
# Constants to redefined
const HOST        = 'mail.infomaniak.com';
const USERNAME    = 'contact@raspgot.fr';
const PASSWORD    = '';
const SECRET_KEY  = ''; //Google reCAPTCHA
```

**AjaxForm.js**
```javascript
const publicKey = 'PUBLIC_KEY';
```

**index.html**
```html
<script src="https://www.google.com/recaptcha/api.js?render=PUBLIC_KEY"></script>
```

## Author
![raspgot](https://dev.raspgot.fr/github/contact-form-recaptcha-v3/raspgot.png)

You can visit my [Portfolio](https://raspgot.fr)

## Dependencies
* [PHPMailer](https://github.com/PHPMailer/PHPMailer)
* [reCAPTCHA PHP client library](https://github.com/google/recaptcha)
* [Bootswatch](https://bootswatch.com/)
* [Jquery](https://jquery.com/)
