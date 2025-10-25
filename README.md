# Contact-Form-PHP

[![version](https://img.shields.io/badge/version-1.7.5-blue.svg)](https://github.com/raspgot/Contact-Form-PHP)
[![code size](https://img.shields.io/github/languages/code-size/raspgot/Contact-Form-PHP)](https://github.com/raspgot/Contact-Form-PHP)
[![closed issues](https://img.shields.io/github/issues-closed-raw/raspgot/Contact-Form-PHP)](https://github.com/raspgot/Contact-Form-PHP/issues?q=is%3Aissue+is%3Aclosed)
[![stars](https://img.shields.io/github/stars/raspgot/Contact-Form-PHP?style=social)](https://github.com/raspgot/Contact-Form-PHP/stargazers)

A **modern**, **secure** and **lightweight** contact form for PHP projects.    
Built with **Bootstrap 5**, **AJAX**, **PHPMailer**, and **Google reCAPTCHA v3** — no jQuery, no bloat    
🔐 Designed with **security**, **performance** and **accessibility** in mind

![Demo](https://github.raspgot.fr/contact-form-raspgot.gif)

## ✨ Features

-   **PHP 8+ Ready** – Strict types & modern code
-   **Bootstrap 5 UI** – Responsive and accessible
-   **AJAX Submission** – No page reload
-   **Google reCAPTCHA v3** – Invisible spam protection
-   **PHPMailer SMTP** – Secure email delivery
-   **Auto-reply** – User confirmation message
-   **Honeypot trap** – Basic anti-bot protection
-   **Rate limiting** – Prevents abuse (default: 5/hour)
-   **Easy customization** – Fields, messages & styles
-   **Regex-based bot User-Agent blocking**
-   **DNS email domain (MX/A) validation**
-   **reCAPTCHA v3 verification** – Default threshold 0.6
-   **Header injection and XSS protection**

## 🚀 Live Demo

🔗 [Try it here](https://github.raspgot.fr)

## 📦 Quick Start

1. **Clone or download** the repo:

    ```bash
    git clone https://github.com/raspgot/Contact-Form-PHP.git
    ```

    Or [download ZIP](https://github.com/raspgot/Contact-Form-PHP/archive/master.zip) and extract it

2. **Run locally** with PHP:

    Use a local PHP server like [XAMPP](https://www.apachefriends.org), [MAMP](https://www.mamp.info) or PHP's built-in server:

    ```bash
    php -S localhost:8000
    ```

## ⚙️ Setup

### 1. Configure backend

Get your reCAPTCHA secret key from the [Google reCAPTCHA Console](https://console.cloud.google.com/security/recaptcha/create)

Edit **`AjaxForm.php`** with your credentials:

```php
const SECRET_KEY    = 'your_recaptcha_secret_key';
const SMTP_HOST     = 'smtp.yourprovider.com';
const SMTP_USERNAME = 'you@example.com';
const SMTP_PASSWORD = 'yourpassword';
const SMTP_SECURE   = 'tls';
const SMTP_PORT     = 587;
```

> **Note:** Enable `php_curl` in `php.ini`
 This extension allows PHP to make HTTP requests (to verify reCAPTCHA or send emails via some SMTP providers)

```ini
extension=curl
```

### 2. Configure frontend

-   In **`AjaxForm.js`**:

    ```js
    const RECAPTCHA_SITE_KEY = 'YOUR_RECAPTCHA_SITE_KEY';
    ```

-   In **`index.html`**:

    ```html
    <script src="https://www.google.com/recaptcha/api.js?render=YOUR_RECAPTCHA_SITE_KEY"></script>
    ```

---

## 🔧 Customization

-   **Validation messages** → edit in `index.html`
-   **Confirmation email** → customize `email_template.php` (logo, branding, text)
-   **Rate limiting** → edit in `AjaxForm.php`:

    ```php
    const MAX_ATTEMPTS = 5;
    const RATE_LIMIT_DURATION = 3600; // in seconds
    ```

---

## 🤝 Contributing

Issues and PRs are welcome !

---

## 👨‍💻 Author

![Logo](https://github.raspgot.fr/raspgot-blue.png)    
Developed by [**Raspgot**](https://raspgot.fr) — [contact@raspgot.fr](mailto:contact@raspgot.fr)

If you find this project useful, please ⭐ star the repository !

## Dependencies

-   [PHPMailer](https://github.com/PHPMailer/PHPMailer)
-   [Bootstrap](https://github.com/twbs/bootstrap)
