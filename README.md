# Contact-Form-PHP

[![version](https://img.shields.io/badge/version-1.4.0-blue.svg)](https://github.com/raspgot/Contact-Form-PHP) [![code size](https://img.shields.io/github/languages/code-size/raspgot/Contact-Form-PHP)](https://github.com/raspgot/Contact-Form-PHP) [![closed issues](https://img.shields.io/github/issues-closed-raw/raspgot/Contact-Form-PHP)](https://github.com/raspgot/Contact-Form-PHP/issues?q=is%3Aissue+is%3Aclosed) [![stars](https://img.shields.io/github/stars/raspgot/Contact-Form-PHP?style=social)](https://github.com/raspgot/Contact-Form-PHP/stargazers)

A modern, simple, and secure contact form built with Bootstrap, featuring AJAX submission. This project includes both PHP and JavaScript input validation, SMTP email sending using PHPMailer, rejection of invalid domains, and integration with Google reCAPTCHA v3. All of this is implemented **without any jQuery dependency**!

![Demo](https://github.raspgot.fr/gif_github_1.2.0.gif)

---

## Live Demo

Experience the live demo here: [Contact-Form-PHP Live Demo](https://github.raspgot.fr)

---

## Features

-   **PHP 8.4** – Compatible with the latest PHP versions ([supported versions](https://www.php.net/supported-versions.php))
-   **Bootstrap 5** – Modern and responsive design
-   **AJAX Submission** – Seamless asynchronous form handling
-   **Google reCAPTCHA v3** – Advanced spam protection
-   **PHPMailer SMTP Authentication** – Secure email delivery
-   **Client-side and Server-side Validation** – Ensures data integrity through robust JavaScript and PHP validation

---

## Installation

Clone the repository or [download it directly](https://github.com/raspgot/Contact-Form-PHP/archive/master.zip):

```bash
git clone https://github.com/raspgot/Contact-Form-PHP.git
```

For your local setup, you can use [XAMPP](https://www.apachefriends.org).

---

## Configuration

Before going live, configure your SMTP and Google reCAPTCHA settings.

### SMTP & reCAPTCHA Settings

Obtain your reCAPTCHA keys from: [Google reCAPTCHA Admin](https://www.google.com/recaptcha/admin)

In your PHP configuration file, update the following constants:

```php
const SMTP_HOST     = ''; // Your SMTP server address
const SMTP_USERNAME = ''; // Your SMTP username
const SMTP_PASSWORD = ''; // Your SMTP password
const SECRET_KEY    = ''; // Your Google reCAPTCHA v3 secret key
```

### Updating the reCAPTCHA Key in JavaScript

In the **AjaxForm.js** file, replace the placeholder with your reCAPTCHA site key:

```javascript
const RECAPTCHA_SITE_KEY = 'YOUR_RECAPTCHA_SITE_KEY';
```

### Integrating the reCAPTCHA Script

Add the following script in your **index.html** file to load the Google reCAPTCHA API:

```html
<script src="https://www.google.com/recaptcha/api.js?render=YOUR_RECAPTCHA_SITE_KEY"></script>
```

### Customizing Error Messages

Error and validation messages can be customized directly in your HTML:

```html
<div class="valid-feedback">Looks good!</div>
<div class="invalid-feedback">Please provide a valid name.</div>
```

**Important:** Ensure the cURL extension is enabled in your `php.ini` file:

```ini
extension=curl;
```

---

## Contributions

Contributions are welcome! Feel free to submit a pull request or open an issue to discuss ideas for improvements.

---

## Author

![Logo](https://github.raspgot.fr/raspgot-blue.png)

Developed by [Raspgot](https://raspgot.fr). Visit my portfolio and give this repo a star if you like the project 🤖

---

## Dependencies

-   [PHPMailer](https://github.com/PHPMailer/PHPMailer)
-   [Bootstrap](https://github.com/twbs/bootstrap)
