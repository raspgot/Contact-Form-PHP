# Contact-Form-PHP

![version](https://img.shields.io/badge/version-1.4.0-blue.svg) ![code size](https://img.shields.io/github/languages/code-size/raspgot/Contact-Form-PHP) [![closed issues](https://img.shields.io/github/issues-closed-raw/raspgot/Contact-Form-PHP)](https://github.com/raspgot/Contact-Form-PHP/issues?q=is%3Aissue+is%3Aclosed) [![stars](https://img.shields.io/github/stars/raspgot/Contact-Form-PHP?style=social)](https://github.com/raspgot/Contact-Form-PHP/stargazers)

A basic, simple, and secure Bootstrap contact form with AJAX submission. It features both PHP and JavaScript input validations, SMTP mail sending, domain rejection for non-existent domains, and Google reCAPTCHA v3 integration. It's completely **jQuery-free** !

![](https://dev.raspgot.fr/github/contact-form-php/gif_github_1.2.0.gif)

## Live Demo

Try the form live here: https://dev.raspgot.fr/github/contact-form-php (down temporarily)

## Features

-   **PHP 8.4** ([See supported versions](https://www.php.net/supported-versions.php))
-   **Bootstrap 5**
-   **Ajax submission** – Seamless asynchronous form handling.
-   **Google reCAPTCHA v3** – Protect your form from spam.
-   **PHPMailer SMTP Authentication** – Secure email delivery.
-   **Validation and inputs security** – Validations performed both in PHP and JavaScript.

## Installation

Clone the repository or [download it directly](https://github.com/raspgot/Contact-Form-PHP/archive/master.zip)

```bash
git clone https://github.com/raspgot/Contact-Form-PHP.git
cd Contact-Form-PHP
php -S localhost:8000 # need cacert.pem in php.ini
```

Alternatively, you can use [Laragon](https://laragon.org) / [XAMMP](https://www.apachefriends.org)

## Configuration

Before using the form, configure your SMTP and Google reCAPTCHA settings.

-   **SMTP Sender**: Configure your SMTP settings (e.g., via [Infomaniak](https://www.infomaniak.com/fr/hebergement/web-et-mail/hebergement-mail)).
-   **Google reCAPTCHA v3**: Obtain your keys from [Google reCAPTCHA](https://www.google.com/recaptcha/intro/v3.html).
-   **PHPMailer Configuration**: See more options in the [PHPMailer examples](https://github.com/PHPMailer/PHPMailer/tree/master/examples).

```php
const SMTP_HOST     = ''; # SMTP server address
const SMTP_USERNAME = ''; # SMTP username
const SMTP_PASSWORD = ''; # SMTP password
const SECRET_KEY    = ''; # Google reCAPTCHA secret key
```

Update your **AjaxForm.js** file with your reCAPTCHA site key:

```javascript
const RECAPTCHA_SITE_KEY = 'RECAPTCHA_SITE_KEY';
```

And include the reCAPTCHA script in **index.html**:

```html
<script src="https://www.google.com/recaptcha/api.js?render=RECAPTCHA_SITE_KEY"></script>
```

You can also customize your error messages in the HTML:

```html
<div class="valid-feedback">Name looks good</div>
<div class="invalid-feedback">Please provide a valid name</div>
```

**Note: Ensure the cURL extension is enabled on your server. In your `php.ini` file, make sure you have:**

```ini
extension=curl;
```

## Author

![logo](https://dev.raspgot.fr/github/contact-form-php/raspgot-blue.png)

You can visit my [Portfolio](https://raspgot.fr) and star this repo if you like it 🤖

## Dependencies

-   [PHPMailer](https://github.com/PHPMailer/PHPMailer)
-   [Bootstrap](https://github.com/twbs/bootstrap)
