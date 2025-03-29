# Contact-Form-PHP

[![version](https://img.shields.io/badge/version-1.4.0-blue.svg)](https://github.com/raspgot/Contact-Form-PHP)
[![code size](https://img.shields.io/github/languages/code-size/raspgot/Contact-Form-PHP)](https://github.com/raspgot/Contact-Form-PHP)
[![closed issues](https://img.shields.io/github/issues-closed-raw/raspgot/Contact-Form-PHP)](https://github.com/raspgot/Contact-Form-PHP/issues?q=is%3Aissue+is%3Aclosed)
[![stars](https://img.shields.io/github/stars/raspgot/Contact-Form-PHP?style=social)](https://github.com/raspgot/Contact-Form-PHP/stargazers)

A **modern**, **lightweight**, and **secure** contact form built with PHP and Bootstrap 5 — powered by **AJAX**, **PHPMailer**, and **Google reCAPTCHA v3**, with **zero jQuery dependency**.

![Demo](https://github.raspgot.fr/gif_github_1.2.0.gif)

---

## 🚀 Live Demo

🔗 [Try the demo](https://github.raspgot.fr)

---

## ✨ Features

-   ✅ **PHP 8.4+** support
-   🎨 **Bootstrap 5** UI – Responsive and clean
-   ⚙️ **AJAX-based form submission** – No page reloads
-   🔐 **Google reCAPTCHA v3** – Prevent spam without hassle
-   📬 **PHPMailer with SMTP Auth** – Secure email delivery
-   🛡️ **Client-side + Server-side Validation** – Double-layered validation
-   🐞 **Bot Protection** – Includes honeypot field to trap bots

---

## 📦 Installation

Clone the repository or download it manually:

```bash
git clone https://github.com/raspgot/Contact-Form-PHP.git
```

Run it locally using something like [XAMPP](https://www.apachefriends.org) or [MAMP](https://www.mamp.info).

---

## ⚙️ Configuration

Before deploying, update your SMTP and reCAPTCHA credentials.

### 1. SMTP & reCAPTCHA Configuration

Obtain your reCAPTCHA keys from: [Google reCAPTCHA Admin](https://www.google.com/recaptcha/admin)  
Update the following constants in `AjaxForm.php`:

```php
const SMTP_HOST     = 'your.smtp.com';
const SMTP_USERNAME = 'your@email.com';
const SMTP_PASSWORD = 'yourpassword';
const SECRET_KEY    = 'your_recaptcha_secret_key';
```

> ℹ️ Ensure `php_curl` is enabled in your `php.ini`:
>
> ```ini
> extension=curl
> ```

### 2. Frontend reCAPTCHA Setup

Replace the site key in `AjaxForm.js`:

```js
const RECAPTCHA_SITE_KEY = 'your_recaptcha_site_key';
```

And update your `index.html` to include the reCAPTCHA script:

```html
<script src="https://www.google.com/recaptcha/api.js?render=your_recaptcha_site_key"></script>
```

---

## 🛠️ Customization

### Customizing Feedback Messages

Update validation messages in your HTML:

```html
<div class="valid-feedback">Looks good !</div>
<div class="invalid-feedback">Please provide a valid name.</div>
```

### Changing Form Fields

Form elements are located in `index.html`, styled with Bootstrap 5. You can add more fields (e.g., subject, phone) and handle them in `AjaxForm.php`.

---

## 🤝 Contributing

All contributions are welcome — bug fixes, feature ideas, improvements, or documentation updates !

---

## 👨‍💻 Author

![Logo](https://github.raspgot.fr/raspgot-blue.png)

Developed with ❤️ by [Raspgot](https://raspgot.fr)

If you find this useful, ⭐️ star the repo !

---

## 📚 Dependencies

-   [PHPMailer](https://github.com/PHPMailer/PHPMailer)
-   [Bootstrap](https://github.com/twbs/bootstrap)
