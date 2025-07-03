# Contact-Form-PHP

[![version](https://img.shields.io/badge/version-1.6.0-blue.svg)](https://github.com/raspgot/Contact-Form-PHP)
[![code size](https://img.shields.io/github/languages/code-size/raspgot/Contact-Form-PHP)](https://github.com/raspgot/Contact-Form-PHP)
[![closed issues](https://img.shields.io/github/issues-closed-raw/raspgot/Contact-Form-PHP)](https://github.com/raspgot/Contact-Form-PHP/issues?q=is%3Aissue+is%3Aclosed)
[![stars](https://img.shields.io/github/stars/raspgot/Contact-Form-PHP?style=social)](https://github.com/raspgot/Contact-Form-PHP/stargazers)

A **modern**, **lightweight**, and **secure** contact form for any PHP project.  
Built with **Bootstrap 5**, powered by **AJAX**, **PHPMailer**, and **Google reCAPTCHA v3** — no jQuery, no bloat.  
🔐 Designed for performance, accessibility, and clean code.

![Demo](https://github.raspgot.fr/contact-form-raspgot.gif)

## 🚀 Live Demo

🔗 [Try the live demo](https://github.raspgot.fr)

---

## ✨ Features

-   **PHP 8.4+ Ready** – Future-proof and modern codebase
-   **Responsive Bootstrap 5 UI** – Clean, accessible and mobile-friendly
-   **AJAX Form Submission** – Seamless UX, no page reload
-   **Google reCAPTCHA v3** – Invisible and effective spam protection
-   **SMTP Email via PHPMailer** – Secure, authenticated delivery
-   **User-Agent & Honeypot Spam Filtering** – No bots allowed
-   **Client + Server Validation** – Double-layered security
-   **Fully Customizable** – Easily adapt fields, messages, and style

---

### Want even better spam protection ?

✅ Regex-enhanced bot detection  
✅ DNS email domain validation  
✅ Honeypot field  
✅ reCAPTCHA score filtering  
✅ rate limiting using sessions (3 submissions per hour)

---

## 📦 Quick Start

### 1. Clone the repository

```bash
git clone https://github.com/raspgot/Contact-Form-PHP.git
```

Or [download as ZIP](https://github.com/raspgot/Contact-Form-PHP/archive/master.zip).

### 2. Run it locally

Use a local PHP server like [XAMPP](https://www.apachefriends.org), [MAMP](https://www.mamp.info) or PHP's built-in server:

```bash
php -S localhost:8000
```

---

## ⚙️ Configuration

### 1. Set your credentials

Get your reCAPTCHA secret key from [Google reCAPTCHA Admin Panel](https://www.google.com/recaptcha/admin)

Edit the following constants in `AjaxForm.php`:

```php
const SMTP_HOST     = 'your.smtp.com';
const SMTP_USERNAME = 'your@email.com';
const SMTP_PASSWORD = 'yourpassword';
const SECRET_KEY    = 'your_recaptcha_secret_key';
```

> ℹ️ Enable the `php_curl` extension in your `php.ini` file:
>
> ```ini
> extension=curl
> ```

### 2. Set your site key in JS

In `AjaxForm.js`, edit the key:

```js
const RECAPTCHA_SITE_KEY = 'YOUR_RECAPTCHA_SITE_KEY';
```

And add the reCAPTCHA script at the end of `index.html`, before `</body>`:

```html
<script src="https://www.google.com/recaptcha/api.js?render=YOUR_RECAPTCHA_SITE_KEY"></script>
```

---

## 🛠️ Customization

### ✏️ Change validation messages

Edit them directly in the HTML:

```html
<div class="valid-feedback">Looks good !</div>
<div class="invalid-feedback">Please provide a valid name.</div>
```

### ➕ Add new fields

To add fields (e.g. subject or phone):

**1.** Add the field in `index.html`:

```html
<input type="text" name="subject" class="form-control" required />
```

**2.** Handle it in `AjaxForm.php`:

```php
$subject = sanitize($_POST['subject']) ?? '';
```

**3.** Include it in `email_template.php`:

---

## 🙌 Contributing

Found a bug ? Have a suggestion ? Pull requests and feedback are welcome !

---

## Author

![Logo](https://github.raspgot.fr/raspgot-blue.png)

Developed with ❤️ by [**Raspgot**](https://raspgot.fr)

If you find this project helpful, don't forget to ⭐ star the repo !

---

## Dependencies

-   [PHPMailer](https://github.com/PHPMailer/PHPMailer)
-   [Bootstrap](https://github.com/twbs/bootstrap)
