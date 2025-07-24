# Contact-Form-PHP

[![version](https://img.shields.io/badge/version-1.7.2-blue.svg)](https://github.com/raspgot/Contact-Form-PHP)
[![code size](https://img.shields.io/github/languages/code-size/raspgot/Contact-Form-PHP)](https://github.com/raspgot/Contact-Form-PHP)
[![closed issues](https://img.shields.io/github/issues-closed-raw/raspgot/Contact-Form-PHP)](https://github.com/raspgot/Contact-Form-PHP/issues?q=is%3Aissue+is%3Aclosed)
[![stars](https://img.shields.io/github/stars/raspgot/Contact-Form-PHP?style=social)](https://github.com/raspgot/Contact-Form-PHP/stargazers)

A **modern**, **secure**, and **customizable** contact form for any PHP project
Built with **Bootstrap 5**, powered by **AJAX**, **PHPMailer**, and **Google reCAPTCHA v3** — no jQuery, no bloat  
🔐 Designed for performance, accessibility, and clean code

![Demo](https://github.raspgot.fr/contact-form-raspgot.gif)

## ✨ Features

-   **PHP 8+ Ready** – Strictly typed and future-proof
-   **Bootstrap 5 UI** – Responsive and accessible
-   **AJAX Submission** – No page reloads
-   **Google reCAPTCHA v3** – Invisible spam protection
-   **SMTP Delivery with PHPMailer** – Secure emails
-   **Auto-reply to users** – Confirm receipt
-   **Disposable email detection** – Block throwaway addresses
-   **Honeypot spam traps** – Catch bots
-   **Session-based rate limiting** – Prevent abuse
-   **Easy customization** – Tailor fields and styles

## 🚀 Live Demo

🔗 [View the demo](https://github.raspgot.fr)

## 📦 Quick Start

1. Clone the repository :

```bash
git clone https://github.com/raspgot/Contact-Form-PHP.git
```

Or [download as ZIP](https://github.com/raspgot/Contact-Form-PHP/archive/master.zip)

2. Run it locally :

Use a local PHP server like [XAMPP](https://www.apachefriends.org), [MAMP](https://www.mamp.info) or PHP's built-in server :

```bash
php -S localhost:8000
```

## ⚙️ Configuration

### 1. Set credentials

Get your reCAPTCHA secret key at [Google reCAPTCHA](https://www.google.com/recaptcha/admin)

In `AjaxForm.php`, edit:

```php
const SECRET_KEY    = 'your_recaptcha_secret_key';
const SMTP_HOST     = 'smtp.yourprovider.com';
const SMTP_USERNAME = 'you@example.com';
const SMTP_PASSWORD = 'yourpassword';
const SMTP_SECURE   = 'tls';
const SMTP_PORT     = 587;
```

> **Note:** Enable `php_curl` in `php.ini`:
>
> ```ini
> extension=curl
> ```

### 2. Set reCAPTCHA site key in JS

In `AjaxForm.js`:

```javascript
const RECAPTCHA_SITE_KEY = 'YOUR_RECAPTCHA_SITE_KEY';
```

And in `index.html`:

```html
<script src="https://www.google.com/recaptcha/api.js?render=YOUR_RECAPTCHA_SITE_KEY"></script>
```

## ✨ Advanced Features

-   Regex-based User-Agent detection (blocks common bots)
-   DNS and disposable email validation (rejects throwaway emails)
-   reCAPTCHA score filtering (requires min. score 0.5)
-   Honeypot hidden field (traps bots)
-   Session rate limiting (max 3 submissions per hour)
-   Input sanitization to prevent header injection and XSS
-   Automatic user acknowledgment email
-   Customizable email template with dynamic data

## 🛠️ Customization

### ✏️ Change validation messages

Edit the messages in `index.html`:

```html
<div class="valid-feedback">Looks good !</div>
<div class="invalid-feedback">Please provide a valid name.</div>
```

### ✉️ Customize the confirmation email

The file `email_template.php` contains the HTML template for the confirmation email automatically sent to the user after form submission

You can modify this file to:

-   Change the content of the email
-   Add your logo and branding
-   Adjust formatting and styles

### ⚡ Adjust session rate limiting

By default, the form limits users to **5 submissions per hour** to prevent abuse

To change this limit:

1. Open `AjaxForm.php`
2. Look for the following lines:

```php
const MAX_ATTEMPTS = 5;
const RATE_LIMIT_DURATION = 3600; // seconds (3600 = 1 hour)
```

3. Modify `MAX_ATTEMPTS` and `RATE_LIMIT_DURATION` to suit your needs

## 🙌 Contributing

Feel free to open issues or submit pull requests :)

## 🧑‍💻 Author

![Logo](https://github.raspgot.fr/raspgot-blue.png)  
Developed by [**Raspgot**](https://raspgot.fr) — [contact@raspgot.fr](mailto:contact@raspgot.fr)

If you find this project useful, please ⭐ star the repository !

## 📚 Dependencies

-   [PHPMailer](https://github.com/PHPMailer/PHPMailer)
-   [Bootstrap](https://github.com/twbs/bootstrap)
