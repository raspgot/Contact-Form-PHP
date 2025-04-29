# Contact-Form-PHP

[![version](https://img.shields.io/badge/version-1.4.0-blue.svg)](https://github.com/raspgot/Contact-Form-PHP)
[![code size](https://img.shields.io/github/languages/code-size/raspgot/Contact-Form-PHP)](https://github.com/raspgot/Contact-Form-PHP)
[![closed issues](https://img.shields.io/github/issues-closed-raw/raspgot/Contact-Form-PHP)](https://github.com/raspgot/Contact-Form-PHP/issues?q=is%3Aissue+is%3Aclosed)
[![stars](https://img.shields.io/github/stars/raspgot/Contact-Form-PHP?style=social)](https://github.com/raspgot/Contact-Form-PHP/stargazers)

A **modern**, **lightweight**, and **secure** contact form built with PHP and Bootstrap 5 — powered by **AJAX**, **PHPMailer**, and **Google reCAPTCHA v3**.  
✅ No jQuery, no fluff — just fast and functional.

![Demo](https://github.raspgot.fr/contact-form-raspgot.gif)

## 🚀 Live Demo

🔗 [Try the live demo](https://github.raspgot.fr)

---

## ✨ Features

-   **PHP 8.4+** support
-   **Bootstrap 5** UI – Clean, responsive and accessible
-   **AJAX form submission** – Smooth UX with no page reload
-   **Google reCAPTCHA v3** – Silent spam protection
-   **PHPMailer with SMTP Auth** – Reliable and secure email delivery
-   **Client-side & Server-side Validation** – Better security and UX
-   **Bot Trap (Honeypot)** – Simple yet effective spam mitigation

---

## 📦 Quick Start

### 1. Clone the repo

```bash
git clone https://github.com/raspgot/Contact-Form-PHP.git
```

Or [download as ZIP](https://github.com/raspgot/Contact-Form-PHP/archive/master.zip).

### 2. Run locally

Use a local PHP server like [XAMPP](https://www.apachefriends.org), [MAMP](https://www.mamp.info), or the built-in PHP server:

```bash
php -S localhost:8000
```

---

## ⚙️ Configuration

Before deployment, make sure to configure SMTP and reCAPTCHA credentials.

### 1. Set SMTP & reCAPTCHA secrets

Get your reCAPTCHA keys from [Google reCAPTCHA Admin Panel](https://www.google.com/recaptcha/admin)

Update the constants in `AjaxForm.php`:

```php
const SMTP_HOST     = 'your.smtp.com';
const SMTP_USERNAME = 'your@email.com';
const SMTP_PASSWORD = 'yourpassword';
const SECRET_KEY    = 'your_recaptcha_secret_key';
```

> ℹ️ Make sure the `php_curl` extension is enabled in `php.ini`:
>
> ```ini
> extension=curl
> ```

### 2. Update frontend reCAPTCHA key

In `AjaxForm.js`, set your site key:

```js
const RECAPTCHA_SITE_KEY = 'YOUR_RECAPTCHA_SITE_KEY';
```

And include the script at the end of `index.html`, before `</body>`:

```html
<script src="https://www.google.com/recaptcha/api.js?render=YOUR_RECAPTCHA_SITE_KEY"></script>
```

---

## 🛠️ Customization

### ✏️ Edit Validation Messages

Change messages directly in the HTML:

```html
<div class="valid-feedback">Looks good !</div>
<div class="invalid-feedback">Please provide a valid name.</div>
```

### ➕ Add Custom Fields

To add fields like subject or phone number:

1. Add them in `index.html`:

```html
<input type="text" name="subject" class="form-control" required />
```

2. Handle them in `AjaxForm.php`:

```php
$subject = sanitize($_POST['subject']) ?? '';
```

3. Add validation + include them in the email body

---

## 🙌 Contributing

Found a bug ? Have a suggestion or improvement ?  
Contributions, pull requests, and feedback are welcome !

---

## Author

![Logo](https://github.raspgot.fr/raspgot-blue.png)

Developed with ❤️ by [**Raspgot**](https://raspgot.fr)

If you find this project helpful, don't forget to ⭐️ star the repo !

---

## Dependencies

-   [PHPMailer](https://github.com/PHPMailer/PHPMailer)
-   [Bootstrap](https://github.com/twbs/bootstrap)
