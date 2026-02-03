# Security Policy

## 🔒 Reporting a Vulnerability

The security of Contact-Form-PHP is important to us. If you discover a security vulnerability, please help us by reporting it responsibly.

### How to Report

**Please DO NOT open a public issue for security vulnerabilities.**

Instead, report security issues by emailing:
- **Email:** contact@raspgot.fr
- **Subject:** [SECURITY] Brief description of the issue

### What to Include

Please provide as much information as possible:

1. **Type of vulnerability** (XSS, SQL injection, authentication bypass, etc.)
2. **Affected component** (file name, function name, line number if possible)
3. **Steps to reproduce** the vulnerability
4. **Potential impact** of the vulnerability
5. **Suggested fix** (if you have one)

### Response Timeline

- **Initial Response:** Within 48 hours
- **Status Update:** Within 7 days
- **Fix Timeline:** Depends on severity and complexity

### Severity Levels

| Severity | Description | Response Time |
|----------|-------------|---------------|
| **Critical** | Remote code execution, authentication bypass | 24-48 hours |
| **High** | XSS, CSRF, SQL injection | 3-7 days |
| **Medium** | Information disclosure, DoS | 1-2 weeks |
| **Low** | Minor issues with limited impact | 2-4 weeks |

## 🛡️ Security Best Practices

When using this contact form, follow these security practices:

### 1. Configuration Security
- ✅ **Use `config.php`** instead of hardcoding credentials
- ✅ **Never commit** `config.php` to version control
- ✅ Store sensitive credentials in environment variables when possible
- ✅ Use strong, unique passwords for SMTP accounts

### 2. reCAPTCHA Configuration
- ✅ Register your domain with Google reCAPTCHA
- ✅ Keep your secret key confidential
- ✅ Adjust the score threshold based on your needs (default: 0.6)

### 3. HTTPS & Transport Security
- ✅ **Always use HTTPS** in production
- ✅ Use TLS for SMTP connections (`'secure' => 'tls'`)
- ✅ Keep PHP and dependencies updated

### 4. Rate Limiting
- ✅ Configure appropriate rate limits (default: 5 submissions/hour)
- ✅ Consider adding IP-based rate limiting for additional protection
- ✅ Monitor for abuse patterns

### 5. Email Security
- ✅ Use app-specific passwords for Gmail/Outlook
- ✅ Enable 2FA on your email account
- ✅ Monitor email logs for suspicious activity

### 6. Server Configuration
- ✅ Disable directory listing
- ✅ Set proper file permissions (644 for files, 755 for directories)
- ✅ Keep PHP updated to the latest stable version
- ✅ Configure `php.ini` securely:
  ```ini
  display_errors = Off
  log_errors = On
  error_log = /path/to/php-error.log
  expose_php = Off
  ```

## 🔍 Known Security Features

This project includes several built-in security features:

### Input Validation
- ✅ Email format validation
- ✅ DNS domain verification (MX/A records)
- ✅ Length validation for all fields
- ✅ XSS prevention via `htmlspecialchars()`
- ✅ Control character filtering

### Bot Protection
- ✅ Google reCAPTCHA v3 integration
- ✅ Honeypot field
- ✅ User-Agent filtering
- ✅ Session-based rate limiting

### Email Security
- ✅ Header injection protection
- ✅ SMTP authentication required
- ✅ TLS/SSL encryption support

### Response Headers
- ✅ `X-Content-Type-Options: nosniff`
- ✅ `X-Frame-Options: DENY`
- ✅ `X-XSS-Protection: 1; mode=block`
- ✅ `Referrer-Policy: strict-origin-when-cross-origin`

## 📚 Security Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
- [reCAPTCHA Security](https://developers.google.com/recaptcha/docs/security)
- [PHPMailer Security](https://github.com/PHPMailer/PHPMailer/blob/master/SECURITY.md)

## 🔄 Security Updates

Security updates will be released as soon as possible after verification. Updates will be announced via:
- GitHub Security Advisories
- Release notes
- Project README

## ⚖️ Responsible Disclosure

We follow responsible disclosure practices:
1. Security researchers have reasonable time to report vulnerabilities
2. We work with researchers to verify and fix issues
3. Public disclosure only after fix is available
4. Credit given to reporters (unless they prefer to remain anonymous)

## 📞 Contact

For security concerns:
- **Email:** contact@raspgot.fr
- **GitHub:** [@raspgot](https://github.com/raspgot)

Thank you for helping keep Contact-Form-PHP secure! 🙏
