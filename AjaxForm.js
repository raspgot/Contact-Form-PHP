/**
 * - Form Validation: https://getbootstrap.com/docs/5.3/forms/validation
 * - FormData API:    https://developer.mozilla.org/docs/Web/API/FormData
 * - Fetch API:       https://developer.mozilla.org/docs/Web/API/Fetch_API
 * - reCAPTCHA v3:    https://developers.google.com/recaptcha/docs/v3
 *
 * Author: Raspgot
 */

/**
 * reCAPTCHA v3 public site key (visible in frontend code)
 * ⚠️ Replace with your own key from https://www.google.com/recaptcha/admin
 * @constant {string}
 */
const RECAPTCHA_SITE_KEY = 'YOUR_RECAPTCHA_SITE_KEY';

/**
 * Backend JSON response shape
 * @typedef {Object} AjaxResponse
 * @property {boolean} success - True if message was sent successfully
 * @property {string}  message - User-facing status message
 * @property {string=} field   - Name of the form field that failed (optional)
 */

document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    // 1. Find form and required DOM elements
    const form = document.querySelector('.needs-validation');
    if (!form) return; // Exit if form not found on page

    const spinner = document.getElementById('loading-spinner');
    const submitButton = form.querySelector('button[type="submit"]');
    const alertContainer = document.getElementById('alert-status');
    
    let isSubmitting = false; // Prevent duplicate submissions

    // 2. Setup live validation (show green/red feedback as user types)
    form.querySelectorAll('input, select, textarea').forEach((field) => {
        const eventType = field.tagName === 'SELECT' ? 'change' : 'input';
        
        field.addEventListener(eventType, () => {
            const isEmpty = !field.value.trim();
            const isValid = field.checkValidity();

            // Remove all validation classes if field is empty
            if (isEmpty) {
                field.classList.remove('is-valid', 'is-invalid');
            }
            // Show green checkmark if valid
            else if (isValid) {
                field.classList.add('is-valid');
                field.classList.remove('is-invalid');
            }
            // Show red error if invalid
            else {
                field.classList.add('is-invalid');
                field.classList.remove('is-valid');
            }
        });
    });

    // 3. Handle form submission
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        event.stopPropagation();

        // Ignore if already submitting
        if (isSubmitting) return;

        // Reset any previous validation styling
        form.classList.remove('was-validated');
        form.querySelectorAll('.is-valid, .is-invalid').forEach((el) => {
            el.classList.remove('is-valid', 'is-invalid');
        });

        // Check HTML5 built-in validation (required, email format, etc.)
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            form.querySelector(':invalid')?.focus(); // Focus first invalid field
            return;
        }

        // Prepare form data for sending
        const formData = new FormData(form);
        const backendURL = 'AjaxForm.php';

        // Disable form to prevent changes during submission
        if (spinner) spinner.classList.remove('d-none');
        if (submitButton) submitButton.disabled = true;
        form.querySelectorAll('input, select, textarea, button').forEach((element) => {
            if (element !== submitButton) element.disabled = true;
        });
        isSubmitting = true;

        try {
            // Step 1: Verify reCAPTCHA is loaded
            if (!RECAPTCHA_SITE_KEY || RECAPTCHA_SITE_KEY === 'YOUR_RECAPTCHA_SITE_KEY') {
                throw new Error('⚠️ Missing reCAPTCHA site key.');
            }
            if (typeof grecaptcha === 'undefined' || !grecaptcha?.ready) {
                throw new Error('⚠️ reCAPTCHA script not loaded.');
            }

            // Step 2: Get reCAPTCHA token (proves user is human)
            const recaptchaToken = await new Promise((resolve, reject) => {
                try {
                    grecaptcha.ready(() => {
                        grecaptcha.execute(RECAPTCHA_SITE_KEY, { action: 'submit' })
                            .then(resolve)
                            .catch(reject);
                    });
                } catch (error) {
                    reject(error);
                }
            });

            // Add token to form data
            formData.append('recaptcha_token', recaptchaToken);

            // Step 3: Send form data to backend
            const response = await fetch(backendURL, {
                method: 'POST',
                body: formData,
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                throw new Error(`⚠️ Network error: ${response.status}`);
            }

            // Step 4: Parse JSON response
            /** @type {AjaxResponse} */
            let data;
            try {
                data = await response.json();
            } catch {
                throw new Error('⚠️ Invalid JSON response from server.');
            }

            const wasSuccessful = !!data?.success;
            const statusMessage = data?.message || (wasSuccessful ? 'Success.' : 'An error occurred.');
            const invalidFieldName = data?.field;

            // Highlight specific field if backend indicates validation error
            if (invalidFieldName) {
                const invalidField = form.querySelector(`[name="${CSS.escape(invalidFieldName)}"]`);
                if (invalidField) {
                    invalidField.classList.add('is-invalid');
                    invalidField.focus();
                    form.classList.remove('was-validated');
                }
            }

            // Show success or error alert
            if (alertContainer) {
                alertContainer.className = `alert alert-${wasSuccessful ? 'success' : 'danger'} fade show`;
                alertContainer.textContent = statusMessage;
                alertContainer.classList.remove('d-none');
                alertContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            // Reset form on success
            if (wasSuccessful) {
                form.reset();
                form.classList.remove('was-validated');
                form.querySelectorAll('.is-valid, .is-invalid').forEach((el) => {
                    el.classList.remove('is-valid', 'is-invalid');
                });
            }
        } catch (error) {
            console.error(error);
            
            // Show error alert
            if (alertContainer) {
                alertContainer.className = 'alert alert-danger fade show';
                alertContainer.textContent = error?.message || 'Unexpected error.';
                alertContainer.classList.remove('d-none');
            }
        } finally {
            // Re-enable form
            if (spinner) spinner.classList.add('d-none');
            if (submitButton) submitButton.disabled = false;
            form.querySelectorAll('input, select, textarea, button').forEach((element) => {
                if (element !== submitButton) element.disabled = false;
            });
            isSubmitting = false;
        }
    });
});
