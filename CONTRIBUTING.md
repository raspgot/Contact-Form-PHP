# Contributing to Contact-Form-PHP

Thank you for your interest in contributing! This document provides guidelines for contributing to this project.

## 🚀 Getting Started

1. **Fork the repository** on GitHub
2. **Clone your fork** locally:
   ```bash
   git clone https://github.com/YOUR_USERNAME/Contact-Form-PHP.git
   cd Contact-Form-PHP
   ```
3. **Create a branch** for your feature or fix:
   ```bash
   git checkout -b feature/your-feature-name
   ```

## 🛠️ Development Setup

### Prerequisites
- PHP 7.4 or higher (PHP 8+ recommended)
- Composer (optional, for development dependencies)
- A local PHP server (XAMPP, MAMP, or `php -S`)

### Configuration
1. Copy the example config:
   ```bash
   cp config.example.php config.php
   ```
2. Fill in your credentials in `config.php`
3. Never commit `config.php` to version control!

### Running Locally
```bash
php -S localhost:8000
```

Then open http://localhost:8000 in your browser.

## 📝 Coding Standards

### PHP Code Style
- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standard
- Use `declare(strict_types=1)` at the top of PHP files
- Always use type hints for function parameters and return types
- Document all public functions with PHPDoc blocks

### JavaScript Code Style
- Use modern ES6+ syntax
- Use `const` and `let` (not `var`)
- Add JSDoc comments for functions
- Follow existing code formatting

### Security Practices
- Never commit credentials or API keys
- Always sanitize user input
- Use parameterized queries if adding database support
- Validate all input on both client and server side

## 🧪 Testing

### Running Tests
```bash
composer test
```

### Writing Tests
- Add tests for new features
- Test both success and failure cases
- Include edge cases

## 📤 Submitting Changes

1. **Commit your changes** with clear, descriptive messages:
   ```bash
   git commit -m "Add feature: description of what you did"
   ```

2. **Push to your fork**:
   ```bash
   git push origin feature/your-feature-name
   ```

3. **Open a Pull Request** on GitHub with:
   - Clear title and description
   - Reference any related issues
   - Screenshots for UI changes
   - Test results

## 🐛 Bug Reports

When reporting bugs, please include:
- PHP version
- Browser and version (for frontend issues)
- Steps to reproduce
- Expected vs actual behavior
- Error messages or logs

## 💡 Feature Requests

Feature requests are welcome! Please:
- Check if it's already been requested
- Explain the use case
- Consider if it fits the project's scope (simple contact form)

## 📋 Pull Request Checklist

- [ ] Code follows project style guidelines
- [ ] Comments added for complex logic
- [ ] Documentation updated (if needed)
- [ ] Tests added/updated (if applicable)
- [ ] No credentials or sensitive data committed
- [ ] Commit messages are clear and descriptive

## ❓ Questions

If you have questions, feel free to:
- Open an issue for discussion
- Contact the maintainer: contact@raspgot.fr

## 📜 License

By contributing, you agree that your contributions will be licensed under the same license as the project.

Thank you for contributing! 🎉
